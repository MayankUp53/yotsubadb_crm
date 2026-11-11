<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Application;
use App\Controller\UsersController;
use App\Model\Table\UsersTable;
use Cake\Database\Connection;
use Cake\Database\Driver\Sqlite;
use Cake\Datasource\ConnectionManager;
use Cake\Http\ServerRequest;
use Cake\Http\Session;
use Cake\ORM\TableRegistry;
use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use PHPUnit\Framework\TestCase;

/** Exercises the application's actual authentication service against SQLite only. */
class UsersFlowTest extends TestCase
{
    private Connection $connection;

    protected function setUp(): void
    {
        ConnectionManager::setConfig('auth_flow_test', ['className' => Connection::class, 'driver' => Sqlite::class, 'database' => ':memory:']);
        $this->connection = ConnectionManager::get('auth_flow_test');
        $this->connection->execute('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, loginid TEXT, password TEXT, auth INTEGER, user_name TEXT, disp_cd TEXT, created DATETIME, created_user_id INTEGER, updated DATETIME, updated_user_id INTEGER, deleted DATETIME)');
        TableRegistry::getTableLocator()->clear();
        TableRegistry::getTableLocator()->set('Users', new UsersTable(['connection' => $this->connection]));
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
        ConnectionManager::drop('auth_flow_test');
    }

    private function controller(string $action, string $method = 'GET', array $data = []): AuthUsersHarness
    {
        $request = (new ServerRequest(['url' => '/users/' . $action, 'environment' => ['REQUEST_METHOD' => $method], 'session' => new Session()]))
            ->withParsedBody($data)
            ->withAttribute('params', ['controller' => 'Users', 'action' => $action, 'plugin' => null, '_ext' => null, 'pass' => []]);
        $service = (new Application(CONFIG))->getAuthenticationService($request);
        $service->authenticate($request);
        $request = $request->withAttribute('authentication', $service)->withAttribute('identity', $service->getIdentity());
        Router::setRequest($request);
        return new AuthUsersHarness($request, 'Users');
    }

    public function testCreatedAndUpdatedPasswordsRemainPlainAndAuthenticate(): void
    {
        $users = TableRegistry::getTableLocator()->get('Users');
        $user = $users->newEntity(['loginid' => 'plain-user', 'password' => '0123', 'auth' => 9], ['validate' => false]);
        $users->saveOrFail($user);
        self::assertSame('0123', $users->get($user->id)->password);
        self::assertStringEndsWith('/customers', $this->controller('login', 'POST', ['loginid' => 'plain-user', 'password' => '0123'])->login()->getHeaderLine('Location'));
        $users->patchEntity($user, ['password' => '0042'], ['validate' => false]);
        $users->saveOrFail($user);
        self::assertSame('0042', $users->get($user->id)->password);
        self::assertNull($this->controller('login', 'POST', ['loginid' => 'plain-user', 'password' => '0123'])->login());
        self::assertStringEndsWith('/customers', $this->controller('login', 'POST', ['loginid' => 'plain-user', 'password' => '0042'])->login()->getHeaderLine('Location'));
        $hasher = new \App\Auth\LegacyPasswordHasher();
        self::assertSame('0042', $hasher->hash('0042'));
        self::assertFalse($hasher->needsRehash('0042'));
    }

    public function testBcryptStoredValueIsNotVerifiedAsAHash(): void
    {
        $this->connection->insert('users', ['loginid' => 'hashed-user', 'password' => password_hash('test-password', PASSWORD_BCRYPT), 'auth' => 9]);
        $controller = $this->controller('login', 'POST', ['loginid' => 'hashed-user', 'password' => 'test-password']);
        self::assertNull($controller->login());
        self::assertNull($controller->Authentication->getIdentity());
        $this->connection->insert('users', ['loginid' => '1965', 'password' => password_hash('1413', PASSWORD_BCRYPT), 'auth' => 9]);
        $home = $this->controller('home');
        self::assertStringEndsWith('/users/login', $home->home('1965')->getHeaderLine('Location'));
        self::assertNull($home->Authentication->getIdentity());
    }

    public function testUserMaintenanceRendersWithoutCallbackTables(): void
    {
        $this->connection->insert('users', ['loginid' => 'operator', 'user_name' => 'Operator', 'disp_cd' => '1', 'auth' => 9]);
        foreach (['edit', 'add'] as $action) {
            $controller = $this->controller($action);
            self::assertFalse($controller->components()->has('CallbackNotification'));
            if ($action === 'edit') $controller->edit('1');
            else $controller->add();
            $view = new \App\View\AppView($controller->getRequest());
            $view->set($controller->viewBuilder()->getVars());
            $html = $view->render('Users/' . $action, false);
            self::assertStringContainsString('name="loginid"', $html);
            self::assertStringContainsString('name="user_name"', $html);
        }
        self::assertSame(['users'], $this->connection->getSchemaCollection()->listTables());
    }

