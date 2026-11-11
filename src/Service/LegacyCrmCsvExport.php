<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Datasource\EntityInterface;
use Cake\Http\Response;
use Laminas\Diactoros\Stream;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\Table;

/** The active Customers/Contacts download branches and CSV templates from app/. */
final class LegacyCrmCsvExport
{
    public function __construct(private Table $customers, private Table $contacts, private Table $users)
    {
    }

    public function query(bool $contacts, array $search): SelectQuery
    {
        $query = ($contacts ? $this->contacts : $this->customers)->find();
        if (!$contacts) {
            foreach (['tel', 'phone1', 'mobile', 'customer_name', 'name_kana', 'zip', 'address1', 'address2', 'address3', 'address4'] as $field) {
                if (!empty($search['s_' . $field])) {
                    $query->where(['Customers.' . $field . ' LIKE' => '%' . $search['s_' . $field] . '%']);
                }
            }
            return $query;
        }
        $query->contain(['Customers' => ['joinType' => 'LEFT']]);
        foreach (['created', 'updated'] as $field) {
            // Legacy export ignores a to-only date and uses midnight bounds when from is supplied.
            if (!empty($search['s_' . $field . '_from'])) {
                $from = str_replace('/', '-', (string)$search['s_' . $field . '_from']);
                $query->where(['Contacts.' . $field . ' >=' => $from . ' 00:00:00']);
                if (!empty($search['s_' . $field . '_to'])) {
                    $to = str_replace('/', '-', (string)$search['s_' . $field . '_to']);
                    $query->where(['Contacts.' . $field . ' <=' => $to . ' 23:59:59']);
                }
            }
        }
        // Preserve the original operators, including equality against literal %...% strings.
        $filters = [
            'tel' => ['Customers.tel', ' LIKE', true],
            'phone1' => ['Customers.phone1', '', true],
            'mobile' => [$this->personalColumn('mobile'), ' LIKE', true],
            'list_name' => ['Contacts.list_name', ' LIKE', true],
            'customer_name' => [$this->personalColumn('customer_name'), ' LIKE', true],
            'name_kana' => [$this->personalColumn('name_kana'), ' LIKE', true],
            'address1' => [$this->personalColumn('address1'), '', true],
            'address2' => [$this->personalColumn('address2'), '', true],
            'address3' => ['Customers.address3', ' LIKE', true],
            'address4' => [$this->personalColumn('address4'), '', true],
            'age' => [$this->personalColumn('age'), '', false],
            'nendai' => [$this->personalColumn('nendai'), '', false],
            'kekka' => ['Contacts.kekka', '', false],
            'sakiokuri_date_from' => [$this->contactColumn('sakiokuri_date_from', 'sakiokuri_datetime'), '', false],
            'product' => [$this->contactColumn('product', 'product_name1'), '', false],
            'total_price_from' => [$this->contactColumn('total_price_from', 'total_price'), '', false],
            'reason' => ['Contacts.reason', '', false],
            'ap_id' => ['Contacts.ap_id', '', false],
            'ap_name' => ['Contacts.ap_name', '', true],
        ];
        foreach ($filters as $field => [$column, $operator, $wildcards]) {
            if (!empty($search['s_' . $field])) {
                $value = $search['s_' . $field];
                $query->where([$column . $operator => $wildcards ? '%' . $value . '%' : $value]);
            }
        }
        return $query;
    }

    private function personalColumn(string $field): string
    {
        // Several old export aliases target Contact fields absent from the migrated schema.
        return ($this->contacts->getSchema()->hasColumn($field) ? 'Contacts.' : 'Customers.') . $field;
    }

    private function contactColumn(string $legacy, string $current): string
    {
        return 'Contacts.' . ($this->contacts->getSchema()->hasColumn($legacy) ? $legacy : $current);
    }

    public function export(bool $contacts, array $search, Response $response): Response
    {
        $maps = $contacts ? [
            'product' => $this->masterOptions('mt_products'),
            'result' => $this->masterOptions('mt_dispositions'),
            'reason' => $this->masterOptions('mt_statuses'),
        ] : [];
        $userQuery = $this->users->find('list', keyField: 'id', valueField: 'user_name');
        if ($this->users->getSchema()->hasColumn('deleted')) {
            $userQuery->where(['Users.deleted IS' => null]);
        }
        $users = $contacts ? [] : $userQuery->toArray();
        $stream = new Stream('php://temp', 'w+');
        // Original headings are unquoted; every data field is quoted. No UTF-8 BOM.
        $stream->write(mb_convert_encoding(implode(',', $this->headers($contacts)) . "\n", 'SJIS-win', 'UTF-8'));
        foreach ($this->query($contacts, $search)->disableBufferedResults()->all() as $record) {
            $row = $contacts ? $this->contactRow($record, $maps) : $this->customerRow($record, $users);
            $quoted = array_map(fn($value) => '"' . str_replace('"', '""', $this->value($value)) . '"', $row);
            $stream->write(mb_convert_encoding(implode(',', $quoted) . "\n", 'SJIS-win', 'UTF-8'));
        }
        $stream->rewind();
        $filename = ($contacts ? '対応履歴情報_' : '顧客情報_') . date('Y-m-d_H:i:s') . '.csv';
        return $response->withHeader('Content-Type', 'application/vnd.ms-excel')
            ->withDownload(mb_convert_encoding($filename, 'SJIS-win', 'UTF-8'))
            ->withBody($stream);
    }

