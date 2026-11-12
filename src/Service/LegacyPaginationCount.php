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
        return (int)Cache::remember($key, static function () use ($query, $search): int {
            $table = $query->getRepository();
            if ($table->getAlias() === 'Contacts' && !self::hasCustomerFilter($search)) {
                // The legacy Contacts list is scoped by Contacts.deleted only.
                // Counting on a fresh query avoids the display-only Customers
                // association turning an 8.7m-row count into a huge join.
                return $table->find()->where(['Contacts.deleted IS' => null])->count();
            }

            return (clone $query)->counter(null)->count();
        }, 'crm_pagination');
    }

    private static function hasCustomerFilter(array $search): bool
    {
        $customerFields = array_flip(array_keys(LegacyCrm::customerFields()));
        foreach ($search as $key => $value) {
            if ($value === '' || $value === null || $value === []) {
                continue;
            }
            $field = preg_replace('/^s_/', '', (string)$key);
            if (isset($customerFields[$field])) {
                return true;
            }
        }

        return false;
    }
}
