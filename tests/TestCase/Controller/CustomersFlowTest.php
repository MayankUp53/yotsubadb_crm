<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\CustomersController;
use App\Controller\ContactsController;
use App\Model\Entity\User;
use App\Model\Table\CustomersTable;
use App\Model\Table\ContactsTable;
use App\Service\LegacyCrm;
use App\Service\LegacyCrmImporter;
use App\View\AppView;
use Cake\Database\Connection;
use Cake\Database\Driver\Sqlite;
use Cake\Datasource\ConnectionManager;
use Cake\Http\ServerRequest;
use Cake\Http\Session;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use PHPUnit\Framework\TestCase;

/** All data is isolated in SQLite; application/customer databases are never written. */
class CustomersFlowTest extends TestCase
{
    private Connection $connection;
    private CustomersTable $customers;
    private ContactsTable $contacts;

    protected function setUp(): void
    {
        ConnectionManager::setConfig('legacy_test', ['className' => Connection::class, 'driver' => Sqlite::class, 'database' => ':memory:']);
        $this->connection = ConnectionManager::get('legacy_test');
        foreach (['customers' => LegacyCrm::customerFields() + ['name' => '', 'phone' => '', 'phone_only' => '', 'tel_only' => '', 'client_cd' => '', 'belong' => ''], 'contacts' => ['customer_id' => ''] + LegacyCrm::contactFields()] as $name => $fields) {
            $columns = [];
            foreach ($fields as $field => $label) {
                $columns[] = $field . ($field === 'belong' ? ' INTEGER DEFAULT 0' : ($field === 'customer_id' ? ' INTEGER' : ($field === 'sakiokuri_datetime' ? ' DATETIME' : ' TEXT')));
            }
            $this->connection->execute('CREATE TABLE ' . $name . ' (id INTEGER PRIMARY KEY AUTOINCREMENT, ' . implode(',', $columns) . ', created DATETIME, updated DATETIME, deleted DATETIME, created_user_id INTEGER, updated_user_id INTEGER, deleted_user_id INTEGER)');
        }
        $this->connection->execute('CREATE TABLE users (id INTEGER PRIMARY KEY, user_name TEXT)');
        $this->connection->insert('users', ['id' => 7, 'user_name' => 'Operator']);
        $this->connection->execute('CREATE TABLE mt_dispositions (id INTEGER PRIMARY KEY, item_name TEXT, deleted DATETIME, active_flg INTEGER DEFAULT 1)');
        $this->connection->insert('mt_dispositions', ['id' => 1, 'item_name' => '受注']);
        $this->customers = new CustomersTable(['connection' => $this->connection, 'alias' => 'Customers']);
        $this->contacts = new ContactsTable(['connection' => $this->connection, 'alias' => 'Contacts']);
        $this->contacts->getAssociation('Customers')->setTarget($this->customers);
        $this->customers->getAssociation('Contacts')->setTarget($this->contacts);
        TableRegistry::getTableLocator()->clear();
        TableRegistry::getTableLocator()->set('Customers', $this->customers);
        TableRegistry::getTableLocator()->set('Contacts', $this->contacts);
        TableRegistry::getTableLocator()->set('MtDispositions', new Table(['connection' => $this->connection, 'table' => 'mt_dispositions', 'alias' => 'MtDispositions']));
        TableRegistry::getTableLocator()->set('Users', new Table(['connection' => $this->connection, 'table' => 'users', 'alias' => 'Users']));
        Router::reload();
        Router::createRouteBuilder('/')->scope('/', function (RouteBuilder $routes): void {
            $routes->setRouteClass(DashedRoute::class);
            $routes->fallbacks();
        });
    }

    protected function tearDown(): void
    {
        TableRegistry::getTableLocator()->clear();
        $this->connection->getDriver()->disconnect();
        ConnectionManager::drop('legacy_test');
    }

    private function controller(string $name, string $action, string $method = 'GET', array $data = [], string $auth = '9')
    {
        $request = (new ServerRequest(['environment' => ['REQUEST_METHOD' => $method], 'session' => new Session()]))->withParsedBody($data)
            ->withAttribute('params', ['controller' => $name, 'action' => $action, 'plugin' => null, '_ext' => null, 'pass' => []]);
        Router::setRequest($request);
        $controller = $name === 'Customers' ? new LegacyCustomerHarness($request, $name) : new LegacyContactHarness($request, $name);
        $controller->auth = $auth;
        return $controller;
    }

    private function addCustomer(array $data = []): int
    {
        $controller = $this->controller('Customers', 'add', 'POST', $data + ['customer_name' => '山田', 'tel' => '03-1234-5678', 'name_kana' => 'ヤマダ']);
        self::assertNotNull($controller->add(), json_encode($controller->viewBuilder()->getVar('record')?->getErrors()));
        return (int)$this->customers->find()->select(['id'])->orderBy(['id' => 'DESC'])->first()->id;
    }

    private function addContact(int $customerId, array $data = []): int
    {
        $controller = $this->controller('Contacts', 'add', 'POST', $data + ['kekka' => '1', 'memo' => '初回']);
        self::assertNotNull($controller->add((string)$customerId), json_encode($controller->viewBuilder()->getVar('record')?->getErrors()));
        return (int)$this->contacts->find()->select(['id'])->orderBy(['id' => 'DESC'])->first()->id;
    }

    public function testContactDncAddBeforeSaveAndEditAfterSave(): void
    {
        $id = $this->addCustomer();
        $add = $this->controller('Contacts', 'add', 'POST', ['kekka' => '1', 'memo' => 'Before']);
        $add->dncResponse = 'DNC response';
        self::assertNotNull($add->add((string)$id));
        self::assertSame([['0312345678', 15, '受注', 0, null]], $add->dncCalls);
        self::assertStringContainsString('DNC response', $add->getRequest()->getSession()->read('Flash.flash.0.message'));
        $contactId = (int)$this->contacts->find()->first()->id;
        $edit = $this->controller('Contacts', 'edit', 'POST', ['kekka' => '1', 'memo' => 'After']);
        self::assertNotNull($edit->edit((string)$contactId));
        self::assertSame([['0312345678', 15, '受注', 1, 'After']], $edit->dncCalls);
    }

    public function testContactEditRedirectMatchesLegacyReferrerFlow(): void
    {
        $customerId = $this->addCustomer();
        $contactId = $this->addContact($customerId);
        foreach ([
            ['/customers/view/' . $customerId, '/customers/view/' . $customerId],
            ['/contacts/view/' . $contactId, '/customers/view/' . $customerId],
            ['/contacts/edit/' . $contactId, '/contacts'],
            ['/contacts', '/contacts'],
            ['', '/contacts'],
        ] as [$referrer, $expected]) {
            $controller = $this->controller('Contacts', 'edit', 'POST', ['kekka' => '1', 'memo' => $referrer]);
            $controller->setRequest($controller->getRequest()->withEnv('HTTP_REFERER', $referrer === '' ? '' : 'http://localhost' . $referrer));
            self::assertStringEndsWith($expected, $controller->edit((string)$contactId)->getHeaderLine('Location'));
            self::assertSame($referrer, $this->contacts->get($contactId)->memo);
            self::assertCount(1, $controller->dncCalls);
        }
        $add = $this->controller('Contacts', 'add', 'POST', ['kekka' => '1']);
        $add->setRequest($add->getRequest()->withEnv('HTTP_REFERER', 'http://localhost/contacts'));
        self::assertStringEndsWith('/customers/view/' . $customerId, $add->add((string)$customerId)->getHeaderLine('Location'));
    }

