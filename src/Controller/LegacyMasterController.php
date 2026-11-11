<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\LegacySettings;
use App\Model\Table\LegacyMasterTable;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\NotFoundException;

/** Shared CakePHP 5 implementation of the original Settings master controllers. */
class LegacyMasterController extends AppController
{
    protected LegacyMasterTable $Master;

    public function initialize(): void
    {
        parent::initialize();
        if (!isset(LegacySettings::sections()[$this->getName()])) {
            throw new NotFoundException();
        }
        $this->Master = $this->fetchTable($this->getName());
    }

    protected function checkWriteAccess(): void
    {
        $user = $this->getCurrentUser();
        if (!$user || (string)$user->auth === '0') {
            throw new ForbiddenException('設定を変更する権限がありません。');
        }
    }

    protected function masterQuery()
    {
        return $this->Master->find()->where([$this->getName() . '.deleted IS' => null]);
    }

    protected function record(?string $id)
    {
        return $this->masterQuery()->where([$this->getName() . '.id' => $id])->firstOrFail();
    }

    protected function masterViewData(): void
    {
        $section = LegacySettings::sections()[$this->getName()];
        $users = $this->fetchTable('Users')->find('list', keyField: 'id', valueField: 'user_name')->toArray();
        $this->set(compact('section', 'users') + [
            'warning' => 0,
            'isProduct' => $this->getName() === 'MtProducts',
            'activeField' => $this->Master->activeField(),
            'activeOpt' => ['0' => '無効', '1' => '有効'],
            'canWrite' => $this->getCurrentUser() && (string)$this->getCurrentUser()->auth !== '0',
        ]);
    }

    public function index()
    {
        $records = $this->masterQuery()->orderBy(['disp_cd' => 'ASC', 'id' => 'ASC'])->all();
        $this->masterViewData();
        $this->set(compact('records'));
        $this->viewBuilder()->setTemplate('/LegacyMasters/index');
    }

    public function view(?string $id = null)
    {
        $record = $this->record($id);
        $this->masterViewData();
        $this->set(compact('record'));
        $this->viewBuilder()->setTemplate('/LegacyMasters/view');
    }

    public function add()
    {
        return $this->masterForm();
    }

    public function edit(?string $id = null)
    {
        return $this->masterForm($id);
    }

    protected function masterForm(?string $id = null)
    {
        $this->checkWriteAccess();
        $record = $id === null ? $this->Master->newEmptyEntity() : $this->record($id);
        if ($this->request->is(['post', 'put', 'patch'])) {
            if ($this->request->getData('back') !== null) {
                return $this->redirect(['action' => 'index']);
            }
            if ($id !== null && $this->request->getData('delete') !== null) {
                return $this->delete($id);
            }
            $model = LegacySettings::sections()[$this->getName()]['model'];
            $raw = $this->request->getData($model) ?? $this->request->getData();
            $activeField = $this->Master->activeField();
            $data = array_intersect_key($raw, array_flip(['item_name', 'active_flg', 'active']));
            if (isset($data['item_name'])) {
                $data['item_name'] = trim((string)$data['item_name']);
            }
            if (array_key_exists('active_flg', $data) || array_key_exists('active', $data)) {
                $value = $data['active_flg'] ?? $data['active'];
                $data[$activeField] = $value;
                if ($this->Master->getSchema()->hasColumn('active') && $this->Master->getSchema()->hasColumn('active_flg')) {
                    $data['active'] = $data['active_flg'] = $value;
                }
            }
            if ($id === null && $this->getName() === 'MtProducts') {
                $data['unit_price'] = $raw['unit_price'] ?? null;
                if ($data['unit_price'] === '') {
                    $data['unit_price'] = null;
                }
            }
            $now = date('Y-m-d H:i:s');
            $userId = $this->getCurrentUser()->id;
            $data += ['updated' => $now, 'updated_user_id' => $userId];
            if ($id === null) {
                $query = $this->Master->find();
                $maximum = $query->select(['maximum' => $query->func()->max('disp_cd')])->first()->maximum ?? 0;
                $data += ['created' => $now, 'created_user_id' => $userId, 'disp_cd' => (int)$maximum + 1];
            }
            $record = $this->Master->patchEntity($record, $data);
            if ($this->Master->save($record)) {
                $title = LegacySettings::sections()[$this->getName()]['title'] . ($this->getName() === 'MtProducts' ? '' : '区分');
                $this->Flash->success($title . 'の' . ($id === null ? '登録' : '更新') . 'が完了しました。');
                return $this->redirect(['action' => 'index']);
            }
            $title = LegacySettings::sections()[$this->getName()]['title'] . ($this->getName() === 'MtProducts' ? '' : '区分');
            $this->Flash->error($title . 'の' . ($id === null ? '登録' : '更新') . 'に失敗しました。もう一度操作してください。');
        }
        $this->masterViewData();
        $this->set(compact('record'));
        $this->viewBuilder()->setTemplate('/LegacyMasters/form');
    }

    public function delete(?string $id = null)
    {
        $this->request->allowMethod(['post', 'put', 'patch', 'delete']);
        $this->checkWriteAccess();
        $record = $this->record($id);
        $record->deleted = date('Y-m-d H:i:s');
        $record->deleted_user_id = $this->getCurrentUser()->id;
        $this->Master->saveOrFail($record);
        $title = LegacySettings::sections()[$this->getName()]['title'] . ($this->getName() === 'MtProducts' ? '' : '区分');
        $this->Flash->success($title . 'の削除が完了しました。');
        return $this->redirect(['action' => 'index']);
    }

    public function sortupdate(?string $ids = null)
    {
        $this->request->allowMethod(['post', 'put', 'patch']);
        $this->checkWriteAccess();
        $ids = $this->request->getData('ids') ?? $ids;
        $ids = is_array($ids) ? $ids : explode('-', trim((string)$ids, '-'));
        if (!$ids || array_filter($ids, static fn($id) => !ctype_digit((string)$id))) {
            throw new BadRequestException('表示順序が正しくありません。');
        }
        $ids = array_map('intval', $ids);
        $existing = $this->masterQuery()->find('list', keyField: 'id', valueField: 'id')->toArray();
        if (count($ids) !== count(array_unique($ids)) || count($ids) !== count($existing) || array_diff($ids, $existing)) {
            throw new BadRequestException('一覧が変更されています。再読み込みしてください。');
        }
        $allRanks = $this->Master->find()->select(['disp_cd', 'deleted'])->all();
        $reserved = [];
        $minimum = 0;
        foreach ($allRanks as $rank) {
            $minimum = min($minimum, (int)$rank->disp_cd);
            if ($rank->deleted !== null) {
                $reserved[(int)$rank->disp_cd] = true;
            }
        }
        $this->Master->getConnection()->transactional(function () use ($ids, $minimum, $reserved): void {
            // Stage ranks so schemas with a unique display-order constraint are supported.
            foreach ($ids as $position => $id) {
                $this->Master->updateAll(['disp_cd' => $minimum - $position - 1], ['id' => $id]);
            }
            $position = 1;
            foreach ($ids as $id) {
                while (isset($reserved[$position])) {
                    $position++;
                }
                $this->Master->updateAll(['disp_cd' => $position++, 'updated' => date('Y-m-d H:i:s'), 'updated_user_id' => $this->getCurrentUser()->id], ['id' => $id]);
            }
        });
        $this->Flash->success('表示順序の変更が完了しました。');
        return $this->redirect(['action' => 'index']);
    }
}
