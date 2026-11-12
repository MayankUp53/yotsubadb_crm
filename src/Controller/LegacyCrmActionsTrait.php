<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\LegacyCrm;
use Cake\Http\Exception\ForbiddenException;

trait LegacyCrmActionsTrait
{
    protected function legacyIsContact(): bool
    {
        return $this->getName() === 'Contacts';
    }

    protected function legacyTable()
    {
        return $this->legacyIsContact() ? $this->Contacts : $this->Customers;
    }

    protected function legacyQuery(array $search = [])
    {
        $user = $this->getCurrentUser();
        return LegacyCrm::search($this->legacyTable(), $search, $user?->belong === null ? null : (int)$user->belong);
    }

    protected function legacyIndex()
    {
        $session = $this->request->getSession();
        $key = $this->getName() . '.search';
        if ($this->request->is('post')) {
            if ($this->request->getData('clear') !== null) {
                $session->delete($key);
                return $this->redirect(['action' => 'index']);
            }
            if ($this->request->getData('add') !== null) {
                return $this->redirect(['action' => 'add']);
            }
            $search = $this->request->getData('Search') ?? $this->request->getData($this->legacyIsContact() ? 'Contact' : 'Customer') ?? [];
            $session->write($key, $search);
            if ($this->request->getData('download') === null) {
                return $this->redirect(['action' => 'index']);
            }
        }
        $querySearch = array_filter($this->request->getQueryParams(), static fn($name) => str_starts_with((string)$name, 's_'), ARRAY_FILTER_USE_KEY);
        $searchData = array_replace($session->read($key) ?? [], $querySearch);
        if ($this->request->is('post') && $this->request->getData('download') !== null) {
            return $this->legacyExport($search);
        }
        $query = $this->legacyQuery($searchData);
        $userBelong = $this->getCurrentUser()?->belong;
        $belong = $userBelong === null ? null : (int)$userBelong;
        $query->counter(fn($countQuery): int => \App\Service\LegacyPaginationCount::count($countQuery, $searchData, $belong));
        $records = $this->paginate($query, [
            'className' => \App\Datasource\Paging\LegacyNumericPaginator::class,
            'limit' => 20,
            'maxLimit' => 100,
            'sortableFields' => [],
        ]);
        $this->legacyOptions();
        $this->set(compact('records', 'searchData'));
        $this->viewBuilder()->setTemplate('/LegacyCrm/index');
    }

    protected function legacyExport(array $search)
    {
        if ($this->legacyIsContact() && (string)$this->getCurrentUser()?->auth !== '9') {
            throw new ForbiddenException('CSV出力の権限がありません。');
        }
        return (new \App\Service\LegacyCrmCsvExport($this->Customers, $this->Contacts, $this->fetchTable('Users')))
            ->export($this->legacyIsContact(), $search, $this->response);
    }

    protected function legacyView(?string $id)
    {
        // Original View retrieves the selected record by ID without list filters.
        $query = $this->legacyTable()->find();
        if ($this->legacyIsContact()) {
            $query->contain(['Customers']);
        }
        $record = $query->where([$this->legacyTable()->getAlias() . '.id' => $id])->firstOrFail();
        if ($this->request->is(['post', 'put', 'patch'])) {
            if ($this->request->getData('delete') !== null) {
                return $this->legacyDelete($id);
            }
            if ($this->legacyIsContact() && $this->request->getData('update') !== null) {
                return $this->legacyForm($id);
            }
            return $this->redirect(['action' => 'index']);
        }
        $customer = $this->legacyIsContact() ? $record->customer : $record;
        $contacts = $this->Contacts->find()
            ->where(['Contacts.customer_id' => $customer->id, 'Contacts.deleted IS' => null])
            ->orderBy(['Contacts.updated' => 'DESC', 'Contacts.created' => 'DESC', 'Contacts.id' => 'DESC'])
            ->all();
        $contact = $this->Contacts->newEmptyEntity();
        $user = $this->getCurrentUser();
        $contact->ap_id = $user?->loginid;
        $contact->ap_name = $user?->user_name;
        $contact->list_name = $this->request->getSession()->read('lead_name') ?? '';
        $this->legacyOptions();
        $this->set(compact('record', 'customer', 'contacts', 'contact'));
        $this->viewBuilder()->setTemplate('/LegacyCrm/view');
    }