    public function testContactDncFailureTimingAndNavigation(): void
    {
        $id = $this->addCustomer();
        $invalid = $this->controller('Contacts', 'add', 'POST', ['kekka' => '1', 'contact_company' => '']);
        self::assertNull($invalid->add((string)$id));
        self::assertCount(1, $invalid->dncCalls);
        self::assertSame(0, $this->contacts->find()->count());
        $missingResult = $this->controller('Contacts', 'add', 'POST', ['memo' => 'No result']);
        self::assertNotNull($missingResult->add((string)$id));
        self::assertSame('', $missingResult->dncCalls[0][2]);
        $contactId = $this->addContact($id);
        $edit = $this->controller('Contacts', 'edit', 'POST', ['kekka' => '1', 'contact_company' => '']);
        self::assertNull($edit->edit((string)$contactId));
        self::assertSame([], $edit->dncCalls);
        foreach (['back', 'add'] as $button) {
            $nav = $this->controller('Contacts', 'edit', 'POST', [$button => '1']);
            self::assertNotNull($nav->edit((string)$contactId));
            self::assertSame([], $nav->dncCalls);
        }
        $get = $this->controller('Contacts', 'view');
        $get->view((string)$contactId);
        self::assertSame([], $get->dncCalls);
    }

    public function testLegacyContactValidationChecksOnlySubmittedInquiryFields(): void
    {
        $validator = $this->contacts->getValidator('legacy');
        $fields = ['contact_company', 'contact_tantou', 'date_want', 'category_cd', 'status_cd', 'correspond_cd'];
        foreach ([true, false] as $isNew) {
            self::assertSame([], $validator->validate([], $isNew));
            foreach ($fields as $field) {
                foreach (['', null, '   '] as $blank) {
                    self::assertArrayHasKey($field, $validator->validate([$field => $blank], $isNew));
                }
                self::assertSame([], $validator->validate([$field => '0'], $isNew));
                self::assertSame([], $validator->validate([$field => 'value'], $isNew));
            }
            self::assertSame([], $validator->validate(['kekka' => '', 'quantity1' => '-2', 'unit_price1' => 'text', 'price1' => '-10', 'sakiokuri_datetime' => 'not-a-date'], $isNew));
        }
        $customerId = $this->addCustomer();
        $contactId = $this->addContact($customerId, ['contact_company' => 'Original company', 'quantity1' => '-2', 'price1' => '-10']);
        self::assertEquals(-2, $this->contacts->get($contactId)->quantity1);
        foreach ($fields as $field) {
            $edit = $this->controller('Contacts', 'edit', 'POST', [$field => '']);
            self::assertNull($edit->edit((string)$contactId));
            self::assertNotEmpty($edit->viewBuilder()->getVar('record')->getError($field));
            self::assertSame([], $edit->dncCalls);
        }
        self::assertSame('Original company', $this->contacts->get($contactId)->contact_company);
    }

    public function testContactDncUsesOnlyActiveUndeletedDispositionName(): void
    {
        $id = $this->addCustomer();
        foreach ([['active_flg' => 0], ['active_flg' => 1, 'deleted' => '2026-01-01']] as $changes) {
            $this->connection->update('mt_dispositions', $changes, ['id' => 1]);
            $add = $this->controller('Contacts', 'add', 'POST', ['kekka' => '1']);
            self::assertNotNull($add->add((string)$id));
            self::assertSame('', $add->dncCalls[0][2]);
        }
    }

    public function testAmeyoUsesExactPhoneLatestCreationAndPreservesLead(): void
    {
        $older = $this->addCustomer(['customer_name' => 'Older']);
        $newer = $this->addCustomer(['customer_name' => 'Newer']);
        $this->connection->update('customers', ['created' => '2025-01-01 00:00:00', 'updated' => '2026-11-10 00:00:00'], ['id' => $older]);
        $this->connection->update('customers', ['created' => '2026-01-01 00:00:00', 'updated' => '2026-01-01 00:00:00'], ['id' => $newer]);
        $controller = $this->controller('Customers', 'ameyo');
        self::assertStringEndsWith('/customers/view/' . $newer . '/lead-A', $controller->ameyo('0312345678', 'lead-A')->getHeaderLine('Location'));
        self::assertSame('lead-A', $controller->getRequest()->getSession()->read('lead_name'));
        // Original Ameyo lookup includes deleted customers as well.
        $this->connection->update('customers', ['deleted' => '2026-11-10 00:00:00'], ['id' => $newer]);
        self::assertStringEndsWith('/customers/view/' . $newer . '/lead-B', $this->controller('Customers', 'ameyo')->ameyo('0312345678', 'lead-B')->getHeaderLine('Location'));
    }

    public function testAmeyoAndCustomerViewUseOriginalUnscopedLookup(): void
    {
        $id = $this->addCustomer();
        $contactId = $this->addContact($id, ['list_name' => 'Saved lead']);
        $removedContact = $this->addContact($id);
        $this->contacts->updateAll(['deleted' => '2026-01-01'], ['id' => $removedContact]);
        $this->customers->updateAll(['belong' => 2], ['id' => $id]);
        foreach ([null, '2026-01-01'] as $deleted) {
            $this->customers->updateAll(['deleted' => $deleted], ['id' => $id]);
            $ameyo = $this->controller('Customers', 'ameyo');
            $ameyo->belong = 1;
            self::assertStringEndsWith('/customers/view/' . $id . '/Incoming', $ameyo->ameyo('0312345678', 'Incoming')->getHeaderLine('Location'));
            $view = $this->controller('Customers', 'view');
            $view->belong = 1;
            $view->view((string)$id, 'Incoming');
            self::assertSame($id, $view->viewBuilder()->getVar('customer')->id);
            self::assertSame('Incoming', $view->viewBuilder()->getVar('contact')->list_name);
            $history = $view->viewBuilder()->getVar('contacts')->toArray();
            self::assertCount(1, $history);
            self::assertSame($contactId, $history[0]->id);
            $list = $this->controller('Customers', 'index');
            $list->belong = 1;
            $list->index();
            self::assertCount(0, $list->viewBuilder()->getVar('records'));
        }
    }

    public function testAmeyoMissingPhoneAndUnmatchedBranchesDoNotCreateCustomers(): void
    {
        $this->addCustomer();
        $controller = $this->controller('Customers', 'ameyo');
        $controller->getRequest()->getSession()->write('lead_name', 'stale');
        self::assertStringEndsWith('/customers', $controller->ameyo()->getHeaderLine('Location'));
        self::assertSame('', $controller->getRequest()->getSession()->read('lead_name'));
        self::assertStringEndsWith('/customers/add', $this->controller('Customers', 'ameyo')->ameyo('03-1234-5678')->getHeaderLine('Location'));
        self::assertStringEndsWith('/customers/index/lead-A', $this->controller('Customers', 'ameyo')->ameyo('unmatched', 'lead-A')->getHeaderLine('Location'));
        self::assertSame(1, $this->customers->find()->count());
    }

