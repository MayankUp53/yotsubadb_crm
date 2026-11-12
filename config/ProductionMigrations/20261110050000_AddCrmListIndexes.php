<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddCrmListIndexes extends AbstractMigration
{
    public function up(): void
    {
        foreach (['customers', 'contacts'] as $name) {
            $table = $this->table($name);
            $columns = ['deleted', 'updated', 'created', 'id'];
            if (!$table->hasIndex($columns)) {
                $table->addIndex($columns, ['name' => 'idx_' . $name . '_active_updated'])->update();
            }
        }
    }

    public function down(): void
    {
        foreach (['customers', 'contacts'] as $name) {
            $table = $this->table($name);
            $index = 'idx_' . $name . '_active_updated';
            if ($table->hasIndexByName($index)) {
                $table->removeIndexByName($index)->update();
            }
        }
    }
}
