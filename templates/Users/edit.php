<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
$this->Form->setTemplates(['inputContainer' => '{{content}}', 'inputContainerError' => '{{content}}{{error}}']);
?>
<div class="panel panel-warning">
    <div class="panel-heading">
        <h3 class="panel-title">ユーザ情報更新</h3>
    </div>
    <?= $this->Form->create($user, [
        'url' => ['controller' => 'Users', 'action' => 'edit', $user->id],
        'context' => ['validator' => 'legacy'],
        'valueSources' => ['context'],
    ]) ?>
    <?= $this->Form->hidden('id') ?>
    <table class="table">
        <tbody>
            <tr>
                <td width="25%" style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">ID</td>
                <td width="25%"><?= $this->Form->control('disp_cd', ['label' => false, 'required' => false, 'type' => 'text', 'class' => 'form-control']) ?></td>
            </tr>
            <tr>
                <td style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">氏名</td>
                <td><?= $this->Form->control('user_name', ['label' => false, 'required' => false, 'type' => 'text', 'class' => 'form-control', 'style' => 'width:100%']) ?></td>
                <td style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">権限</td>
                <td><?= $this->Form->control('auth', ['label' => false, 'type' => 'select', 'class' => 'form-control', 'style' => 'width:100%', 'options' => $authOpt, 'empty' => '-----']) ?></td>
            </tr>
            <tr>
                <td style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">ログインID</td>
                <td><?= $this->Form->control('loginid', ['label' => false, 'required' => false, 'type' => 'text', 'class' => 'form-control', 'style' => 'width:100%']) ?></td>
                <td style="vertical-align:middle;color:#ffffff;" bgcolor="#ff8c00">パスワード</td>
                <td><?= $this->Form->control('password', ['label' => false, 'required' => false, 'type' => 'password', 'class' => 'form-control', 'style' => 'width:100%', 'value' => '']) ?></td>
            </tr>
        </tbody>
    </table>
    <div style="width:95%;margin-bottom:5px;margin-top:5px;" align="center" class="btn_margin">
        <?= $this->Form->button('更　新', ['name' => 'edit', 'type' => 'submit', 'style' => 'width:150px;height:30px;cursor:pointer', 'class' => 'btn-warning btn-xs']) ?>
    </div>
    <?= $this->Form->end() ?>
</div>