    public function testCustomerAddAndImportLeaveCodeAndBelongToDatabaseDefaults(): void
    {
        $controller = $this->controller('Customers', 'add', 'POST', ['customer_name' => 'Default customer', 'tel' => '0312345678']);
        $controller->belong = 8;
        self::assertNotNull($controller->add());
        $customer = $this->customers->find()->where(['customer_name' => 'Default customer'])->firstOrFail();
        self::assertNull($customer->client_cd);
        self::assertSame(0, $customer->belong);
        self::assertSame(7, $customer->created_user_id);
        $result = (new LegacyCrmImporter($this->customers, $this->contacts))->import([
            array_values(LegacyCrm::importFields('customers')),
            ['Imported default', '', '男', '', '70', '', '', '', '', '', '0312345679'],
        ], 'customers', 7, 8);
        self::assertSame(1, $result['inserted']);
        $imported = $this->customers->find()->where(['customer_name' => 'Imported default'])->firstOrFail();
        self::assertNull($imported->client_cd);
        self::assertSame(0, $imported->belong);
        self::assertSame(7, $imported->created_user_id);
        // An update must preserve existing ownership and client code.
        $this->customers->updateAll(['client_cd' => 'existing-code', 'belong' => 8], ['id' => $customer->id]);
        $edit = $this->controller('Customers', 'edit', 'POST', ['customer_name' => 'Default customer', 'tel' => '0312345678']);
        $edit->belong = 2;
        self::assertNotNull($edit->edit((string)$customer->id));
        $updated = $this->customers->get($customer->id);
        self::assertSame('existing-code', $updated->client_cd);
        self::assertSame(8, $updated->belong);
    }

    public function testCustomerCreateEditAndInvalidData(): void
    {
        $id = $this->addCustomer(['address4' => 'Building', 'deleted' => '2020-01-01']);
        $customer = $this->customers->get($id);
        self::assertSame('0312345678', $customer->phone1);
        self::assertSame('Building', $customer->address4);
        self::assertNull($customer->deleted);
        self::assertSame(7, $customer->created_user_id);
        $controller = $this->controller('Customers', 'edit', 'PATCH', ['customer_name' => '山田', 'tel' => '03-1234-5678', 'address4' => 'Changed']);
        self::assertNotNull($controller->edit((string)$id), json_encode($controller->viewBuilder()->getVar('record')?->getErrors()));
        self::assertSame('Changed', $this->customers->get($id)->address4);
        $invalid = $this->controller('Customers', 'add', 'POST', ['tel' => '', 'customer_name' => '', 'address4' => 'Keep']);
        self::assertNull($invalid->add());
        self::assertNotEmpty($invalid->viewBuilder()->getVar('record')->getErrors());
        self::assertSame('Keep', $invalid->viewBuilder()->getVar('record')->address4);
        self::assertNotNull($this->controller('Customers', 'add', 'POST', ['back' => '1'])->add());
    }

    public function testCompositeDuplicateAndSearchSemantics(): void
    {
        $this->addCustomer();
        $this->addCustomer(['customer_name' => '田中']);
        $duplicate = $this->controller('Customers', 'add', 'POST', ['customer_name' => '山田', 'tel' => '03-1234-5678']);
        self::assertNull($duplicate->add());
        self::assertNotEmpty($duplicate->viewBuilder()->getVar('record')->getError('customer_name'));
        self::assertSame(2, LegacyCrm::search($this->customers, ['s_customer_name' => '山田,田中'])->count());
        self::assertSame(1, LegacyCrm::search($this->customers, ['s_customer_name' => '山 田'])->count());
        self::assertSame(0, LegacyCrm::search($this->customers, ['s_mobile' => 'missing'])->count());
    }

    public function testContactProductsCheckboxesEditAndSearch(): void
    {
        $customerId = $this->addCustomer();
        $id = $this->addContact($customerId, ['quantity1' => '2', 'unit_price1' => '150', 'shoujou' => ['右目', '左目'], 'day_service_days' => ['月', '金'], 'sakiokuri_datetime' => '2026-11-15T10:30']);
        $contact = $this->contacts->get($id);
        self::assertSame('[右目][左目]', $contact->shoujou);
        self::assertSame('[月][金]', $contact->day_service_days);
        self::assertEquals(2, $contact->day_service_times);
        self::assertEquals(300, $contact->total_price);
        self::assertSame(1, LegacyCrm::search($this->contacts, ['s_total_price_from' => '300', 's_sakiokuri_date_to' => '2026/11/15', 's_customer_name' => '山田'])->count());
        self::assertSame(0, LegacyCrm::search($this->contacts, ['s_total_price_from' => '301'])->count());
        $edit = $this->controller('Contacts', 'edit', 'PATCH', ['kekka' => '1', 'memo' => 'Changed', 'customer_id' => '999', 'deleted' => '2020-01-01']);
        self::assertNotNull($edit->edit((string)$id));
        self::assertEquals($customerId, $this->contacts->get($id)->customer_id);
        self::assertSame('Changed', $this->contacts->get($id)->memo);
        self::assertNull($this->contacts->get($id)->deleted);
    }

    public function testLegacyEditAndContactAddAcceptOutOfScopeAndDeletedRecords(): void
    {
        $id = $this->addCustomer();
        foreach ([null, '2026-01-01 00:00:00'] as $deleted) {
            $this->customers->updateAll(['belong' => 2, 'deleted' => $deleted], ['id' => $id]);
            $get = $this->controller('Customers', 'edit');
            $get->belong = 1;
            $get->edit((string)$id);
            self::assertSame($id, $get->viewBuilder()->getVar('record')->id);
            $edit = $this->controller('Customers', 'edit', 'POST', ['customer_name' => 'Updated', 'tel' => '03-1234-5678', 'address4' => 'Changed']);
            $edit->belong = 1;
            self::assertNotNull($edit->edit((string)$id));
            self::assertSame('Changed', $this->customers->get($id)->address4);
            self::assertSame(2, $this->customers->get($id)->belong);
            self::assertSame($deleted !== null, $this->customers->get($id)->deleted !== null);
            $add = $this->controller('Contacts', 'add', 'POST', ['customer_id' => (string)$id, 'kekka' => '1', 'memo' => 'Created']);
            $add->belong = 1;
            self::assertNotNull($add->add());
            self::assertCount(1, $add->dncCalls);
            $contactId = (int)$this->contacts->find()->orderBy(['id' => 'DESC'])->first()->id;
            $this->contacts->updateAll(['deleted' => $deleted], ['id' => $contactId]);
            $contactGet = $this->controller('Contacts', 'edit');
            $contactGet->belong = 1;
            $contactGet->edit((string)$contactId);
            self::assertSame($contactId, $contactGet->viewBuilder()->getVar('record')->id);
            $contactEdit = $this->controller('Contacts', 'edit', 'POST', ['kekka' => '1', 'memo' => 'Updated']);
            $contactEdit->belong = 1;
            self::assertNotNull($contactEdit->edit((string)$contactId));
            self::assertSame('Updated', $this->contacts->get($contactId)->memo);
            self::assertSame($deleted !== null, $this->contacts->get($contactId)->deleted !== null);
            self::assertCount(1, $contactEdit->dncCalls);
            self::assertSame(0, LegacyCrm::search($this->customers, [], 1)->count());
        }
    }

