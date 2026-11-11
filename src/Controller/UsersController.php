<?php

declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;
use Cake\ORM\Exception\PersistenceFailedException;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
         $users = $this->Users->find()
            ->where(['Users.deleted IS' => null])
            ->orderBy(['Users.updated' => 'DESC', 'Users.created' => 'DESC']);

        // $this->paginate = [
        //     'limit' => 10
        // ];

        // $users = $this->paginate($query);

    $this->set(compact('users', ));
    $this->setListValue();
}



    /**
     * View method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        $user = $this->Users->find()
            ->where(['Users.id' => $id, 'Users.deleted IS' => null])
            ->firstOrFail();

        $this->set(compact('user'));
        $this->setListValue();
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $user = $this->Users->newEmptyEntity();
        if ($this->request->is('post')) {
            $raw = $this->request->getData('User') ?? $this->request->getData();
            $data = array_intersect_key($raw, array_flip(['disp_cd', 'user_name', 'auth', 'loginid', 'password']));
            $now = date('Y-m-d H:i:s');
            $currentUser = $this->getCurrentUser();

            $data['created'] = $now;
            $data['created_user_id'] = $currentUser ? $currentUser->id : 0;
            $data['updated'] = $now;
            $data['updated_user_id'] = $currentUser ? $currentUser->id : 0;

            $user = $this->Users->patchEntity($user, $data, ['validate' => 'legacy']);
            if ($this->Users->save($user)) {
                $this->Flash->success(__('ユーザ情報の登録が完了しました。'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('ユーザ情報の登録に失敗しました。もう一度操作してください。'));
        }

        $this->set(compact('user'));
        $this->setListValue();
    }

    /**
     * Edit method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        $user = $this->Users->find()
            ->where(['Users.id' => $id])
            ->firstOrFail();

        if ($this->request->is(['post', 'put'])) {
            $raw = $this->request->getData('User') ?? $this->request->getData();
            $data = array_intersect_key($raw, array_flip(['disp_cd', 'user_name', 'auth', 'loginid', 'password']));
            $currentUser = $this->getCurrentUser();
            $data['updated_user_id'] = $currentUser ? $currentUser->id : 0;

            $user = $this->Users->patchEntity($user, $data, ['validate' => 'legacy']);
            if ($this->Users->save($user)) {
                $this->Flash->success(__('ユーザ情報の更新が完了しました。'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('ユーザ情報の更新に失敗しました。もう一度操作してください。'));
            // Legacy Edit reloads stored values on failure while retaining validation messages.
            $errors = $user->getErrors();
            $user = $this->Users->get($id);
            $user->setErrors($errors);
        }

        $this->set(compact('user'));
        $this->setListValue();
    }

    /**
     * Delete method (logical delete)
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $user = $this->Users->get($id);

        $now = date('Y-m-d H:i:s');
        $currentUser = $this->getCurrentUser();

        $user->deleted = $now;
        $user->deleted_user_id = $currentUser ? $currentUser->id : 0;

        if ($this->Users->save($user, ['fields' => ['deleted', 'deleted_user_id']])) {
            $this->Flash->success(__('ユーザの削除が完了しました。'));
        } else {
            $this->Flash->error(__('ユーザの削除に失敗しました。もう一度操作してください。'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Login method
     *
     * Mirrors legacy behavior: plain password allowed via LegacyPasswordHasher.
     */
    public function login()
    {
        $this->request->allowMethod(['get', 'post']);
        $result = $this->Authentication->getResult();

        if ($this->request->is('post')) {
            if ($result !== null && $result->isValid()) {
                return $this->redirect(['controller' => 'Customers', 'action' => 'index']);
            }
            $this->Flash->error(__('ユーザ名またはパスワードが誤っています。再度入力してください。'));
        }
    }

    /** Legacy Knowledge URL: /users/home/{userid}/{phone}/{lead_name}. */
    public function home(?string $userid = null, ?string $phone = null, ?string $lead_name = null)
    {
        $this->request->allowMethod(['get', 'post']);
        $user = $userid === null || $userid === '' ? null : $this->Users->find()
            ->where(['Users.loginid' => $userid, 'Users.deleted IS' => null])
            ->first();
        $hasher = new \App\Auth\LegacyPasswordHasher();
        if (!$user || !$hasher->check($this->legacyHomePassword($userid), (string)$user->password)) {
            $this->Flash->error(__('ユーザ名またはパスワードが誤っています。再度入力してください。'));
            return $this->redirect(['action' => 'login']);
        }

        // Verify credentials before replacing and persisting the authenticated identity.
        $this->Authentication->setIdentity($user);
        if ((string)$user->auth === '9') {
            return $this->redirect(['controller' => 'Customers', 'action' => 'ameyo', $phone ?? '', $lead_name ?? '']);
        }
        return $this->redirect(['action' => 'blank']);
    }

    /** Password selection used by the original app's home() action. */
    protected function legacyHomePassword(string $userid): string
    {
        if ($userid === '1965') {
            return '1413';
        }
        if ($userid === '1952') {
            return '2558';
        }
        if (in_array($userid, array_map('strval', range(5195, 5204)), true)) {
            return '0123';
        }
        return '123456';
    }

    public function blank()
    {
        $this->viewBuilder()->setLayout('blank');
    }

    /**
     * Logout method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function logout()
    {
        $this->Authentication->logout();
        return $this->redirect(['action' => 'login']);
    }

    /**
     * SSO method (Single Sign-On)
     *
     * @param string|null $userid User ID
     * @param string|null $phone Customer phone number
     * @param string|null $lead_name Lead/list name
     * @return \Cake\Http\Response|null|void
     */
    public function sso(?string $userid = null, ?string $phone = null, ?string $lead_name = null)
    {
        $this->request->allowMethod(['get', 'post']);
        $user = $userid === null || $userid === '' ? null : $this->Users->find()
            ->where(['Users.loginid' => $userid, 'Users.deleted IS' => null])
            ->first();
        // Original sso() submits this fixed password, even for IDs mapped differently in home().
        $hasher = new \App\Auth\LegacyPasswordHasher();
        if (!$user || !$hasher->check('123456', (string)$user->password)) {
            $this->Flash->error(__('ユーザ名またはパスワードが誤っています。再度入力してください。'));
            return $this->redirect(['action' => 'login']);
        }

        $this->Authentication->setIdentity($user);
        $this->request->getSession()->write('lead_name', $lead_name ?? '');
        return $this->redirect(['controller' => 'Customers', 'action' => 'ameyo', $phone ?? '', $lead_name ?? '']);
    }

    /**
     * Set list values for views
     *
     * @return void
     */
    protected function setListValue(): void
    {
        // 権限
        $authOpt = ['0' => '一般', '9' => '管理者'];

        // 作業者
        $opeOpt = $this->Users->find('list', keyField: 'id', valueField: 'user_name')
            ->where(['Users.deleted IS' => null])
            ->orderBy(['Users.disp_cd' => 'ASC'])
            ->toArray();

        $this->set(compact('authOpt', 'opeOpt'));
    }

    /**
     * Before filter callback
     *
     * @param \Cake\Event\EventInterface<\Cake\Controller\Controller> $event Event
     * @return \Cake\Http\Response|null|void
     */
    public function beforeFilter(EventInterface $event): ?\Cake\Http\Response
    {
        parent::beforeFilter($event);
        $this->Authentication->allowUnauthenticated(['sso', 'login', 'home']);
        return null;
    }
}
