<?php
declare(strict_types=1);

namespace App\Service;

use Cake\ORM\Table;

/** Field and query definitions ported from app/Model and app/View. */
final class LegacyCrm
{
    public static function customerFields(): array
    {
        return [
            'tel' => '電話番号（「-」有）', 'phone1' => '電話番号（「-」無）', 'mobile' => '携帯電話',
            'customer_name' => '名前', 'name_kana' => 'カナ', 'gender' => '性別', 'age' => '年齢',
            'dob' => '生年', 'nendai' => '年代', 'zip' => '郵便番号', 'address1' => '住所1（県）',
            'address2' => '住所2（市区郡）', 'address3' => '住所3（町地番）', 'address4' => '住所4（建物名）',
        ];
    }

    public static function contactFields(): array
    {
        $fields = ['kekka' => '結果', 'list_name' => 'リスト名', 'sakiokuri_datetime' => '先送り日時',
            'ap_id' => 'AP担当者ID', 'ap_name' => 'AP担当者名'];
        for ($i = 1; $i <= 5; $i++) {
            $fields += ["product_name$i" => "商品$i", "quantity$i" => "数量$i", "unit_price$i" => "単価$i", "price$i" => "金額$i"];
        }
        return $fields + ['total_price' => '購入金額合計', 'reason' => '購入動機', 'shoujou' => '症状',
            'impression' => '印象', 'joukyou' => '状況', 'day_service_times' => 'デイサービス（週）',
            'day_service_days' => 'デイサービス曜日', 'kazoku_kankyou' => '家族環境', 'fukuyaku' => '服薬',
            'supply' => 'サプリ', 'memo' => '備考',
            'receipt_number' => '受付番号', 'contact_company' => '問合せ社名', 'contact_tantou' => '問合せ担当者名',
            'contact_dep' => '問合せ部署', 'contact_tel' => '問合せ電話番号', 'site_company' => '現場社名',
            'site_tantou' => '現場担当者名', 'site_dep' => '現場部署', 'site_tel' => '現場電話番号',
            'location' => '現場住所', 'product_cd' => '製品', 'ticket_no' => '伝票番号', 'destination' => '納品先',
            'date_want' => '希望日付', 'time_from' => '希望時間（開始）', 'time_to' => '希望時間（終了）',
            'category_cd' => '処理区分', 'status_cd' => '完了区分', 'correspond_cd' => '対応区分',
            'agent_cd' => '取次先', 'hv_div' => 'HV', 'contact_detail' => '問合せ内容', 'remark' => 'メモ',
            'answer_detail' => '回答内容', 'contract_cd' => '契約区分', 'contract_no' => '契約番号',
            'contract_fukusu' => '契約複数', 'contract_priod' => '契約期間'];
    }

    public static function contactFieldsForRole(bool $admin): array
    {
        $fields = self::contactFields();
        return $admin ? $fields : array_diff_key($fields, array_flip(['reason', 'shoujou', 'impression', 'joukyou',
            'day_service_times', 'day_service_days', 'kazoku_kankyou', 'fukuyaku', 'supply', 'memo']));
    }

    public static function importFields(string $type): array
    {
        if ($type === 'contacts') {
            return ['id' => '履歴番号', 'customer_id' => '顧客ID'] + self::contactFields();
        }
        $fields = self::customerFields();
        $ordered = [];
        foreach (['customer_name', 'name_kana', 'gender', 'age', 'nendai', 'zip', 'address1', 'address2', 'address3', 'address4', 'tel'] as $key) {
            $ordered[$key] = $fields[$key];
        }
        return $ordered;
    }

    public static function normalizeCustomer(array $data): array
    {
        $data = array_intersect_key($data, self::customerFields());
        if (array_key_exists('tel', $data)) {
            $data['phone1'] = preg_replace('/\D/u', '', (string)($data['phone1'] ?? $data['tel']));
            if ($data['phone1'] === '') {
                $data['phone1'] = preg_replace('/\D/u', '', (string)$data['tel']);
            }
            $data['tel_only'] = $data['phone1'];
            $data['phone'] = $data['phone1'];
            $data['phone_only'] = $data['phone1'];
        }
        if (array_key_exists('customer_name', $data)) {
            $data['name'] = $data['customer_name'];
        }
        foreach (['zip' => 'zip_cd', 'address1' => 'addr1', 'address2' => 'addr2', 'address3' => 'addr3'] as $old => $current) {
            if (array_key_exists($old, $data)) {
                $data[$current] = $data[$old];
            }
        }
        foreach (['age', 'dob', 'nendai', 'gender'] as $field) {
            if (isset($data[$field]) && $data[$field] === '') {
                $data[$field] = null;
            }
        }
        return $data;
    }

