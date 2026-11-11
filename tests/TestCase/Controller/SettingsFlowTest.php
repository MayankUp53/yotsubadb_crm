<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\LegacyMasterController;
use App\Model\Entity\User;
use App\Model\Table\CustomersTable;
use App\Service\LegacyCrm;
use App\Service\LegacyPhoneExport;
use App\Service\LegacySettings;
use App\View\AppView;
use Cake\Database\Connection;
use Cake\Database\Driver\Sqlite;
use Cake\Datasource\ConnectionManager;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\MethodNotAllowedException;
use Cake\Http\ServerRequest;
use Cake\Http\Session;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use PHPUnit\Framework\TestCase;

class SettingsFlowTest extends TestCase
{
    private Connection $connection;

    protected function setUp(): void
    {
        ConnectionManager::setConfig('settings_test', ['className' => Connection::class, 'driver' => Sqlite::class, 'database' => ':memory:']);
        $this->connection = ConnectionManager::get('settings_test');
        $locator = TableRegistry::getTableLocator();
        $locator->clear();
        foreach (LegacySettings::sections() as $name => $section) {
            $this->connection->execute('CREATE TABLE ' . $section['table'] . ' (id INTEGER PRIMARY KEY AUTOINCREMENT, disp_cd INTEGER UNIQUE, item_name TEXT, active_flg INTEGER, unit_price INTEGER, created DATETIME, created_user_id INTEGER, updated DATETIME, updated_user_id INTEGER, deleted DATETIME, deleted_user_id INTEGER)');
            $class = 'App\\Model\\Table\\' . $name . 'Table';
            $locator->set($name, new $class(['connection' => $this->connection, 'alias' => $name]));
        }
        $this->connection->execute('CREATE TABLE users (id INTEGER PRIMARY KEY, user_name TEXT)');
        $this->connection->insert('users', ['id' => 7, 'user_name' => 'Operator']);
        $locator->set('Users', new Table(['connection' => $this->connection, 'table' => 'users', 'alias' => 'Users']));
        $fields = implode(',', array_map(static fn($field) => $field . ' TEXT', array_keys(LegacyCrm::customerFields())));
        $this->connection->execute('CREATE TABLE customers (id INTEGER PRIMARY KEY AUTOINCREMENT,' . $fields . ',belong INTEGER DEFAULT 0, created DATETIME, updated DATETIME, deleted DATETIME)');
        $locator->set('Customers', new CustomersTable(['connection' => $this->connection, 'alias' => 'Customers']));
        $this->connection->execute('CREATE TABLE phones (id INTEGER PRIMARY KEY, phone1 TEXT)');
        $this->connection->insert('phones', ['id' => 1, 'phone1' => 'sentinel']);
        Router::reload();
        $loadRoutes = require CONFIG . 'routes.php';
        $loadRoutes(Router::createRouteBuilder('/'));
    }

    protected function tearDown(): void
    {
        TableRegistry::getTableLocator()->clear();
        $this->connection->getDriver()->disconnect();
        ConnectionManager::drop('settings_test');
    }

    public static function sections(): array
    {
        return array_map(static fn($name) => [$name], array_keys(LegacySettings::sections()));
    }

    private function request(string $name, string $action, string $method = 'GET', array $data = []): ServerRequest
    {
        $request = (new ServerRequest(['environment' => ['REQUEST_METHOD' => $method], 'session' => new Session()]))
            ->withParsedBody($data)->withAttribute('params', ['controller' => $name, 'action' => $action, 'plugin' => null, '_ext' => null, 'pass' => []]);
        Router::setRequest($request);
        return $request;
    }

    private function controller(string $name, string $action, string $method = 'GET', array $data = [], string $auth = '9'): SettingsMasterHarness
    {
        $controller = new SettingsMasterHarness($this->request($name, $action, $method, $data), $name);
        $controller->auth = $auth;
        return $controller;
    }

    private function addRecord(string $name, array $data = []): int
    {
        $controller = $this->controller($name, 'add', 'POST', $data + ['item_name' => '名称', 'active_flg' => '1', 'unit_price' => '150']);
        self::assertNotNull($controller->add(), json_encode($controller->viewBuilder()->getVar('record')?->getErrors()));
        return (int)TableRegistry::getTableLocator()->get($name)->find()->select(['id'])->orderBy(['id' => 'DESC'])->first()->id;
    }

