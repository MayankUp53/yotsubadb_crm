<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddOriginalAppCrmFields extends AbstractMigration
{
    public function up(): void
    {
        $definitions = [
            'customers' => \App\Service\LegacyCrm::customerFields() + ['belong' => '所属', 'phone_only' => '電話番号'],
            'contacts' => \App\Service\LegacyCrm::contactFields(),
        ];
        foreach ($definitions as $name => $fields) {
            $table = $this->table($name);
            $changed = false;
            foreach ($fields as $field => $label) {
                if ($table->hasColumn($field)) {
                    continue;
                }
                $type = 'string';
                $options = ['null' => true, 'default' => null];
                if (in_array($field, ['age', 'dob', 'nendai', 'belong'], true)) {
                    $type = 'integer';
                } elseif ($field === 'sakiokuri_datetime') {
                    $type = 'datetime';
                } elseif (preg_match('/^(quantity|unit_price|price)\d$/', $field) || $field === 'total_price') {
                    $type = 'decimal';
                    $options += ['precision' => 14, 'scale' => 2];
                } elseif (in_array($field, ['memo', 'shoujou', 'impression', 'joukyou', 'fukuyaku', 'supply', 'contact_detail', 'answer_detail', 'remark'], true)) {
                    $type = 'text';
                } else {
                    $options['limit'] = 255;
                }
                if ($field === 'belong') {
                    $options['default'] = 0;
                }
                $table->addColumn($field, $type, $options);
                $changed = true;
            }
            if ($changed) {
                $table->update();
            }
        }
        // Keep existing records visible without overwriting original app values.
        $customers = $this->table('customers');
        foreach (['customer_name' => 'name', 'phone1' => 'tel_only', 'zip' => 'zip_cd', 'address1' => 'addr1', 'address2' => 'addr2', 'address3' => 'addr3'] as $target => $source) {
            if ($customers->hasColumn($source)) {
                $this->execute("UPDATE customers SET $target = $source WHERE $target IS NULL");
            }
        }
        $this->execute('UPDATE customers SET belong = 0 WHERE belong IS NULL');
        if ($this->table('contacts')->hasColumn('status')) {
            $this->execute('UPDATE contacts SET kekka = status WHERE kekka IS NULL');
        }
    }
}