    public function testLegacyUserAddFormAndSuccessfulCreation(): void
    {
        $get = $this->controller('add');
        $get->add();
        $view = new \App\View\AppView($get->getRequest());
        $view->set($get->viewBuilder()->getVars());
        $html = $view->render('Users/add', false);
        self::assertStringContainsString('ユーザ新規追加', $html);
        self::assertStringContainsString('登　録', $html);
        self::assertStringNotContainsString('キャンセル', $html);
        self::assertStringNotContainsString('登録日時', $html);
        self::assertStringNotContainsString('*ID', $html);
        self::assertStringNotContainsString('required="required"', $html);
        self::assertSame(1, substr_count($html, '<button'));
        $this->connection->insert('users', ['id' => 1, 'user_name' => 'Operator', 'auth' => 9]);
        $users = TableRegistry::getTableLocator()->get('Users');
        $add = $this->controller('add', 'POST', ['id' => 1, 'user_name' => 'New user', 'auth' => '0', 'disp_cd' => '', 'loginid' => 'new-login',
            'password' => 'plain-test-password', 'created_user_id' => 999, 'updated_user_id' => 999, 'deleted' => '2020-01-01 00:00:00']);
        $add->Authentication->setIdentity($users->get(1));
        self::assertStringEndsWith('/users', $add->add()->getHeaderLine('Location'));
        $new = $users->find()->where(['loginid' => 'new-login'])->firstOrFail();
        self::assertNotSame(1, $new->id);
        self::assertSame(0, $new->auth);
        self::assertSame('', $new->disp_cd);
        self::assertSame('plain-test-password', $new->password);
        self::assertSame(1, $new->created_user_id);
        self::assertSame(1, $new->updated_user_id);
        self::assertNotNull($new->created);
        self::assertEquals($new->created, $new->updated);
        self::assertNull($new->deleted);
        self::assertSame('Operator', $users->get(1)->user_name);
        $blank = $this->controller('add', 'POST', ['User' => ['user_name' => 'Blank credentials', 'auth' => '9', 'loginid' => '', 'password' => '']]);
        self::assertStringEndsWith('/users', $blank->add()->getHeaderLine('Location'));
        $created = $users->find()->where(['user_name' => 'Blank credentials'])->firstOrFail();
        self::assertSame('', $created->loginid);
        self::assertSame('', $created->password);
    }

    public function testLegacyUserAddRequiresNameAndAuthorityAndRetainsFormErrors(): void
    {
        foreach ([['auth' => '9'], ['user_name' => ''], ['user_name' => '   ', 'auth' => '9'], ['user_name' => 'Valid name', 'auth' => '']] as $data) {
            $controller = $this->controller('add', 'POST', $data + ['loginid' => 'attempted-login', 'password' => 'attempted-test-password']);
            self::assertNull($controller->add());
            $entity = $controller->viewBuilder()->getVar('user');
            self::assertNotEmpty($entity->getErrors());
            self::assertSame(0, TableRegistry::getTableLocator()->get('Users')->find()->count());
            self::assertSame('flash/error', $controller->getRequest()->getSession()->read('Flash.flash.0.element'));
            $view = new \App\View\AppView($controller->getRequest());
            $view->set($controller->viewBuilder()->getVars());
            $html = $view->render('Users/add', false);
            self::assertStringContainsString('権限は必須です。選択してください。', $html);
            self::assertStringNotContainsString('value="attempted-login"', $html);
            self::assertStringNotContainsString('attempted-test-password', $html);
        }
    }

    public function testLegacyUserEditFormPrefillsLoginAndOnlyShowsUpdate(): void
    {
        $this->connection->insert('users', ['loginid' => 'existing-login', 'password' => 'stored-test-password', 'user_name' => 'Existing name', 'disp_cd' => '5', 'auth' => 9]);
        $controller = $this->controller('edit');
        $controller->edit('1');
        $view = new \App\View\AppView($controller->getRequest());
        $view->set($controller->viewBuilder()->getVars());
        $html = $view->render('Users/edit', false);
        self::assertStringContainsString('value="existing-login"', $html);
        self::assertStringContainsString('bgcolor="#ff8c00"', $html);
        self::assertStringContainsString('更　新', $html);
        self::assertStringNotContainsString('stored-test-password', $html);
        self::assertStringNotContainsString('削　除', $html);
        self::assertStringNotContainsString('キャンセル', $html);
        self::assertStringNotContainsString('登録日時', $html);
        self::assertSame(1, substr_count($html, '<button'));
    }

