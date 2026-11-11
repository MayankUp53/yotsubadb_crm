<?php
declare(strict_types=1);

namespace App\Service;

use Cake\ORM\Table;
use InvalidArgumentException;

/** Lookup the uploaded phone list without deleting or sharing a staging table. */
final class LegacyPhoneExport
{
    public function __construct(private Table $customers)
    {
    }

    public static function headers(): array
    {
        return ['電話番号（「-」無）', '名前', 'かな', '性別', '年齢', '年代', '郵便番号',
            '住所1（県）', '住所2（市区郡）', '住所3（町地番）', '住所4（建物名）', '電話番号（「-」有）'];
    }

    public function exportRows(array $rows, ?int $belong = null): iterable
    {
        if (count($rows) < 2) {
            throw new InvalidArgumentException('電話番号リストにデータがありません。');
        }
        array_shift($rows);
        $phones = [];
        foreach ($rows as $offset => $row) {
            $value = trim((string)($row[0] ?? ''));
            if ($value === '') {
                continue;
            }
            if (str_starts_with($value, '=')) {
                throw new InvalidArgumentException(($offset + 2) . '行目：電話番号を文字列で入力してください。');
            }
            $phone = preg_replace('/\D/u', '', mb_convert_kana($value, 'n', 'UTF-8'));
            if ($phone === '') {
                throw new InvalidArgumentException(($offset + 2) . '行目：電話番号が正しくありません。');
            }
            $phones[] = str_starts_with($phone, '0') ? $phone : '0' . $phone;
        }
        if (!$phones) {
            throw new InvalidArgumentException('電話番号を入力してください。');
        }
        $fields = ['customer_name', 'name_kana', 'gender', 'age', 'nendai', 'zip', 'address1', 'address2', 'address3', 'address4', 'tel'];
        foreach (array_chunk($phones, 500) as $batch) {
            $matches = [];
            $query = LegacyCrm::search($this->customers, [], $belong)->where(['Customers.phone1 IN' => array_values(array_unique($batch))]);
            foreach ($query->all() as $customer) {
                $matches[$customer->phone1][] = $customer;
            }
            foreach ($batch as $phone) {
                foreach ($matches[$phone] ?? [null] as $customer) {
                    $result = ["'" . $phone];
                    foreach ($fields as $field) {
                        $value = $customer?->get($field) ?? '';
                        if ($field === 'gender') {
                            $value = ['1' => '男', '2' => '女'][(string)$value] ?? '';
                        } elseif ($field === 'nendai') {
                            $value = $value === '' ? '' : (string)((int)$value * 10);
                        }
                        $result[] = $value;
                    }
                    yield $result;
                }
            }
        }
    }
}