    /** @dataProvider sections */
    public function testEverySectionCreateEditViewDelete(string $name): void
    {
        $model = LegacySettings::sections()[$name]['model'];
        $controller = $this->controller($name, 'add', 'POST', [$model => ['item_name' => 'Inactive', 'active_flg' => '0', 'unit_price' => 150, 'deleted' => '2000-01-01', 'created_user_id' => 999]]);
        self::assertNotNull($controller->add());
        $table = TableRegistry::getTableLocator()->get($name);
        $record = $table->find()->first();
        self::assertSame(0, $record->active_flg);
        self::assertSame(7, $record->created_user_id);
        self::assertNull($record->deleted);
        self::assertSame(1, $record->disp_cd);
        $id = (string)$record->id;
        $this->controller($name, 'edit', 'PATCH', ['item_name' => 'Changed', 'active_flg' => '1', 'unit_price' => 999, 'disp_cd' => 99])->edit($id);
        $record = $table->get($id);
        self::assertSame('Changed', $record->item_name);
        self::assertSame(1, $record->active_flg);
        self::assertSame(1, $record->disp_cd);
        if ($name === 'MtProducts') {
            self::assertSame(150, $record->unit_price);
        }
        $view = $this->controller($name, 'view');
        $view->view($id);
        self::assertSame('Changed', $view->viewBuilder()->getVar('record')->item_name);
        self::assertSame('/' . LegacySettings::sections()[$name]['slug'], parse_url($this->controller($name, 'edit', 'POST', ['back' => '1'])->edit($id)->getHeaderLine('Location'), PHP_URL_PATH));
        $this->controller($name, 'edit', 'POST', ['delete' => '1'])->edit($id);
        self::assertNotNull($table->get($id)->deleted);
        self::assertSame(7, $table->get($id)->deleted_user_id);
        $index = $this->controller($name, 'index');
        $index->index();
        self::assertCount(0, $index->viewBuilder()->getVar('records'));
    }

    /** @dataProvider sections */
    public function testEverySectionValidationAndSort(string $name): void
    {
        $invalid = $this->controller($name, 'add', 'POST', ['item_name' => '', 'active_flg' => '']);
        self::assertNull($invalid->add());
        self::assertNotEmpty($invalid->viewBuilder()->getVar('record')->getError('item_name'));
        self::assertNotEmpty($invalid->viewBuilder()->getVar('record')->getError('active_flg'));
        $first = $this->addRecord($name);
        $second = $this->addRecord($name, ['item_name' => 'Second']);
        $this->controller($name, 'sortupdate', 'POST', ['ids' => "$second-$first"])->sortupdate();
        $table = TableRegistry::getTableLocator()->get($name);
        self::assertSame(1, $table->get($second)->disp_cd);
        self::assertSame(2, $table->get($first)->disp_cd);
        self::assertSame(7, $table->get($second)->updated_user_id);
        try {
            $this->controller($name, 'sortupdate', 'POST', ['ids' => "$first-$first"])->sortupdate();
            self::fail('Duplicate sort IDs should fail');
        } catch (BadRequestException $e) {
            self::assertSame(1, $table->get($second)->disp_cd);
        }
    }

    /** @dataProvider sections */
    public function testEverySectionRejectsGetDeleteAndReadOnlyWrites(string $name): void
    {
        $id = (string)$this->addRecord($name);
        foreach ([['delete', 'GET', '9', MethodNotAllowedException::class], ['sortupdate', 'GET', '9', MethodNotAllowedException::class], ['edit', 'POST', '0', ForbiddenException::class], ['delete', 'POST', '0', ForbiddenException::class]] as [$action, $method, $auth, $class]) {
            try {
                $this->controller($name, $action, $method, [], $auth)->{$action}($id);
                self::fail('Expected access rejection');
            } catch (\Throwable $e) {
                self::assertInstanceOf($class, $e);
            }
        }
        self::assertNull(TableRegistry::getTableLocator()->get($name)->get($id)->deleted);
    }