    public function testLegacyUserEditSavesDeletedUserAndPreservesTimestamps(): void
    {
        $this->connection->insert('users', ['loginid' => 'old-login', 'password' => 'old-test-password', 'user_name' => 'Old name', 'auth' => 9,
            'created' => '2020-01-01 00:00:00', 'created_user_id' => 3, 'updated' => '2021-01-01 00:00:00', 'deleted' => '2022-01-01 00:00:00']);
        $this->connection->insert('users', ['id' => 2, 'loginid' => 'operator', 'user_name' => 'Operator', 'auth' => 9]);
        $controller = $this->controller('edit', 'POST', ['id' => 2, 'disp_cd' => '', 'user_name' => 'Updated name', 'auth' => '0',
            'loginid' => 'new-login', 'password' => 'new-test-password', 'created_user_id' => 999, 'updated_user_id' => 999, 'updated' => '2030-01-01 00:00:00']);
        $users = TableRegistry::getTableLocator()->get('Users');
        $controller->Authentication->setIdentity($users->get(2));
        self::assertStringEndsWith('/users', $controller->edit('1')->getHeaderLine('Location'));
        $saved = $users->get(1);
        self::assertSame('Updated name', $saved->user_name);
        self::assertSame('new-login', $saved->loginid);
        self::assertSame('new-test-password', $saved->password);
        self::assertSame(0, $saved->auth);
        self::assertSame('', $saved->disp_cd);
        self::assertSame(2, $saved->updated_user_id);
        self::assertSame(3, $saved->created_user_id);
        self::assertSame('2021-01-01 00:00:00', $saved->updated->format('Y-m-d H:i:s'));
        self::assertSame('2022-01-01 00:00:00', $saved->deleted->format('Y-m-d H:i:s'));
        self::assertSame('Operator', $users->get(2)->user_name);
        $blank = $this->controller('edit', 'PUT', ['User' => ['user_name' => 'Updated name', 'auth' => '0', 'loginid' => '', 'password' => '']]);
        self::assertStringEndsWith('/users', $blank->edit('1')->getHeaderLine('Location'));
        self::assertSame('', $users->get(1)->loginid);
        self::assertSame('', $users->get(1)->password);
    }

    public function testLegacyUserEditFailureReloadsStoredValuesWithErrors(): void
    {
        $this->connection->insert('users', ['loginid' => 'existing-login', 'user_name' => 'Existing name', 'auth' => 9]);
        foreach ([['user_name' => '', 'auth' => '9'], ['user_name' => '  ', 'auth' => '9'], ['user_name' => 'New name', 'auth' => ''], ['user_name' => 'New name']] as $data) {
            $controller = $this->controller('edit', 'POST', $data + ['loginid' => 'changed-login']);
            self::assertNull($controller->edit('1'));
            $user = $controller->viewBuilder()->getVar('user');
            self::assertSame('existing-login', $user->loginid);
            self::assertSame('Existing name', $user->user_name);
            self::assertNotEmpty($user->getErrors());
            self::assertSame('flash/error', $controller->getRequest()->getSession()->read('Flash.flash.0.element'));
            $view = new \App\View\AppView($controller->getRequest());
            $view->set($controller->viewBuilder()->getVars());
            self::assertStringContainsString('value="existing-login"', $view->render('Users/edit', false));
        }
    }

    public function testNormalLoginUsesCredentialsAndAlwaysOpensCustomers(): void
    {
        foreach ([0, 9] as $role) {
            $this->connection->insert('users', ['loginid' => 'operator' . $role, 'password' => 'test-password', 'auth' => $role]);
            $controller = $this->controller('login', 'POST', ['loginid' => 'operator' . $role, 'password' => 'test-password']);
            self::assertStringEndsWith('/customers', $controller->login()->getHeaderLine('Location'));
        }
        $invalid = $this->controller('login', 'POST', ['loginid' => 'operator9', 'password' => 'incorrect']);
        self::assertNull($invalid->login());
        self::assertNotEmpty($invalid->getRequest()->getSession()->read('Flash.flash'));
        self::assertNull($this->controller('login')->login());
    }