    public static function normalizeContact(array $data, array $existing = []): array
    {
        $data = array_intersect_key($data, self::contactFields());
        $submitted = $data;
        if ($existing && preg_grep('/^(quantity|unit_price|price)\d$/', array_keys($data))) {
            $data += array_intersect_key($existing, array_flip(array_values(preg_grep('/^(quantity|unit_price|price)\d$/', array_keys(self::contactFields())))));
        }
        foreach (['shoujou', 'impression', 'joukyou', 'day_service_days'] as $field) {
            if (isset($data[$field]) && is_array($data[$field])) {
                if ($field === 'day_service_days' && empty($data['day_service_times'])) {
                    $data['day_service_times'] = count(array_filter($data[$field], static fn($v) => $v !== ''));
                }
                $data[$field] = implode('', array_map(static fn($value) => '[' . str_replace(['[', ']'], '', (string)$value) . ']', array_filter($data[$field], static fn($v) => $v !== '')));
            }
        }
        foreach (['sakiokuri_datetime', 'date_want'] as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = $data[$field] === '' ? null : str_replace(['T', '/'], [' ', '-'], $data[$field]);
            }
        }
        $total = 0;
        for ($i = 1; $i <= 5; $i++) {
            $explicitPrice = isset($submitted["price$i"]) && $submitted["price$i"] !== '';
            $changedInputs = array_key_exists("quantity$i", $submitted) || array_key_exists("unit_price$i", $submitted);
            if (!$explicitPrice && $changedInputs && isset($data["quantity$i"], $data["unit_price$i"]) && $data["quantity$i"] !== '' && $data["unit_price$i"] !== '') {
                $data["price$i"] = round((float)$data["quantity$i"] * (float)$data["unit_price$i"], 2);
            }
            $total += (float)($data["price$i"] ?? 0);
            foreach (["quantity$i", "unit_price$i", "price$i"] as $field) {
                if (isset($data[$field]) && $data[$field] === '') {
                    $data[$field] = null;
                }
            }
        }
        if (array_intersect(array_keys($data), ['price1', 'price2', 'price3', 'price4', 'price5'])) {
            $data['total_price'] = $total;
        }
        if (isset($data['kekka'])) {
            $data['status'] = $data['kekka'];
        }
        return $data;
    }

    public static function search(Table $table, array $data = [], ?int $belong = null)
    {
        $contacts = $table->getAlias() === 'Contacts';
        $alias = $table->getAlias();
        $query = $table->find()->where(["$alias.deleted IS" => null]);
        if ($contacts) {
            $query->contain(['Customers'])->where(['Customers.deleted IS' => null]);
        }
        $customerAlias = $contacts ? 'Customers' : $alias;
        if ($belong !== null && $table->getConnection()->getSchemaCollection()->describe('customers')->hasColumn('belong')) {
            $query->where(["$customerAlias.belong IN" => [0, $belong]]);
        }
        $customerFields = array_keys(self::customerFields());
        $contactFields = array_keys(self::contactFields());
        foreach ($data as $key => $value) {
            $field = preg_replace('/^s_/', '', $key);
            if ($value === '' || $value === null || $value === []) {
                continue;
            }
            if ($contacts && preg_match('/^(created|updated|sakiokuri_date)_(from|to)$/', $field, $m)) {
                $column = $m[1] === 'sakiokuri_date' ? 'sakiokuri_datetime' : $m[1];
                $query->where(["Contacts.$column " . ($m[2] === 'from' ? '>=' : '<=') => str_replace('/', '-', substr((string)$value, 0, 10)) . ($m[2] === 'from' ? ' 00:00:00' : ' 23:59:59')]);
                continue;
            }
            if ($contacts && in_array($field, ['total_price_from', 'total_price_to'], true)) {
                $query->where(['Contacts.total_price ' . ($field === 'total_price_from' ? '>=' : '<=') => $value]);
                continue;
            }
            if ($contacts && $field === 'product') {
                $field = 'product_name1';
            }
            $prefix = in_array($field, $customerFields, true) ? $customerAlias : $alias;
            if (!in_array($field, $customerFields, true) && !($contacts && in_array($field, [...$contactFields, 'id', 'created_user_id', 'customer_id'], true))) {
                continue;
            }
            if (in_array($field, ['nendai', 'gender', 'kekka', 'reason', 'product_name1', 'status_cd', 'created_user_id', 'customer_id'], true)) {
                $query->where(["$prefix.$field" . (is_array($value) ? ' IN' : '') => $value]);
            } else {
                // Search plugin semantics: comma separates OR groups, spaces separate AND terms.
                $groups = [];
                foreach (explode(',', (string)$value) as $group) {
                    $terms = preg_split('/\s+/u', trim($group), -1, PREG_SPLIT_NO_EMPTY);
                    if ($terms) {
                        $groups[] = ['AND' => array_map(static fn($term) => ["$prefix.$field LIKE" => '%' . $term . '%'], $terms)];
                    }
                }
                if ($groups) {
                    $query->where(['OR' => $groups], ["$prefix.$field" => 'string']);
                }
            }
        }
        return $query->orderBy(["$alias.updated" => 'DESC', "$alias.created" => 'DESC', "$alias.id" => 'DESC']);
    }
}