    /** @dataProvider sections */
    public function testEverySectionTemplatesAndRoutes(string $name): void
    {
        $id = (string)$this->addRecord($name);
        foreach (['index', 'add', 'view', 'edit'] as $action) {
            $controller = $this->controller($name, $action);
            $controller->{$action}(...(in_array($action, ['view', 'edit'], true) ? [$id] : []));
            $view = new AppView($controller->getRequest());
            $view->set($controller->viewBuilder()->getVars());
            $html = $view->render($controller->viewBuilder()->getTemplate(), false);
            if ($action === 'view') {
                self::assertStringContainsString('Item Name', $html);
                self::assertStringContainsString('Actions', $html);
            } else {
                self::assertStringContainsString(LegacySettings::sections()[$name]['title'], $html);
            }
            if ($action === 'index') {
                self::assertStringContainsString('master-sort-form', $html);
                foreach (['詳細', '↑', '↓', '設定へ戻る', '単価：'] as $extra) self::assertStringNotContainsString($extra, $html);
            }
            if ($action === 'edit' && $name === 'MtProducts') self::assertStringNotContainsString('name="unit_price"', $html);
        }
        $section = LegacySettings::sections()[$name];
        foreach ([$section['slug'], $section['table']] as $path) {
            $base = Router::parseRequest(new ServerRequest(['url' => '/' . $path]));
            self::assertSame($name, $base['controller']);
            self::assertSame('index', $base['action']);
            foreach (['index', 'add', 'view', 'edit', 'delete', 'sortupdate'] as $action) {
                $parsed = Router::parseRequest(new ServerRequest(['url' => '/' . $path . '/' . $action . ($action === 'index' || $action === 'add' ? '' : '/1')]));
                self::assertSame($name, $parsed['controller']);
                self::assertSame($action, $parsed['action']);
            }
        }
    }

    public function testSettingsDashboardAndPhoneLookup(): void
    {
        $this->connection->insert('customers', ['id' => 1, 'phone1' => '09012345678', 'tel' => '090-1234-5678', 'customer_name' => '山田', 'gender' => '1', 'nendai' => '7', 'zip' => '0123456']);
        $this->connection->insert('customers', ['id' => 2, 'phone1' => '09012345678', 'customer_name' => 'Deleted', 'deleted' => '2020-01-01']);
        $export = new LegacyPhoneExport(TableRegistry::getTableLocator()->get('Customers'));
        $rows = iterator_to_array($export->exportRows([['電話番号'], ['9012345678'], ['090-1234-5678'], ['0311112222']]));
        self::assertCount(3, $rows);
        self::assertSame("'09012345678", $rows[0][0]);
        self::assertSame('山田', $rows[0][1]);
        self::assertSame('男', $rows[0][3]);
        self::assertSame('70', $rows[0][5]);
        self::assertSame('0123456', $rows[0][6]);
        self::assertSame('', $rows[2][1]);
        self::assertSame('sentinel', $this->connection->execute('SELECT phone1 FROM phones')->fetch('assoc')['phone1']);
        $request = $this->request('Settings', 'index');
        $controller = new SettingsDashboardHarness($request, 'Settings');
        $controller->index();
        $view = new AppView($request);
        $view->set($controller->viewBuilder()->getVars());
        $html = $view->render('Settings/index', false);
        self::assertStringContainsString('get-phone-data', $html);
        self::assertStringNotContainsString('phone-template', $html);
        self::assertStringNotContainsString('雛形', $html);
        self::assertStringContainsString('顧客データ　xlsx', $html);
        self::assertStringContainsString('電話番号リスト　xlsx', $html);
        foreach (LegacySettings::sections() as $section) self::assertStringContainsString($section['table'], $html);
    }

