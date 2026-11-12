<?php
declare(strict_types=1);

namespace App\Datasource\Paging;

use Cake\Core\Exception\CakeException;
use Cake\Datasource\Paging\Exception\PageOutOfBoundsException;
use Cake\Datasource\Paging\NumericPaginator;
use Cake\Datasource\Paging\PaginatedInterface;
use Cake\Datasource\QueryInterface;
use Cake\Datasource\RepositoryInterface;
use Cake\Datasource\ResultSetDecorator;

/** Numeric pagination that avoids multi-million-row OFFSET scans near the end. */
final class LegacyNumericPaginator extends NumericPaginator
{
    public function paginate(mixed $target, array $params = [], array $settings = []): PaginatedInterface
    {
        $query = null;
        if ($target instanceof QueryInterface) {
            $query = $target;
            $target = $query->getRepository();
            if ($target === null) {
                throw new CakeException('No repository set for query.');
            }
        }
        assert($target instanceof RepositoryInterface);

        $data = $this->extractData($target, $params, $settings);
        $query = $this->getQuery($target, $query, $data);
        $total = $this->getCount(clone $query, $data);
        $page = (int)$data['options']['page'];
        $limit = (int)$data['options']['limit'];
        $offset = ($page - 1) * $limit;

        if ($total !== null && $offset > intdiv($total, 2) && method_exists($query, 'orderBy')) {
            $remaining = max(0, $total - $offset);
            $fetchLimit = min($limit, $remaining);
            $reverseOffset = max(0, $total - $offset - $fetchLimit);
            $alias = $target->getAlias();
            $query->orderBy([
                "$alias.updated" => 'ASC',
                "$alias.created" => 'ASC',
                "$alias.id" => 'ASC',
            ], true)->offset($reverseOffset)->limit($fetchLimit);
            $items = new ResultSetDecorator(array_reverse($query->all()->toArray()));
        } else {
            $items = $this->getItems($query, $data);
        }

        $this->pagingParams['count'] = count($items);
        $this->pagingParams['totalCount'] = $total;
        $pagingParams = $this->buildParams($data);
        if ($pagingParams['requestedPage'] > $pagingParams['currentPage']) {
            throw new PageOutOfBoundsException([
                'requestedPage' => $pagingParams['requestedPage'],
                'pagingParams' => $pagingParams,
            ]);
        }

        return $this->buildPaginated($items, $pagingParams);
    }
}
