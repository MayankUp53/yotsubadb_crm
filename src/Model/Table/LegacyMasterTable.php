<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Service\LegacySettings;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class LegacyMasterTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $section = LegacySettings::sections()[$this->getAlias()] ?? null;
        if ($section) {
            $this->setTable($section['table']);
        }
        $this->setPrimaryKey('id');
        $this->setDisplayField('item_name');
    }

    public function activeField(): string
    {
        return $this->getSchema()->hasColumn('active_flg') ? 'active_flg' : 'active';
    }

    public function validationDefault(Validator $validator): Validator
    {
        $nameRequired = in_array($this->getAlias(), ['MtProducts', 'MtSubcategories'], true) ? '商品名は必須です。' : '名称は必須です。';
        $validator->scalar('item_name', '名称を入力してください。')->requirePresence('item_name', 'create', $nameRequired)
            ->notEmptyString('item_name', $nameRequired)->maxLength('item_name', 200, '名称は200文字以内で入力してください。');
        $active = $this->activeField();
        $validator->requirePresence($active, 'create', '有効無効は必須です。')->notEmptyString($active, '有効無効は必須です。')
            ->inList($active, [0, 1, '0', '1'], '有効または無効を選択してください。');
        $validator->nonNegativeInteger('disp_cd', '表示順序は0以上の整数で入力してください。')->allowEmptyString('disp_cd');
        if ($this->getAlias() === 'MtProducts') {
            if ($this->getSchema()->getColumnType('unit_price') === 'integer') {
                $validator->nonNegativeInteger('unit_price', '単価は0以上の整数で入力してください。')->allowEmptyString('unit_price');
            } else {
                $validator->numeric('unit_price', '単価は数値で入力してください。')->greaterThanOrEqual('unit_price', 0, '単価は0以上で入力してください。')->allowEmptyString('unit_price');
            }
        }
        return $validator;
    }
}
