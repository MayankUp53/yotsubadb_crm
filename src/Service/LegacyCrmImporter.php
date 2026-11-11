<?php
declare(strict_types=1);

namespace App\Service;

use Cake\ORM\Table;
use InvalidArgumentException;

final class LegacyCrmImporter
{
    public function __construct(private Table $customers, private Table $contacts)
    {
    }

    public function import(array $rows, string $type, int $userId, ?int $belong = null): array
    {
        $result = ['inserted' => 0, 'updated' => 0, 'failed' => 0, 'errors' => []];
        $fields = LegacyCrm::importFields($type);
        $header = array_shift($rows);
        if (!$header) {
            throw new InvalidArgumentException('ファイルが空です。');
        }
        $header = array_map(static fn($v) => trim(ltrim((string)$v, "\xEF\xBB\xBF")), $header);
        if ($type === 'customers' && count($header) === 11) {
            $keys = array_keys($fields);
        } else {
            $labels = array_flip($fields);
            $keys = [];
            foreach ($header as $label) {
                $keys[] = isset($fields[$label]) ? $label : ($labels[$label] ?? null);
            }
            if (in_array(null, $keys, true) || count(array_unique($keys)) !== count($keys) || !in_array($type === 'contacts' ? 'customer_id' : 'customer_name', $keys, true)) {
                throw new InvalidArgumentException('ヘッダーが正しくありません。雛形を使用してください。');
            }
        }
        foreach ($rows as $offset => $row) {
            if (!array_filter($row, static fn($v) => $v !== null && trim((string)$v) !== '')) {
                continue;
            }
            try {
                if (count($row) > count($keys) && array_filter(array_slice($row, count($keys)), static fn($v) => $v !== null && trim((string)$v) !== '')) {
                    throw new InvalidArgumentException('列数が正しくありません。');
                }
                $raw = array_combine($keys, array_pad(array_slice(array_map(static fn($v) => trim((string)$v), $row), 0, count($keys)), count($keys), ''));
                if ($type === 'customers') {
                    // Original spreadsheet columns contain labels/decades, not internal codes.
                    if (array_key_exists('gender', $raw)) {
                        $raw['gender'] = ['男' => 1, '女' => 2][$raw['gender']] ?? null;
                    }
                    if (array_key_exists('nendai', $raw)) {
                        $raw['nendai'] = ['20' => 2, '30' => 3, '40' => 4, '50' => 5, '60' => 6, '70' => 7, '80' => 8][$raw['nendai']] ?? null;
                    }
                }
                $table = $type === 'contacts' ? $this->contacts : $this->customers;
                $data = $type === 'contacts' ? LegacyCrm::normalizeContact($raw) : LegacyCrm::normalizeCustomer($raw);
                if ($type === 'contacts') {
                    $customer = LegacyCrm::search($this->customers, [], $belong)->where(['Customers.id' => $raw['customer_id']])->first();
                    if (!$customer) {
                        throw new InvalidArgumentException('顧客IDが存在しないか、削除されています。');
                    }
                    $data['customer_id'] = $customer->id;
                    $entity = empty($raw['id']) ? null : LegacyCrm::search($this->contacts, [], $belong)->where(['Contacts.id' => $raw['id']])->first();
                    if (!empty($raw['id']) && (!$entity || (int)$entity->customer_id !== (int)$customer->id)) {
                        throw new InvalidArgumentException('履歴番号と顧客IDが一致しません。');
                    }
                } else {
                    $entity = $this->customers->find()->where(['customer_name' => $data['customer_name'] ?? '', 'phone1' => $data['phone1'] ?? ''])->first();
                }
                if ($type === 'contacts' && $entity) {
                    $data = LegacyCrm::normalizeContact($raw, $entity->toArray()) + ['customer_id' => $customer->id];
                }
                $new = $entity === null;
                $data += ['updated' => date('Y-m-d H:i:s'), 'updated_user_id' => $userId];
                if ($new) {
                    $entity = $table->newEmptyEntity();
                    $data += ['created' => date('Y-m-d H:i:s'), 'created_user_id' => $userId];
                }
                $entity = $table->patchEntity($entity, $data, ['validate' => 'legacy']);
                if ($entity->hasErrors()) {
                    throw new InvalidArgumentException(implode(' / ', array_map(static fn($messages) => implode(' ', $messages), $entity->getErrors())));
                }
                $table->saveOrFail($entity);
                $result[$new ? 'inserted' : 'updated']++;
            } catch (\Throwable $e) {
                $result['failed']++;
                $result['errors'][] = ['row' => $offset + 2, 'message' => $e instanceof InvalidArgumentException ? $e->getMessage() : '保存に失敗しました。入力値と重複を確認してください。'];
            }
        }
        return $result;
    }
}