    protected function legacyForm(?string $id = null, ?string $customerId = null)
    {
        $isContact = $this->legacyIsContact();
        $table = $this->legacyTable();
        // Legacy edit actions look up by ID, independently of list scope/deleted filters.
        $record = $id === null ? $table->newEmptyEntity() : $table->find()->where([$table->getAlias() . '.id' => $id])->firstOrFail();
        if ($isContact) {
            if ($id === null) {
                $record->ap_id = $this->getCurrentUser()?->loginid;
                $record->ap_name = $this->getCurrentUser()?->user_name;
                $record->list_name = $this->request->getSession()->read('lead_name') ?? '';
            }
            $customerId = $id === null ? ($customerId ?? $this->request->getData('customer_id') ?? $this->request->getData('Contact.customer_id')) : (string)$record->customer_id;
            if (!$customerId) {
                $this->Flash->error('顧客を選択してください。');
                return $this->redirect(['controller' => 'Customers', 'action' => 'index']);
            }
            $customer = $this->Customers->find()->where(['Customers.id' => $customerId])->firstOrFail();
            $this->set(compact('customer'));
        }
        $back = ['controller' => 'Customers', 'action' => $isContact ? 'view' : ($id ? 'view' : 'index')];
        if ($isContact || $id) {
            $back[] = $isContact ? $customerId : $id;
        }
        if ($this->request->is(['post', 'put', 'patch'])) {
            if ($this->request->getData('back') !== null) {
                return $this->redirect($back);
            }
            if ($id && $this->request->getData('delete') !== null) {
                return $this->legacyDelete($id);
            }
            if ($isContact && $id && $this->request->getData('add') !== null) {
                return $this->redirect(['controller' => 'Customers', 'action' => 'view', $customerId]);
            }
            $raw = $this->request->getData($isContact ? 'Contact' : 'Customer') ?? $this->request->getData();
            if ($isContact) {
                $raw = array_intersect_key($raw, LegacyCrm::contactFieldsForRole((string)$this->getCurrentUser()?->auth === '9'));
                unset($raw['ap_id'], $raw['ap_name']);
                if ((string)$this->getCurrentUser()?->auth !== '9' && isset($raw['kekka']) && !in_array((int)$raw['kekka'], [1, 2, 3, 4, 5, 7, 8, 9, 10], true)) {
                    throw new ForbiddenException('この結果は選択できません。');
                }
            }
            $data = $isContact ? LegacyCrm::normalizeContact($raw, $record->toArray()) : LegacyCrm::normalizeCustomer($raw);
            $now = date('Y-m-d H:i:s');
            $data += ['updated' => $now, 'updated_user_id' => $this->getCurrentUser()?->id ?? 0];
            if ($id === null) {
                $data += ['created' => $now, 'created_user_id' => $this->getCurrentUser()?->id ?? 0];
                if ($isContact) {
                    $data += ['customer_id' => $customerId, 'ap_id' => $this->getCurrentUser()?->loginid, 'ap_name' => $this->getCurrentUser()?->user_name];
                }
            }
            $record = $table->patchEntity($record, $data, ['validate' => 'legacy']);
            $dncResponse = '';
            if ($isContact && $id === null) {
                // The original Add calls DNC before attempting the database save.
                $dncResponse = $this->syncLegacyDnc((string)$customerId, $raw['kekka'] ?? null);
            }
            if ($table->save($record)) {
                if ($isContact && $id !== null) {
                    // The original Edit calls DNC only after a successful save.
                    $dncResponse = $this->syncLegacyDnc((string)$customerId, $raw['kekka'] ?? $record->kekka);
                }
                $message = '登録が完了しました。';
                if ($isContact) {
                    $message = $id === null ? '登録が完了しました。' : '対応履歴情報の更新が完了しました。';
                    $message .= $dncResponse;
                    if ($id === null && $dncResponse === '') {
                        $message .= '画面の右側のコール結果をクリックしてください';
                    }
                }
                $this->Flash->success($message);
                if ($isContact && $id !== null) {
                    // Legacy Contact Edit returns to the customer only for a View-origin request.
                    if (str_contains($this->referer(), 'view')) {
                        return $this->redirect(['controller' => 'Customers', 'action' => 'view', $customerId]);
                    }
                    return $this->redirect(['controller' => 'Contacts', 'action' => 'index']);
                }
                return $this->redirect($isContact ? ['controller' => 'Customers', 'action' => 'view', $customerId] : ($id ? ['action' => 'view', $id] : ['action' => 'index']));
            }
            $this->Flash->error($isContact
                ? ($id === null ? '登録に失敗しました。もう一度操作してください。' . $dncResponse : '対応履歴情報の更新に失敗しました。もう一度操作してください。')
                : '登録に失敗しました。入力内容を確認してください。');
        }
        $this->legacyOptions();
        $this->set(compact('record', 'back'));
        if ($isContact) {
            $contacts = LegacyCrm::search($this->Contacts, ['customer_id' => $customerId])->all();
            $this->set(compact('contacts'));
        }
        $this->viewBuilder()->setTemplate($isContact && $id && $this->request->getData('_legacy_contact_view') ? '/LegacyCrm/view' : '/LegacyCrm/form');
    }