    public function testContactViewUsesIdAndKeepsOnlyUndeletedCustomerHistory(): void
    {
        $customerId = $this->addCustomer();
        $activeId = $this->addContact($customerId, ['list_name' => 'Active lead']);
        $deletedId = $this->addContact($customerId, ['list_name' => 'Deleted lead']);
        $otherCustomer = $this->addCustomer(['customer_name' => 'Other customer']);
        $this->addContact($otherCustomer);
        $this->contacts->updateAll(['deleted' => '2026-01-01'], ['id' => $deletedId]);
        foreach ([null, '2026-01-01'] as $deletedParent) {
            $this->customers->updateAll(['belong' => 2, 'deleted' => $deletedParent], ['id' => $customerId]);
            foreach ([$activeId, $deletedId] as $selectedId) {
                $controller = $this->controller('Contacts', 'view');
                $controller->belong = 1;
                $controller->view((string)$selectedId);
                self::assertSame($selectedId, $controller->viewBuilder()->getVar('record')->id);
                self::assertSame($customerId, $controller->viewBuilder()->getVar('customer')->id);
                $history = $controller->viewBuilder()->getVar('contacts')->toArray();
                self::assertCount(1, $history);
                self::assertSame($activeId, $history[0]->id);
                self::assertSame([], $controller->dncCalls);
            }
        }
    }

    public function testLegacyDeleteUsesIdForOutOfScopeAndAlreadyDeletedRecords(): void
    {
        foreach ([null, '2026-01-01 00:00:00'] as $deleted) {
            $id = $this->addCustomer(['customer_name' => 'Delete-' . ($deleted ?? 'active')]);
            $contactId = $this->addContact($id);
            $this->customers->updateAll(['belong' => 2, 'deleted' => $deleted], ['id' => $id]);
            $this->contacts->updateAll(['deleted' => $deleted], ['id' => $contactId]);
            $contactDelete = $this->controller('Contacts', 'delete', 'POST');
            $contactDelete->belong = 1;
            self::assertNotNull($contactDelete->delete((string)$contactId));
            self::assertSame([], $contactDelete->dncCalls);
            self::assertSame(7, $this->contacts->get($contactId)->deleted_user_id);
            $customerDelete = $this->controller('Customers', 'delete', 'POST');
            $customerDelete->belong = 1;
            self::assertNotNull($customerDelete->delete((string)$id));
            self::assertNotNull($this->customers->get($id)->deleted);
            self::assertSame(7, $this->customers->get($id)->deleted_user_id);
        }
    }

    public function testDeleteRequiresPostAndAdminAndHidesHistory(): void
    {
        $customerId = $this->addCustomer();
        $id = $this->addContact($customerId);
        foreach ([['GET', '9', \Cake\Http\Exception\MethodNotAllowedException::class], ['POST', '0', \Cake\Http\Exception\ForbiddenException::class]] as [$method, $auth, $exception]) {
            try {
                $this->controller('Contacts', 'delete', $method, [], $auth)->delete((string)$id);
                self::fail('Expected deletion rejection');
            } catch (\Throwable $e) {
                self::assertInstanceOf($exception, $e);
            }
        }
        $this->controller('Contacts', 'delete', 'POST')->delete((string)$id);
        self::assertSame(0, LegacyCrm::search($this->contacts)->count());
        self::assertSame(7, $this->contacts->get($id)->deleted_user_id);
        $this->addContact($customerId);
        $this->controller('Customers', 'delete', 'POST')->delete((string)$customerId);
        self::assertSame(0, LegacyCrm::search($this->customers)->count());
        self::assertSame(0, LegacyCrm::search($this->contacts)->count());
    }

    public function testSearchPersistenceAndCsv(): void
    {
        $this->addCustomer();
        $controller = $this->controller('Customers', 'index', 'POST', ['searching' => '1', 'Search' => ['s_customer_name' => '山田']]);
        self::assertNotNull($controller->index());
        self::assertSame(['s_customer_name' => '山田'], $controller->getRequest()->getSession()->read('Customers.search'));
        $export = $this->controller('Customers', 'index', 'POST', ['download' => '1', 'Search' => ['s_customer_name' => 'missing']])->index();
        self::assertStringNotContainsString('山田', (string)$export->getBody());
        $export = $this->controller('Contacts', 'index', 'POST', ['download' => '1'])->index();
        self::assertStringContainsString('購入金額合計', mb_convert_encoding((string)$export->getBody(), 'UTF-8', 'SJIS-win'));
    }

    private function exportedCsv(string $controller, array $search = [], ?int $belong = null): array
    {
        $action = $this->controller($controller, 'index', 'POST', ['download' => '1', 'Search' => $search]);
        $action->belong = $belong;
        $response = $action->index();
        $body = (string)$response->getBody();
        $stream = fopen('php://temp', 'w+');
        fwrite($stream, mb_convert_encoding($body, 'UTF-8', 'SJIS-win'));
        rewind($stream);
        $rows = [];
        while (($row = fgetcsv($stream, 0, ',', '"', '')) !== false) $rows[] = $row;
        fclose($stream);
        return [$response, $rows, $body];
    }

    public function testCsvHeadersMatchActiveLegacyControllerDefinitions(): void
    {
        foreach (['Customers', 'Contacts'] as $controller) {
            $source = file_get_contents(ROOT . '/app/Controller/' . $controller . 'Controller.php');
            $code = '';
            foreach (token_get_all($source) as $token) {
                if (is_array($token)) {
                    if (!in_array($token[0], [T_COMMENT, T_DOC_COMMENT], true)) $code .= $token[1];
                } else $code .= $token;
            }
            self::assertSame(1, preg_match('/\$head\s*=\s*array\((.*?)\);/s', $code, $head));
            preg_match_all("/mb_convert_encoding\\('([^']+)'/u", $head[1], $labels);
            [, $rows] = $this->exportedCsv($controller);
            self::assertSame($labels[1], $rows[0]);
        }
    }

    public function testCustomerCsvUsesOriginal21ColumnsEncodingAndAuditFields(): void
    {
        $id = $this->addCustomer(['customer_name' => '山田 "太郎",会社', 'gender' => '1', 'age' => '70', 'dob' => '1956', 'nendai' => '7',
            'mobile' => '09012345678', 'zip' => '0123456', 'address1' => '県', 'address2' => '市', 'address3' => '町', 'address4' => '建物']);
        $this->connection->update('customers', ['created' => '2026-01-01 10:00:00', 'updated' => '2026-02-01 11:00:00',
            'deleted' => '2026-03-01 12:00:00', 'deleted_user_id' => 7, 'belong' => 2], ['id' => $id]);
        [$response, $rows, $bytes] = $this->exportedCsv('Customers', [], 1);
        self::assertCount(2, $rows);
        self::assertSame(['ＩＤ', 'ＴＥＬ', 'ＴＥＬ２', '携帯電話', '名前', 'かな', '性別', '年齢', '生年', '年代', '郵便番号',
            '住所（県）', '住所（市区郡）', '住所（町番地）', '住所（建物名）', '登録者', '登録日', '更新者', '更新日', '削除者', '削除日'], $rows[0]);
        self::assertSame([(string)$id, '03-1234-5678', '0312345678', '09012345678', '山田 "太郎",会社', 'ヤマダ', '男', '70', '1956', '70',
            '0123456', '県', '市', '町', '建物', 'Operator', '2026-01-01 10:00:00', 'Operator', '2026-02-01 11:00:00', 'Operator', '2026-03-01 12:00:00'], $rows[1]);
        self::assertStringStartsWith(mb_convert_encoding('ＩＤ,ＴＥＬ,ＴＥＬ２', 'SJIS-win', 'UTF-8'), $bytes);
        self::assertStringNotContainsString("\xEF\xBB\xBF", $bytes);
        self::assertSame('application/vnd.ms-excel', $response->getHeaderLine('Content-Type'));
        self::assertMatchesRegularExpression('/顧客情報_\d{4}-\d{2}-\d{2}_\d{2}:\d{2}:\d{2}\.csv/', mb_convert_encoding($response->getHeaderLine('Content-Disposition'), 'UTF-8', 'SJIS-win'));
    }

