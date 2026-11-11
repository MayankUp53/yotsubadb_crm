<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
$this->Form->setTemplates(['inputContainer' => '{{content}}', 'inputContainerError' => '{{content}}{{error}}']);
?>
<div class="panel panel-info">
    <div class="panel-heading">
        <h3 class="panel-title">ユーザ新規追加</h3>
    </div>
    <?= $this->Form->create($user, ['url' => ['controller' => 'Users', 'action' => 'add'], 'context' => ['validator' => 'legacy']]) ?>
    <table class="table">
        <tbody>
            <tr>
                <td width="25%" style="vertical-align:middle;color:#ffffff;" bgcolor="#4169E1">ID</td>
                <td width="25%"><?= $this->Form->control('disp_cd', ['label' => false, 'required' => false, 'type' => 'text', 'class' => 'form-control']) ?></td>
                <td width="25%" style="vertical-align:middle;color:#ffffff;" bgcolor="#4169E1">権限</td>
                <td width="25%"><?= $this->Form->control('auth', ['label' => false, 'required' => false, 'type' => 'select', 'class' => 'form-control', 'options' => $authOpt, 'empty' => '-----']) ?></td>
            </tr>
            <tr>
                <td style="vertical-align:middle;color:#ffffff;" bgcolor="#4169E1">氏名</td>
                <td><?= $this->Form->control('user_name', ['label' => false, 'required' => false, 'type' => 'text', 'class' => 'form-control', 'style' => 'width:100%']) ?></td>
            </tr>
            <tr>
                <td style="vertical-align:middle;color:#ffffff;" bgcolor="#4169E1">ログインID</td>
                <td><?= $this->Form->control('loginid', ['label' => false, 'required' => false, 'type' => 'text', 'class' => 'form-control', 'style' => 'width:100%', 'value' => '']) ?></td>
                <td style="vertical-align:middle;color:#ffffff;" bgcolor="#4169E1">パスワード</td>
                <td><?= $this->Form->control('password', ['label' => false, 'required' => false, 'type' => 'password', 'class' => 'form-control', 'style' => 'width:100%', 'value' => '']) ?></td>
            </tr>
        </tbody>
    </table>
    <div style="width:95%;margin-bottom:5px;margin-top:5px;" align="center" class="btn_margin">
        <?= $this->Form->button('登　録', ['name' => 'edit', 'type' => 'submit', 'style' => 'width:150px;height:30px;cursor:pointer', 'class' => 'btn-info btn-xs']) ?>
    </div>
    <?= $this->Form->end() ?>
</div>