    protected function legacyDelete(?string $id)
    {
        $this->request->allowMethod(['post', 'put', 'patch', 'delete']);
        $user = $this->getCurrentUser();
        if (!$user || (string)$user->auth !== '9') {
            throw new ForbiddenException('削除する権限がありません。');
        }
        $table = $this->legacyTable();
        $record = $table->find()->where([$table->getAlias() . '.id' => $id])->firstOrFail();
        $record->deleted = date('Y-m-d H:i:s');
        $record->deleted_user_id = $user->id;
        $table->saveOrFail($record, ['checkRules' => false]);
        $this->Flash->success('削除が完了しました。');
        return $this->redirect($this->legacyIsContact() ? ['controller' => 'Customers', 'action' => 'view', $record->customer_id] : ['action' => 'index']);
    }

    protected function legacyOptions(): void
    {
        $isContact = $this->legacyIsContact();
        $fields = $isContact ? LegacyCrm::contactFieldsForRole((string)$this->getCurrentUser()?->auth === '9') : LegacyCrm::customerFields();
        $options = [
            'gender' => ['1' => '男', '2' => '女'], 'nendai' => array_combine(range(2, 8), range(20, 80, 10)),
            'kazoku_kankyou' => ['1' => '一人暮らし', '2' => '夫婦同居', '3' => '家族同居'],
            'impression' => array_combine(['明るい', 'よく話す', '真面目', 'おとなしい', '無口', '警戒'], ['明るい', 'よく話す', '真面目', 'おとなしい', '無口', '警戒']),
            'day_service_days' => array_combine(['月', '火', '水', '木', '金', '土', '日'], ['月', '火', '水', '木', '金', '土', '日']),
        ];
        $masterMap = ['kekka' => 'mt_dispositions', 'reason' => 'mt_statuses', 'shoujou' => 'mt_categories', 'joukyou' => 'mt_subcategories',
            'category_cd' => 'mt_categories', 'status_cd' => 'mt_statuses', 'correspond_cd' => 'mt_corresponds', 'agent_cd' => 'mt_agents', 'product_cd' => 'mt_products'];
        $connection = $this->Customers->getConnection();
        $tables = $connection->getSchemaCollection()->listTables();
        $productPrices = [];
        $inactive = [];
        foreach ($masterMap + ['product_name1' => 'mt_products'] as $field => $name) {
            $options[$field] = [];
            if (!in_array($name, $tables, true)) {
                continue;
            }
            $master = new \Cake\ORM\Table(['table' => $name, 'connection' => $connection]);
            $query = $master->find();
            if ($master->getSchema()->hasColumn('deleted')) {
                $query->where(['deleted IS' => null]);
            }
            if ($master->getSchema()->hasColumn('disp_cd')) {
                $query->orderBy(['disp_cd' => 'ASC']);
            }
            foreach ($query->all() as $row) {
                $options[$field][$row->id] = $row->item_name;
                if (($master->getSchema()->hasColumn('active_flg') && (int)$row->active_flg !== 1)
                    || ($master->getSchema()->hasColumn('active') && (int)$row->active !== 1)) {
                    $inactive[$field][] = $row->id;
                }
                if ($field === 'product_name1') {
                    $productPrices[$row->id] = $row->unit_price;
                }
            }
        }
        foreach (['shoujou', 'joukyou'] as $field) {
            $options[$field] = array_combine(array_values($options[$field]), array_values($options[$field]));
        }
        $legacyAllDispositions = $options['kekka'];
        $legacyActiveDispositions = array_diff_key($options['kekka'], array_flip($inactive['kekka'] ?? []));
        if ((string)$this->getCurrentUser()?->auth !== '9') {
            $options['kekka'] = array_intersect_key($options['kekka'], array_flip([1, 2, 3, 4, 5, 7, 8, 9, 10]));
        }
        for ($i = 2; $i <= 5; $i++) {
            $options["product_name$i"] = $options['product_name1'];
            $inactive["product_name$i"] = $inactive['product_name1'] ?? [];
        }
        $inputOptions = $options;
        foreach ($inactive as $field => $ids) {
            $inputOptions[$field] = array_diff_key($inputOptions[$field], array_flip($ids));
        }
        $users = $this->fetchTable('Users')->find('list', keyField: 'id', valueField: 'user_name')->toArray();
        $canDelete = (string)$this->getCurrentUser()?->auth === '9';
        $auth_loginid = $this->getCurrentUser()?->loginid ?? '';
        $auth_user_name = $this->getCurrentUser()?->user_name ?? '';
        $this->set(compact('isContact', 'fields', 'options', 'inputOptions', 'users', 'canDelete', 'productPrices'));
        $this->set(compact('legacyAllDispositions', 'legacyActiveDispositions', 'auth_loginid', 'auth_user_name'));
        $this->set('warning', 0);
    }
}