    public function testContactCsvUsesOriginal38ColumnsAndMasterLabels(): void
    {
        $this->connection->execute('CREATE TABLE mt_products (id INTEGER PRIMARY KEY, item_name TEXT, deleted DATETIME, active_flg INTEGER)');
        $this->connection->insert('mt_products', ['id' => 3, 'item_name' => 'P003_サプリ_ignored', 'active_flg' => 0]);
        $this->connection->execute('CREATE TABLE mt_statuses (id INTEGER PRIMARY KEY, item_name TEXT, deleted DATETIME)');
        $this->connection->insert('mt_statuses', ['id' => 4, 'item_name' => '購入理由']);
        $id = $this->addCustomer(['gender' => '2', 'nendai' => '6']);
        $contactId = $this->addContact($id, ['kekka' => '1', 'list_name' => 'Lead A', 'product_name1' => '3', 'quantity1' => '2', 'price1' => '300',
            'price2' => '40', 'reason' => '4', 'shoujou' => ['症状'], 'impression' => ['明るい'], 'joukyou' => ['状況'],
            'day_service_times' => '1', 'day_service_days' => ['月'], 'kazoku_kankyou' => '2', 'fukuyaku' => '薬', 'supply' => 'サプリ',
            'memo' => "一行\r\n二行\n三行\r四行"]);
        $this->connection->update('customers', ['created' => '2020-01-01 00:00:00', 'deleted' => '2026-01-01 00:00:00', 'belong' => 2], ['id' => $id]);
        $this->connection->update('contacts', ['created' => '2026-11-11 12:00:00', 'deleted' => '2026-11-12 00:00:00'], ['id' => $contactId]);
        [$response, $rows] = $this->exportedCsv('Contacts', [], 1);
        self::assertCount(2, $rows);
        self::assertCount(38, $rows[0]);
        self::assertCount(38, $rows[1]);
        self::assertSame(['電話番号（「-」無）', '電話番号（「-」有）', '携帯電話', '名前', 'かな'], array_slice($rows[0], 0, 5));
        self::assertSame('商品ステイタス', $rows[0][24]);
        self::assertSame('女', $rows[1][5]);
        self::assertSame('60', $rows[1][8]);
        self::assertSame(['2020-01-01 00:00:00', '水', '受注', 'Lead A', 'operator', 'Operator', '2026-11-11 12:00:00'], array_slice($rows[1], 14, 7));
        self::assertSame(['P003', 'サプリ', 'サンプル', '2', '300', '340', '購入理由', '[症状]', '[明るい]', '[状況]', '1', '[月]', '夫婦同居', '薬', 'サプリ', '一行&二行&三行&四行'], array_slice($rows[1], 22));
        self::assertMatchesRegularExpression('/対応履歴情報_\d{4}-\d{2}-\d{2}_\d{2}:\d{2}:\d{2}\.csv/', mb_convert_encoding($response->getHeaderLine('Content-Disposition'), 'UTF-8', 'SJIS-win'));
    }

    public function testLegacyCsvUsesPostedFiltersWithoutListScopeOrSearchConnectors(): void
    {
        $this->addCustomer(['customer_name' => '山田']);
        $this->addCustomer(['customer_name' => '田中']);
        [, $rows] = $this->exportedCsv('Customers', ['s_customer_name' => '山田,田中']);
        self::assertCount(1, $rows); // The old download uses a literal LIKE, not Search plugin OR splitting.
        [, $rows] = $this->exportedCsv('Customers', ['s_customer_name' => '田中']);
        self::assertCount(2, $rows);
        self::assertSame('田中', $rows[1][4]);
        $controller = $this->controller('Customers', 'index', 'POST', ['download' => '1']);
        $controller->getRequest()->getSession()->write('Customers.search', ['s_customer_name' => 'missing']);
        $body = mb_convert_encoding((string)$controller->index()->getBody(), 'UTF-8', 'SJIS-win');
        self::assertStringContainsString('山田', $body);
        self::assertStringContainsString('田中', $body);
    }

    public function testLegacyContactCsvOperatorsAndDateBounds(): void
    {
        $id = $this->addCustomer(['address1' => 'Tokyo']);
        $contactId = $this->addContact($id, ['memo' => 'Memo', 'list_name' => 'Lead A']);
        $this->connection->update('contacts', ['created' => '2026-11-11 23:59:59', 'updated' => '2026-11-11 23:59:59'], ['id' => $contactId]);
        foreach ([['s_tel' => '1234'], ['s_list_name' => 'Lead'], ['s_customer_name' => '山田'], ['s_created_to' => '2020-01-01'],
            ['s_created_from' => '2026/11/11', 's_created_to' => '2026/11/11'], ['s_updated_from' => '2026-11-11', 's_updated_to' => '2026-11-11']] as $search) {
            [, $rows] = $this->exportedCsv('Contacts', $search);
            self::assertCount(2, $rows, json_encode($search));
        }
        foreach ([['s_phone1' => '0312345678'], ['s_address1' => 'Tokyo'], ['s_ap_name' => 'Operator'], ['s_created_from' => '2026-11-12']] as $search) {
            [, $rows] = $this->exportedCsv('Contacts', $search);
            self::assertCount(1, $rows, json_encode($search));
        }
        // Old export's phone equality compares literal surrounding percent signs.
        $this->connection->update('customers', ['phone1' => '%0312345678%'], ['id' => $id]);
        [, $rows] = $this->exportedCsv('Contacts', ['s_phone1' => '0312345678']);
        self::assertCount(2, $rows);
    }

    public function testLegacyContactCsvRetainsLeftJoinAndDoesNotRequireExistingParent(): void
    {
        $this->connection->insert('contacts', ['customer_id' => 999, 'kekka' => '1', 'list_name' => 'Orphan contact', 'created' => '2026-11-11 12:00:00']);
        [, $rows] = $this->exportedCsv('Contacts');
        self::assertCount(2, $rows);
        self::assertSame('', $rows[1][0]);
        self::assertSame('Orphan contact', $rows[1][17]);
    }

    public function testCustomerAndContactImportsAndFailures(): void
    {
        $importer = new LegacyCrmImporter($this->customers, $this->contacts);
        $row = ['山田', 'ヤマダ', '1', '70', '7', '012-3456', 'Tokyo', 'City', 'Street', 'Building', '03-1234-5678'];
        $rows = [array_values(LegacyCrm::importFields('customers')), $row];
        self::assertSame(1, $importer->import($rows, 'customers', 7)['inserted']);
        self::assertSame(1, $importer->import($rows, 'customers', 7)['updated']);
        self::assertSame('012-3456', $this->customers->get(1)->zip);
        $result = $importer->import([['customer_id', 'kekka', 'memo'], ['1', '1', 'Imported'], ['999', '1', 'Invalid']], 'contacts', 7);
        self::assertSame(1, $result['inserted']);
        self::assertSame(1, $result['failed']);
        self::assertSame(3, $result['errors'][0]['row']);
        $result = $importer->import([['id', 'customer_id', 'kekka', 'memo'], ['1', '1', '1', 'Updated']], 'contacts', 7);
        self::assertSame(1, $result['updated']);
        self::assertSame('Updated', $this->contacts->get(1)->memo);
    }