    public function testPhoneExportUploadAndTemplate(): void
    {
        $this->connection->insert('customers', ['phone1' => '09012345678', 'customer_name' => '山田']);
        $path = tempnam(TMP, 'settings-upload-');
        file_put_contents($path, "電話番号\n09012345678\n");
        try {
            $request = $this->request('Imports', 'getphonedata', 'POST', ['Import' => ['result' => new \Laminas\Diactoros\UploadedFile($path, filesize($path), UPLOAD_ERR_OK, 'phones.csv')]]);
            $controller = new SettingsImportsHarness($request, 'Imports');
            $response = $controller->getphonedata();
            self::assertStringContainsString('Shift_JIS', $response->getHeaderLine('Content-Type'));
            self::assertStringContainsString('山田', mb_convert_encoding((string)$response->getBody(), 'UTF-8', 'SJIS-win'));
            self::assertSame('sentinel', $this->connection->execute('SELECT phone1 FROM phones')->fetch('assoc')['phone1']);
            $template = $controller->phoneTemplate();
            file_put_contents($path, (string)$template->getBody());
            $book = (new \PhpOffice\PhpSpreadsheet\Reader\Xlsx())->load($path);
            self::assertSame('A', $book->getActiveSheet()->getHighestColumn());
            self::assertStringContainsString('電話番号', $book->getActiveSheet()->getCell('A1')->getValue());
            $book->getActiveSheet()->setCellValueExplicit('A2', '09012345678', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($book))->save($path);
            $book->disconnectWorksheets();
            $xlsxRequest = $this->request('Imports', 'getphonedata', 'POST', ['result' => new \Laminas\Diactoros\UploadedFile($path, filesize($path), UPLOAD_ERR_OK, 'phones.xlsx')]);
            $xlsxResponse = (new SettingsImportsHarness($xlsxRequest, 'Imports'))->getphonedata();
            self::assertStringContainsString("'09012345678", (string)$xlsxResponse->getBody());
            $missing = new SettingsImportsHarness($this->request('Imports', 'getphonedata', 'POST'), 'Imports');
            self::assertStringContainsString('/settings', $missing->getphonedata()->getHeaderLine('Location'));
        } finally {
            unlink($path);
        }
    }

    /** @dataProvider sections */
    public function testReorderAfterDeletionKeepsDeletedRanksAndReferences(string $name): void
    {
        $deleted = $this->addRecord($name);
        $active = $this->addRecord($name, ['item_name' => 'Active']);
        $this->controller($name, 'delete', 'POST')->delete((string)$deleted);
        $this->controller($name, 'sortupdate', 'POST', ['ids' => (string)$active])->sortupdate();
        $table = TableRegistry::getTableLocator()->get($name);
        self::assertNotNull($table->get($deleted)->deleted);
        self::assertSame('名称', $table->get($deleted)->item_name);
        self::assertGreaterThan(0, $table->get($active)->disp_cd);
    }

    public function testInvalidPhoneFilesAndReadonlyImportsAreRejected(): void
    {
        $request = $this->request('Imports', 'import', 'POST');
        $controller = new SettingsImportsHarness($request, 'Imports');
        $controller->auth = '0';
        try {
            $controller->import();
            self::fail('Readonly import should fail');
        } catch (ForbiddenException $e) {
            self::assertSame(0, TableRegistry::getTableLocator()->get('Customers')->find()->count());
        }
        $export = new LegacyPhoneExport(TableRegistry::getTableLocator()->get('Customers'));
        foreach ([[['電話番号']], [['電話番号'], ['=A1']], [['電話番号'], ['invalid']]] as $rows) {
            try {
                iterator_to_array($export->exportRows($rows));
                self::fail('Invalid phone file should fail');
            } catch (\InvalidArgumentException $e) {
                self::assertNotSame('', $e->getMessage());
            }
        }
        self::assertSame('sentinel', $this->connection->execute('SELECT phone1 FROM phones')->fetch('assoc')['phone1']);
    }
}

trait SettingsIdentityHarness
{
    public string $auth = '9';
    protected function getCurrentUser(): ?User
    {
        return new User(['id' => 7, 'auth' => $this->auth]);
    }
}

class SettingsMasterHarness extends LegacyMasterController
{
    use SettingsIdentityHarness;
    public function initialize(): void
    {
        $this->Master = TableRegistry::getTableLocator()->get($this->getName());
        $this->loadComponent('Flash');
    }
}

class SettingsDashboardHarness extends \App\Controller\SettingsController
{
    use SettingsIdentityHarness;
    public function initialize(): void { $this->loadComponent('Flash'); }
}

class SettingsImportsHarness extends \App\Controller\ImportsController
{
    use SettingsIdentityHarness;
    public function initialize(): void { $this->loadComponent('Flash'); }
}
