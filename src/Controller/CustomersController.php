<?php
declare(strict_types=1);

namespace App\Controller;

class CustomersController extends AppController
{
    use LegacyCrmActionsTrait;

    protected $Customers;
    protected $Contacts;

    public function initialize(): void
    {
        parent::initialize();
        $this->Customers = $this->fetchTable('Customers');
        $this->Contacts = $this->fetchTable('Contacts');
    }

    public function clearSession()
    {
        $session = $this->request->getSession();
        $session->delete('Customers.search');

        return $this->redirect(['action' => 'index']);
    }

    public function index(?string $lead_name = null)
    {
        if ($lead_name !== null) {
            $this->request->getSession()->write('lead_name', $lead_name);
        }
        return $this->legacyIndex();
    }

    public function add()
    {
        return $this->legacyForm(null);
    }

    public function view(?string $id = null, ?string $lead_name = null)
    {
        if ($lead_name !== null) {
            $this->request->getSession()->write('lead_name', $lead_name);
        }
        // Match the original Customer View: look up by ID without list-scope filters.
        $record = $this->Customers->find()->where(['Customers.id' => $id])->firstOrFail();
        if ($this->request->is(['post', 'put', 'patch'])) {
            if ($this->request->getData('delete') !== null) {
                return $this->legacyDelete($id);
            }
            return $this->redirect(['action' => 'index']);
        }
        $customer = $record;
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

    public function edit(?string $id = null)
    {
        return $this->legacyForm($id);
    }

    public function delete(?string $id = null)
    {
        return $this->legacyDelete($id);
    }

    public function jump(?string $phone = null)
    {
        if (!$phone) {
            return $this->redirect(['action' => 'index']);
        }
        $normalized = preg_replace('/\D/u', '', $phone);
        $query = $this->legacyQuery()->where(['OR' => ['Customers.phone1' => $normalized, 'Customers.tel' => $phone]]);
        $matches = $query->all();
        if ($matches->count() === 0) {
            return $this->redirect(['action' => 'view', $this->autoAdd($phone)]);
        }
        if ($matches->count() === 1) {
            return $this->redirect(['action' => 'view', $matches->first()->id]);
        }
        $this->request->getSession()->write('Customers.search', ['s_phone1' => $normalized]);
        return $this->redirect(['action' => 'index']);
    }

    protected function autoAdd(?string $phone = null): int
    {
        $user = $this->getCurrentUser();
        $data = \App\Service\LegacyCrm::normalizeCustomer(['tel' => $phone, 'customer_name' => $phone]);
        $data += ['created' => date('Y-m-d H:i:s'), 'updated' => date('Y-m-d H:i:s'),
            'created_user_id' => $user?->id ?? 0, 'updated_user_id' => $user?->id ?? 0, 'belong' => $user?->belong ?? 0];
        if ($this->Customers->getSchema()->hasColumn('client_cd')) {
            $data['client_cd'] = $this->Customers->getSchema()->getColumnType('client_cd') === 'integer' ? random_int(100000000, 2000000000) : 'ameyo_' . bin2hex(random_bytes(8));
        }
        $customer = $this->Customers->newEntity($data, ['validate' => false]);
        $this->Customers->saveOrFail($customer);
        return (int)$customer->id;
    }

    public function ameyo(?string $phone = null, ?string $lead_name = null)
    {
        $this->request->getSession()->write('lead_name', $lead_name ?? '');
        if ($phone === null || $phone === '') {
            return $this->redirect(['action' => 'index']);
        }
        // The legacy handoff matches phone1 exactly and opens the latest-created record.
        $customer = $this->Customers->find()->where(['Customers.phone1' => $phone])
            ->orderBy(['Customers.created' => 'DESC', 'Customers.id' => 'DESC'], true)
            ->first();
        if ($customer) {
            return $this->redirect(['action' => 'view', $customer->id, $lead_name ?? '']);
        }
        if ($lead_name === null || $lead_name === '') {
            return $this->redirect(['action' => 'add']);
        }
        $this->log('エラーコード001: 顧客情報が見つかりませんでした。', 'error');
        return $this->redirect(['action' => 'index', $lead_name]);
    }
}