    public function testViewsRenderLegacyFields(): void
    {
        $id = $this->addCustomer();
        $contactId = $this->addContact($id);
        foreach (['Customers', 'Contacts'] as $name) {
            foreach (['index', 'add', 'edit', 'view'] as $action) {
                $controller = $this->controller($name, $action);
                $recordId = (string)($name === 'Customers' ? $id : $contactId);
                match ($action) { 'index' => $controller->index(), 'add' => $name === 'Contacts' ? $controller->add((string)$id) : $controller->add(), 'edit' => $controller->edit($recordId), 'view' => $controller->view($recordId) };
                $view = new AppView($controller->getRequest());
                $view->set($controller->viewBuilder()->getVars());
                $html = $view->render($controller->viewBuilder()->getTemplate(), false);
                self::assertNotEmpty($html);
                $dom = new \DOMDocument();
                @$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
                self::assertSame(1, (new \DOMXPath($dom))->query('//form')->length);
                if ($action === 'index') {
                    self::assertStringContainsString('Search[s_customer_name]', $html);
                    self::assertStringNotContainsString('>操作<', $html);
                    self::assertStringNotContainsString('データ取込', $html);
                    self::assertStringContainsString('検　索', $html);
                    self::assertStringContainsString('onDblClick', $html);
                } elseif ($name === 'Contacts' && $action === 'edit') {
                    self::assertStringContainsString('name="contact_company"', $html);
                    self::assertStringContainsString('対応履歴更新', $html);
                } else {
                    self::assertStringContainsString($name === 'Customers' && $action !== 'view' ? 'name="customer_name"' : 'name="product_name5"', $html);
                }
            }
        }
    }

    public function testCustomerImportUpdatesDeletedAndOutOfScopeMatchesWithoutRestoring(): void
    {
        $id = $this->addCustomer();
        $contactId = $this->addContact($id);
        $this->customers->updateAll(['belong' => 2], ['id' => $id]);
        self::assertSame(0, LegacyCrm::search($this->customers, [], 1)->count());
        self::assertSame(0, LegacyCrm::search($this->contacts, [], 1)->count());
        self::assertSame(1, LegacyCrm::search($this->contacts, [], 2)->count());
        $importer = new LegacyCrmImporter($this->customers, $this->contacts);
        self::assertSame(1, $importer->import([['id', 'customer_id', 'kekka'], [$contactId, $id, '1']], 'contacts', 7, 1)['failed']);
        $this->customers->updateAll(['client_cd' => 'existing-code', 'created' => '2020-01-01 00:00:00', 'created_user_id' => 3], ['id' => $id]);
        foreach ([null, '2026-01-01 00:00:00'] as $deleted) {
            $this->customers->updateAll(['deleted' => $deleted, 'deleted_user_id' => $deleted ? 4 : null], ['id' => $id]);
            $report = $importer->import([array_values(LegacyCrm::importFields('customers')),
                ['山田', '更新カナ', '女', '70', '70', '', '', '', '', 'Imported building', '03-1234-5678']], 'customers', 7, 1);
            self::assertSame(1, $report['updated'], json_encode($report));
            self::assertSame(0, $report['inserted']);
            self::assertSame(0, $report['failed']);
            self::assertSame(1, $this->customers->find()->count());
            $customer = $this->customers->get($id);
            self::assertSame('Imported building', $customer->address4);
            self::assertSame(2, $customer->belong);
            self::assertSame('existing-code', $customer->client_cd);
            self::assertSame('2020-01-01 00:00:00', $customer->created->format('Y-m-d H:i:s'));
            self::assertSame(3, $customer->created_user_id);
            self::assertSame(7, $customer->updated_user_id);
            self::assertSame($deleted, $customer->deleted?->format('Y-m-d H:i:s'));
            self::assertSame($deleted ? 4 : null, $customer->deleted_user_id);
        }
    }

    public function testOriginalSearchSurvivesPaginationParameters(): void
    {
        $this->addCustomer();
        $this->addCustomer(['customer_name' => 'Other']);
        $controller = $this->controller('Customers', 'index');
        $controller->getRequest()->getSession()->write('Customers.search', ['s_customer_name' => '山田']);
        $controller->setRequest($controller->getRequest()->withQueryParams(['page' => '1']));
        $controller->index();
        self::assertCount(1, $controller->viewBuilder()->getVar('records'));
        self::assertSame('山田', $controller->viewBuilder()->getVar('searchData')['s_customer_name']);
    }

    public function testListPaginationNeverRunsACountQuery(): void
    {
        $customerId = $this->addCustomer();
        for ($i = 0; $i < 45; $i++) {
            $this->connection->insert('contacts', ['customer_id' => $customerId, 'kekka' => '1', 'created' => '2026-11-10 10:00:00', 'updated' => '2026-11-10 10:00:00']);
        }
        foreach ([1 => 20, 2 => 20, 3 => 5] as $page => $expected) {
            $controller = $this->controller('Contacts', 'index');
            $controller->rejectCount = true;
            $controller->setRequest($controller->getRequest()->withQueryParams(['page' => (string)$page]));
            $controller->index();
            $records = $controller->viewBuilder()->getVar('records');
            self::assertCount($expected, $records);
            self::assertSame($page, $records->currentPage());
            self::assertSame($page < 3, $records->hasNextPage());
            self::assertNull($records->totalCount());
        }
    }

    public function testContactBlockCheckboxesSurviveHtmlParsingOnBothViews(): void
    {
        foreach (['mt_categories' => ['右目', '左目'], 'mt_subcategories' => ['状況A', '状況B']] as $table => $labels) {
            $this->connection->execute('CREATE TABLE ' . $table . ' (id INTEGER PRIMARY KEY, item_name TEXT, active_flg INTEGER)');
            foreach ($labels as $i => $label) $this->connection->insert($table, ['id' => $i + 1, 'item_name' => $label, 'active_flg' => 1]);
        }
        $customerId = $this->addCustomer();
        $contactId = $this->addContact($customerId, ['shoujou' => ['左目'], 'impression' => ['明るい'], 'joukyou' => ['状況B']]);
        foreach (['Customers' => $customerId, 'Contacts' => $contactId] as $name => $id) {
            $controller = $this->controller($name, 'view');
            $controller->view((string)$id);
            $view = new AppView($controller->getRequest());
            $view->set($controller->viewBuilder()->getVars());
            $html = $view->render('LegacyCrm/view', false);
            $document = new \DOMDocument();
            $errors = libxml_use_internal_errors(true);
            try {
                $document->loadHTML('<?xml encoding="UTF-8">' . $html);
            } finally {
                libxml_clear_errors();
                libxml_use_internal_errors($errors);
            }
            $xpath = new \DOMXPath($document);
            foreach (['shoujou' => 2, 'impression' => 6, 'joukyou' => 2, 'day_service_days' => 7] as $field => $expected) {
                $checkboxes = $xpath->query('//input[@type="checkbox" and @name="' . $field . '[]"]');
                self::assertSame($expected, $checkboxes->length, $name . ': ' . $field);
                if ($field !== 'day_service_days') {
                    foreach ($checkboxes as $checkbox) {
                        $id = $checkbox->getAttribute('id');
                        self::assertSame(1, $xpath->query('//label[@for="' . $id . '"]')->length, $name . ': missing label for ' . $id);
                    }
                }
            }
            if ($name === 'Contacts') {
                self::assertSame(1, $xpath->query('//input[@name="shoujou[]" and @value="左目" and @checked]')->length);
            }
        }
    }