    public function testHomePasswordMappingAndAdminHandoffPersistIdentity(): void
    {
        $cases = ['1965' => '1413', '1952' => '2558', 'other' => '123456'];
        foreach (range(5195, 5204) as $id) $cases[(string)$id] = '0123';
        foreach ($cases as $id => $password) {
            $this->connection->insert('users', ['loginid' => (string)$id, 'password' => $password, 'auth' => 9]);
            $controller = $this->controller('home');
            $response = $controller->home((string)$id, '0312345678', 'lead-A');
            self::assertStringEndsWith('/customers/ameyo/0312345678/lead-A', $response->getHeaderLine('Location'));
            self::assertSame((string)$id, $controller->Authentication->getIdentity()->get('loginid'));
            self::assertNotEmpty($controller->getRequest()->getSession()->read('Auth'));
        }
    }

    public function testHomeNonAdminRolesOpenBlank(): void
    {
        foreach ([0, 1, 99] as $role) {
            $id = 'role-' . $role;
            $this->connection->insert('users', ['loginid' => $id, 'password' => '123456', 'auth' => $role]);
            self::assertStringEndsWith('/users/blank', $this->controller('home')->home($id)->getHeaderLine('Location'));
        }
        $controller = $this->controller('blank');
        $controller->blank();
        self::assertSame('blank', $controller->viewBuilder()->getLayout());
        self::assertStringNotContainsString('<nav', (string)$controller->render()->getBody());
    }

    public function testHomeRejectsIncorrectDeletedAndMissingAccounts(): void
    {
        $this->connection->insert('users', ['loginid' => 'wrong', 'password' => 'incorrect', 'auth' => 9]);
        $this->connection->insert('users', ['loginid' => 'deleted', 'password' => '123456', 'auth' => 9, 'deleted' => '2026-01-01 00:00:00']);
        foreach (['wrong', 'deleted', 'missing', null] as $id) {
            $controller = $this->controller('home');
            self::assertStringEndsWith('/users/login', $controller->home($id)->getHeaderLine('Location'));
            self::assertNull($controller->Authentication->getIdentity());
        }
    }

    public function testSsoVerifiesLegacyFixedPasswordAndHandsOffForAllRoles(): void
    {
        foreach ([0, 9, 99] as $role) {
            $id = 'sso-' . $role;
            $this->connection->insert('users', ['loginid' => $id, 'password' => '123456', 'auth' => $role]);
            $controller = $this->controller('sso');
            self::assertStringEndsWith('/customers/ameyo/0312345678/lead-A', $controller->sso($id, '0312345678', 'lead-A')->getHeaderLine('Location'));
            self::assertSame($id, $controller->Authentication->getIdentity()->get('loginid'));
            self::assertNotEmpty($controller->getRequest()->getSession()->read('Auth'));
            self::assertSame('lead-A', $controller->getRequest()->getSession()->read('lead_name'));
            self::assertNull($controller->getRequest()->getSession()->read('campaign'));
            self::assertNull($controller->getRequest()->getSession()->read('sessionId'));
        }
        $controller = $this->controller('sso');
        $controller->getRequest()->getSession()->write('lead_name', 'stale');
        self::assertNotNull($controller->sso('sso-0'));
        self::assertSame('', $controller->getRequest()->getSession()->read('lead_name'));
    }

    public function testSsoRejectsIncorrectDeletedAndMissingAccounts(): void
    {
        // Unlike home(), the original sso() does not use the per-user mapped password.
        $this->connection->insert('users', ['loginid' => '1965', 'password' => '1413', 'auth' => 9]);
        $this->connection->insert('users', ['loginid' => 'deleted-sso', 'password' => '123456', 'auth' => 9, 'deleted' => '2026-01-01 00:00:00']);
        foreach (['1965', 'deleted-sso', 'missing', null] as $id) {
            $controller = $this->controller('sso');
            self::assertStringEndsWith('/users/login', $controller->sso($id, '0312345678', 'lead-A')->getHeaderLine('Location'));
            self::assertNull($controller->Authentication->getIdentity());
            self::assertNull($controller->getRequest()->getSession()->read('Auth'));
            self::assertNull($controller->getRequest()->getSession()->read('lead_name'));
        }
    }

    public function testLegacyUrlsKeepPositionalArguments(): void
    {
        $params = Router::getRouteCollection()->parseRequest(new ServerRequest(['url' => '/users/home/operator/0312345678/lead-A']));
        self::assertSame('home', $params['action']);
        self::assertSame(['operator', '0312345678', 'lead-A'], $params['pass']);
        $params = Router::parseRequest(new ServerRequest(['url' => '/users/sso/operator/0312345678/lead-A']));
        self::assertSame('Users', $params['controller']);
        self::assertSame('sso', $params['action']);
        self::assertSame(['operator', '0312345678', 'lead-A'], $params['pass']);
    }
}

class AuthUsersHarness extends UsersController
{
    public function initialize(): void
    {
        parent::initialize();
    }
}
