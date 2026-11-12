<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CorrectCustomerPaginationCountIndex extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('customers');
        if ($table->hasIndex(['deleted', 'belong', 'id'])) {
            return;
        }

        if ($table->hasIndexByName('idx_customers_pagination_count')) {
            $table->removeIndexByName('idx_customers_pagination_count');
        }
        $table->addIndex(
            ['deleted', 'belong', 'id'],
            ['name' => 'idx_customers_pagination_count']
        )->update();
    }

    public function down(): void
    {
        $table = $this->table('customers');
        if ($table->hasIndexByName('idx_customers_pagination_count')) {
            $table->removeIndexByName('idx_customers_pagination_count');
        }
        $table->addIndex(
            ['id', 'deleted', 'belong'],
            ['name' => 'idx_customers_pagination_count']
        )->update();
    }
}