    public function testSavedContactFieldsAutofillOnViewAndEdit(): void
    {
        foreach (['mt_products', 'mt_statuses', 'mt_categories', 'mt_subcategories', 'mt_corresponds', 'mt_agents'] as $table) {
            $this->connection->execute('CREATE TABLE ' . $table . ' (id INTEGER PRIMARY KEY, item_name TEXT, active_flg INTEGER, unit_price TEXT)');
            foreach (range(1, 5) as $id) $this->connection->insert($table, ['id' => $id, 'item_name' => 'Option ' . $id, 'active_flg' => 1, 'unit_price' => '100']);
        }
        $customerId = $this->addCustomer();
        $data = ['kekka' => '1', 'list_name' => 'Saved list', 'sakiokuri_datetime' => '2026-11-20 14:30:00', 'reason' => '2',
            'shoujou' => ['Option 2', 'Option 4'], 'impression' => ['明るい', 'よく話す'], 'joukyou' => ['Option 1', 'Option 3'],
            'day_service_days' => ['月', '金'], 'day_service_times' => '2', 'kazoku_kankyou' => '2', 'fukuyaku' => 'Saved medicine',
            'supply' => 'Saved supplement', 'memo' => "Saved memo\nSecond line", 'receipt_number' => 'R-100',
            'contact_company' => 'Inquiry company', 'contact_tantou' => 'Inquiry person', 'contact_dep' => 'Inquiry department', 'contact_tel' => '0312340000',
            'site_company' => 'Site company', 'site_tantou' => 'Site person', 'site_dep' => 'Site department', 'site_tel' => '0312340001',
            'location' => 'Saved location', 'product_cd' => '3', 'ticket_no' => 'T-10', 'destination' => 'Saved destination',
            'date_want' => '2026-11-21', 'time_from' => '10:00', 'time_to' => '11:30', 'category_cd' => '2', 'status_cd' => '3',
            'correspond_cd' => '4', 'agent_cd' => '5', 'hv_div' => '1', 'contact_detail' => 'Saved inquiry', 'remark' => 'Saved remark', 'answer_detail' => 'Saved answer'];
        foreach (range(1, 5) as $i) $data += ['product_name' . $i => (string)$i, 'quantity' . $i => (string)$i, 'unit_price' . $i => '100', 'price' . $i => (string)($i * 100)];
        $contactId = $this->addContact($customerId, $data);
        $saved = $this->contacts->get($contactId);
        foreach (['view', 'edit'] as $action) {
            $controller = $this->controller('Contacts', $action);
            $controller->getRequest()->getSession()->write('lead_name', 'Different current session');
            $controller->{$action}((string)$contactId);
            $view = new AppView($controller->getRequest());
            $view->set($controller->viewBuilder()->getVars());
            $html = $view->render($controller->viewBuilder()->getTemplate(), false);
            $document = new \DOMDocument();
            @$document->loadHTML('<?xml encoding="UTF-8">' . $html);
            $xpath = new \DOMXPath($document);
            $fields = $action === 'view'
                ? ['kekka', 'list_name', 'reason', 'day_service_times', 'kazoku_kankyou', 'fukuyaku', 'supply', 'memo', 'total_price', ...array_keys(array_filter($data, static fn($key) => preg_match('/^(product_name|quantity|unit_price|price)[1-5]$/', $key), ARRAY_FILTER_USE_KEY))]
                : ['receipt_number', 'contact_company', 'contact_tantou', 'contact_dep', 'contact_tel', 'site_company', 'site_tantou', 'site_dep', 'site_tel', 'location', 'product_cd', 'ticket_no', 'destination', 'date_want', 'time_from', 'time_to', 'category_cd', 'status_cd', 'correspond_cd', 'agent_cd', 'hv_div', 'contact_detail', 'remark', 'answer_detail'];
            foreach ($fields as $field) {
                $nodes = $xpath->query('//*[@name="' . $field . '"]');
                self::assertSame(1, $nodes->length, $action . ': missing ' . $field);
                $node = $nodes->item(0);
                $actual = $node->tagName === 'select'
                    ? $xpath->query('option[@selected]', $node)->item(0)?->getAttribute('value')
                    : ($node->tagName === 'textarea' ? $node->textContent : $node->getAttribute('value'));
                self::assertSame((string)$saved->get($field), $actual, $action . ': saved value missing for ' . $field);
            }
            if ($action === 'view') {
                self::assertSame('2026-11-20 14:30', $xpath->query('//input[@name="sakiokuri_datetime"]')->item(0)->getAttribute('value'));
                foreach (['shoujou', 'impression', 'joukyou', 'day_service_days'] as $field) {
                    $actual = [];
                    foreach ($xpath->query('//input[@name="' . $field . '[]" and @checked]') as $checkbox) $actual[] = $checkbox->getAttribute('value');
                    self::assertSame($data[$field], $actual, $field . ': selections not restored');
                }
            }
        }
    }

    public function testOriginalContactCheckboxesAndUpdateNavigation(): void
    {
        $this->connection->execute('CREATE TABLE mt_categories (id INTEGER PRIMARY KEY, item_name TEXT, active_flg INTEGER)');
        $this->connection->insert('mt_categories', ['id' => 1, 'item_name' => '右目', 'active_flg' => 1]);
        $customerId = $this->addCustomer();
        $contactId = $this->addContact($customerId, ['shoujou' => ['右目'], 'day_service_days' => ['月', '金'], 'list_name' => 'List']);
        $controller = $this->controller('Contacts', 'view');
        $controller->view((string)$contactId);
        $view = new AppView($controller->getRequest());
        $view->set($controller->viewBuilder()->getVars());
        $html = $view->render($controller->viewBuilder()->getTemplate(), false);
        self::assertStringContainsString('name="shoujou[]"', $html);
        self::assertStringContainsString('name="_legacy_contact_view"', $html);
        $dom = new \DOMDocument();
        @$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
        $xpath = new \DOMXPath($dom);
        self::assertSame(1, $xpath->query('//input[@name="shoujou[]"][@checked]')->length);
        self::assertSame(2, $xpath->query('//input[@name="day_service_days[]"][@checked]')->length);
        $response = $this->controller('Contacts', 'edit', 'POST', ['add' => '新規登録', 'memo' => 'Do not save'])->edit((string)$contactId);
        self::assertStringContainsString('/customers/view/' . $customerId, $response->getHeaderLine('Location'));
        self::assertSame('初回', $this->contacts->get($contactId)->memo);
        $invalid = $this->controller('Contacts', 'edit', 'POST', ['_legacy_contact_view' => '1', 'contact_company' => '', 'memo' => 'Keep input']);
        self::assertNull($invalid->edit((string)$contactId));
        self::assertSame('/LegacyCrm/view', $invalid->viewBuilder()->getTemplate());
        self::assertSame('Keep input', $invalid->viewBuilder()->getVar('record')->memo);
    }