    private function headers(bool $contacts): array
    {
        if (!$contacts) {
            return ['ＩＤ', 'ＴＥＬ', 'ＴＥＬ２', '携帯電話', '名前', 'かな', '性別', '年齢', '生年', '年代', '郵便番号',
                '住所（県）', '住所（市区郡）', '住所（町番地）', '住所（建物名）', '登録者', '登録日', '更新者', '更新日', '削除者', '削除日'];
        }
        return ['電話番号（「-」無）', '電話番号（「-」有）', '携帯電話', '名前', 'かな', '性別', '年齢', '生年', '年代', '郵便番号',
            '住所1（県）', '住所2（市区郡）', '住所3（町地番）', '住所4（建物名）', '日付', '曜日', '結果', 'リスト名',
            'AP担当者ID', 'AP担当者名', 'AP受注日時', '先送り日時', '商品コード1', '商品名1', '商品ステイタス', '数量1', '金額1',
            '購入金額合計', '購入動機', '症状', '印象', '状況', 'デイサービス回数', 'デイサービス曜日', '家族環境', '服薬', 'サプリ', '備考'];
    }

    private function customerRow(EntityInterface $record, array $users): array
    {
        $row = [$record->id, $record->tel, $record->phone1, $record->mobile, $record->customer_name, $record->name_kana,
            $this->gender($record->gender), $record->age, $record->dob, $this->decade($record->nendai), $record->zip,
            $record->address1, $record->address2, $record->address3, $record->address4];
        foreach (['created', 'updated', 'deleted'] as $field) {
            $row[] = $users[$record->get($field . '_user_id')] ?? '';
            $row[] = $record->get($field);
        }
        return $row;
    }

    private function contactRow(EntityInterface $record, array $maps): array
    {
        $c = $record->customer;
        $product = empty($record->product_name1) ? [] : explode('_', $maps['product'][$record->product_name1] ?? '');
        $created = $this->value($record->created);
        $weekday = $created === '' ? '' : ['日', '月', '火', '水', '木', '金', '土'][(int)date('w', strtotime($created))];
        $families = ['1' => '一人暮らし', '2' => '夫婦同居', '3' => '家族同居'];
        return [$c?->phone1, $c?->tel, $c?->mobile, $c?->customer_name, $c?->name_kana, $this->gender($c?->gender),
            $c?->age, $c?->dob, $this->decade($c?->nendai), $c?->zip, $c?->address1, $c?->address2, $c?->address3, $c?->address4,
            $c?->created, $weekday, $maps['result'][$record->kekka] ?? '', $record->list_name, $record->ap_id, $record->ap_name,
            $record->created, $record->sakiokuri_datetime, $product[0] ?? '', empty($product[1]) ? ($product[0] ?? '') : $product[1],
            'サンプル', $record->quantity1, $record->price1, $record->total_price, $maps['reason'][$record->reason] ?? '',
            $record->shoujou, $record->impression, $record->joukyou, $record->day_service_times, $record->day_service_days,
            $families[(string)$record->kazoku_kankyou] ?? '', $record->fukuyaku, $record->supply,
            str_replace(["\r\n", "\n", "\r"], '&', (string)$record->memo)];
    }

    private function gender(mixed $value): string
    {
        return ['1' => '男', '2' => '女'][(string)$value] ?? '';
    }

    private function decade(mixed $value): string
    {
        return in_array((string)$value, ['2', '3', '4', '5', '6', '7', '8'], true) ? (string)((int)$value * 10) : '';
    }

    private function value(mixed $value): string
    {
        return $value instanceof \DateTimeInterface ? $value->format('Y-m-d H:i:s') : (string)($value ?? '');
    }

    private function masterOptions(string $name): array
    {
        $connection = $this->customers->getConnection();
        if (!in_array($name, $connection->getSchemaCollection()->listTables(), true)) {
            return [];
        }
        $table = new Table(['table' => $name, 'connection' => $connection]);
        return $table->find('list', keyField: 'id', valueField: 'item_name')->where(['deleted IS' => null])->toArray();
    }
}
