<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddCrmPaginationCountIndexes extends AbstractMigration
{
    public function up(): void
    {
        $this->withLegacyDateMode(function (): void {
            $customers = $this->table('customers');
            $customerColumns = $customers->hasColumn('belong')
                ? ['deleted', 'belong', 'id']
                : ['deleted', 'id'];
            foreach ([
                'customers' => $customerColumns,
                'contacts' => ['deleted', 'customer_id'],
            ] as $name => $columns) {
                $table = $this->table($name);
                if (!$table->hasIndex($columns)) {
                    $table->addIndex($columns, ['name' => 'idx_' . $name . '_pagination_count'])->update();
                }
            }
        });
    }

    public function down(): void
    {
        $this->withLegacyDateMode(function (): void {
            foreach (['customers', 'contacts'] as $name) {
                $table = $this->table($name);
                $index = 'idx_' . $name . '_pagination_count';
                if ($table->hasIndexByName($index)) {
                    $table->removeIndexByName($index)->update();
                }
            }
        });
    }

    private function withLegacyDateMode(callable $operation): void
    {
        $row = $this->fetchRow('SELECT @@SESSION.sql_mode AS sql_mode');
        $original = (string)($row['sql_mode'] ?? '');
        $blocked = ['STRICT_TRANS_TABLES', 'STRICT_ALL_TABLES', 'NO_ZERO_DATE', 'NO_ZERO_IN_DATE'];
        $relaxed = implode(',', array_diff(array_filter(explode(',', $original)), $blocked));
        $quote = static fn(string $value): string => "'" . str_replace("'", "''", $value) . "'";

        $this->execute('SET SESSION sql_mode = ' . $quote($relaxed));
        try {
            $operation();
        } finally {
            $this->execute('SET SESSION sql_mode = ' . $quote($original));
        }
    }
}
