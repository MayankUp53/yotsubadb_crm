<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddCrmPaginationCountIndexes extends AbstractMigration
{
    public function up(): void
    {
        foreach ([
            'customers' => ['deleted', 'belong', 'id'],
            'contacts' => ['deleted', 'customer_id'],
        ] as $name => $columns) {
            $table = $this->table($name);
            if (!$table->hasIndex($columns)) {
                $table->addIndex($columns, ['name' => 'idx_' . $name . '_pagination_count'])->update();
            }
        }
    }

    public function down(): void
    {
        foreach (['customers', 'contacts'] as $name) {
            $table = $this->table($name);
            $index = 'idx_' . $name . '_pagination_count';
            if ($table->hasIndexByName($index)) {
                $table->removeIndexByName($index)->update();
            }
        }
    }
}
