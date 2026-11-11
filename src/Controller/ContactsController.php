<?php
declare(strict_types=1);

namespace App\Controller;

class ContactsController extends AppController
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
        $session->delete('Contacts.search');

        return $this->redirect(['action' => 'index']);
    }

    public function index()
    {
        return $this->legacyIndex();
    }

    public function add(?string $customer_id = null)
    {
        return $this->legacyForm(null, $customer_id);
    }

    public function view(?string $id = null)
    {
        return $this->legacyView($id);
    }

    public function edit(?string $id = null, ?string $customer_id = null)
    {
        return $this->legacyForm($id);
    }

    public function delete(?string $id = null)
    {
        return $this->legacyDelete($id);
    }

    protected function syncLegacyDnc(string $customerId, mixed $dispositionId): string
    {
        $customer = $this->Customers->find()->select(['phone1'])
            ->where(['Customers.id' => $customerId])->firstOrFail();
        $disposition = $dispositionId === null || $dispositionId === '' ? null
            : $this->fetchTable('MtDispositions')->find()->select(['item_name'])
                ->where(['id' => $dispositionId, 'deleted IS' => null, 'active_flg' => 1])->first();
        return $this->callDncAPI((string)$customer->phone1, 15, (string)($disposition?->item_name ?? ''));
    }

    protected function callDncAPI(string $phone, int $leadId, string $status): string
    {
        return (new \App\Service\AmeyoDnc())->call($phone, $leadId, $status);
    }
}
