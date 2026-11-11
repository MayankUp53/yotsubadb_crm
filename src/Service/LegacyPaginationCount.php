<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Cache\Cache;
use Cake\ORM\Query\SelectQuery;

/** Reuse exact filtered totals between pages; never substitute approximate row estimates. */
final class LegacyPaginationCount
{
    public static function count(SelectQuery $query, array $search, ?int $belong): int
    {
        $table = $query->getRepository();
        $connection = $table->getConnection();
        ksort($search);
        $key = 'count_' . hash('sha256', serialize([
            $connection->configName(), $connection->config()['database'] ?? '', $table->getTable(), $belong, $search,
        ]));
        return (int)Cache::remember($key, static fn(): int => (clone $query)->counter(null)->count(), 'crm_pagination');
    }
}