    public function testOperatorFieldsAndAuthorCannotBeForged(): void
    {
        $customerId = $this->addCustomer();
        $operator = $this->controller('Contacts', 'add', 'POST', ['kekka' => '1', 'memo' => 'Hidden', 'ap_name' => 'Forged'], '0');
        self::assertNotNull($operator->add((string)$customerId));
        $record = $this->contacts->find()->first();
        self::assertNull($record->memo);
        self::assertSame('Operator', $record->ap_name);
        $controller = $this->controller('Contacts', 'edit', 'GET', [], '0');
        $controller->edit((string)$record->id);
        self::assertArrayNotHasKey('shoujou', $controller->viewBuilder()->getVar('fields'));
        self::assertArrayNotHasKey('memo', $controller->viewBuilder()->getVar('fields'));
    }

    public function testPartialProductImportPreservesOtherLineTotals(): void
    {
        $customerId = $this->addCustomer();
        $contactId = $this->addContact($customerId, ['quantity1' => '2', 'unit_price1' => '150', 'quantity2' => '1', 'unit_price2' => '200']);
        $report = (new LegacyCrmImporter($this->customers, $this->contacts))->import([['id', 'customer_id', 'kekka', 'quantity2'], [$contactId, $customerId, '1', '3']], 'contacts', 7);
        self::assertSame(1, $report['updated']);
        self::assertEquals(900, $this->contacts->get($contactId)->total_price);
    }

    public function testCustomerImportUsesOriginalGenderAndDecadeConversions(): void
    {
        $importer = new LegacyCrmImporter($this->customers, $this->contacts);
        $header = array_values(LegacyCrm::importFields('customers'));
        $cases = [
            ['男', '20', 1, 2], ['女', '30', 2, 3], ['男', '40', 1, 4],
            ['女', '50', 2, 5], ['男', '60', 1, 6], ['女', '70', 2, 7],
            ['男', '80', 1, 8], ['', '', null, null], ['その他', '0', null, null],
            ['1', '7', null, null], ['2', '90', null, null],
        ];
        foreach ($cases as $i => [$gender, $decade, $expectedGender, $expectedDecade]) {
            $name = 'Import conversion ' . $i;
            $phone = '031234' . str_pad((string)$i, 4, '0', STR_PAD_LEFT);
            $row = [$name, '', $gender, '70', $decade, '', '', '', '', '', $phone];
            $result = $importer->import([$header, $row], 'customers', 7);
            self::assertSame(1, $result['inserted'], json_encode($result));
            $customer = $this->customers->find()->where(['customer_name' => $name])->firstOrFail();
            if ($expectedGender === null) self::assertNull($customer->gender);
            else self::assertEquals($expectedGender, $customer->gender);
            if ($expectedDecade === null) self::assertNull($customer->nendai);
            else self::assertEquals($expectedDecade, $customer->nendai);
            self::assertSame($phone, $customer->phone1);
        }
        $result = $importer->import([$header, ['Import conversion 0', '', '女', '70', '70', '', '', '', '', '', '0312340000']], 'customers', 7);
        self::assertSame(1, $result['updated']);
        $updated = $this->customers->find()->where(['customer_name' => 'Import conversion 0'])->firstOrFail();
        self::assertEquals(2, $updated->gender);
        self::assertEquals(7, $updated->nendai);
    }

    public function testUploadedCsvAndWorkbookTemplate(): void
    {
        $filePath = tempnam(TMP, 'legacy-import-');
        $stream = fopen($filePath, 'w');
        fputcsv($stream, array_values(LegacyCrm::importFields('customers')));
        fputcsv($stream, ['山田', 'ヤマダ', '男', '70', '70', '0123456', '', '', '', '', '0312345678']);
        fclose($stream);
        try {
            $request = (new ServerRequest(['environment' => ['REQUEST_METHOD' => 'POST'], 'session' => new Session()]))
                ->withAttribute('params', ['controller' => 'Imports', 'action' => 'import', 'plugin' => null, '_ext' => null, 'pass' => []])
                ->withParsedBody(['type' => 'customers', 'result' => new \Laminas\Diactoros\UploadedFile($filePath, filesize($filePath), UPLOAD_ERR_OK, 'customers.csv', 'text/csv')]);
            Router::setRequest($request);
            $controller = new LegacyImportsHarness($request, 'Imports');
            $controller->import();
            self::assertSame(1, $controller->viewBuilder()->getVar('report')['inserted']);
            $imported = $this->customers->find()->firstOrFail();
            self::assertEquals(1, $imported->gender);
            self::assertEquals(7, $imported->nendai);
            $response = $controller->template();
            self::assertStringStartsWith('PK', (string)$response->getBody());
            file_put_contents($filePath, (string)$response->getBody());
            $book = (new \PhpOffice\PhpSpreadsheet\Reader\Xlsx())->load($filePath);
            self::assertSame('K', $book->getActiveSheet()->getHighestColumn());
            $book->getActiveSheet()->fromArray([['Template', '', '女', '60', '60', '0123456', '', '', '', '', '0312345679']], null, 'A2');
            $templateReport = (new LegacyCrmImporter($this->customers, $this->contacts))->import($book->getActiveSheet()->toArray(null, false, true, false), 'customers', 7);
            self::assertSame(1, $templateReport['inserted']);
            $templateCustomer = $this->customers->find()->where(['customer_name' => 'Template'])->firstOrFail();
            self::assertEquals(2, $templateCustomer->gender);
            self::assertEquals(6, $templateCustomer->nendai);
            $book->disconnectWorksheets();
            $view = new AppView($request);
            $view->set($controller->viewBuilder()->getVars());
            self::assertStringContainsString('取込結果', $view->render('Imports/index', false));
        } finally {
            unlink($filePath);
        }
    }
}

trait LegacyHarness
{
    public string $auth = '9';
    public ?int $belong = null;
    public function initialize(): void
    {
        $this->Customers = TableRegistry::getTableLocator()->get('Customers');
        $this->Contacts = TableRegistry::getTableLocator()->get('Contacts');
        $this->loadComponent('Flash');
    }
    protected function getCurrentUser(): ?User
    {
        return new User(['id' => 7, 'auth' => $this->auth, 'belong' => $this->belong, 'loginid' => 'operator', 'user_name' => 'Operator']);
    }
}

class LegacyCustomerHarness extends CustomersController
{
    use LegacyHarness;
}

class LegacyContactHarness extends ContactsController
{
    use LegacyHarness;
    public bool $rejectCount = false;
    public array $dncCalls = [];
    public string $dncResponse = '';
    protected function callDncAPI(string $phone, int $leadId, string $status): string
    {
        $latest = $this->Contacts->find()->orderBy(['id' => 'DESC'])->first();
        $this->dncCalls[] = [$phone, $leadId, $status, $this->Contacts->find()->count(), $latest?->memo];
        return $this->dncResponse;
    }
    protected function legacyQuery(array $search = [])
    {
        $query = LegacyCrm::search($this->Contacts, $search);
        if ($this->rejectCount) {
            $query->counter(static function (): int { throw new \LogicException('Full count must not run for list pagination'); });
        }
        return $query;
    }
}

class LegacyImportsHarness extends \App\Controller\ImportsController
{
    use LegacyHarness;
}
