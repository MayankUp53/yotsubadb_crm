<?= $this->Html->css('settings', ['block' => true]) ?>
<div class="settings-page">
    <div class="panel panel-primary">
        <div class="panel-heading"><h3 class="panel-title"><?= $type === 'contacts' ? '対応履歴データ取込' : '顧客データ取込' ?></h3></div>
        <div class="panel-body">
            <nav class="settings-tabs" aria-label="取込データの種類">
                <?= $this->Html->link('顧客データ', ['action' => 'index', '?' => ['type' => 'customers']], ['class' => $type === 'customers' ? 'is-active' : '', 'aria-current' => $type === 'customers' ? 'page' : null]) ?>
                <?= $this->Html->link('対応履歴データ', ['action' => 'index', '?' => ['type' => 'contacts']], ['class' => $type === 'contacts' ? 'is-active' : '', 'aria-current' => $type === 'contacts' ? 'page' : null]) ?>
            </nav>
            <?= $this->Form->create(null, ['type' => 'file', 'url' => ['action' => 'import']]) ?>
            <?= $this->Form->hidden('type', ['value' => $type]) ?>
            <div class="settings-upload-row">
                <label for="import-file" class="settings-upload-title">取込ファイル</label>
                <div><?= $this->Form->control('result', ['id' => 'import-file', 'type' => 'file', 'label' => false, 'accept' => '.csv,.xlsx,.xls,.ods', 'required' => true]) ?></div>
                <div class="settings-actions">
                    <?= $this->Html->link('雛形ダウンロード', ['action' => 'template', '?' => ['type' => $type]], ['class' => 'btn btn-warning']) ?>
                    <?= $this->Form->button('取込', ['class' => 'btn btn-success']) ?>
                </div>
            </div>
            <?= $this->Form->end() ?>
            <p class="settings-note">対応形式：Excel（.xlsx / .xls）、CSV（UTF-8 / Shift-JIS）、ODS。<br>雛形をダウンロードし、項目の順番を変更せずに入力してください。</p>
            <p class="settings-note"><?= $type === 'contacts' ? '顧客IDは必須です。履歴番号が空欄の場合は新規登録、入力されている場合は該当する履歴を更新します。' : '電話番号と名前が一致する顧客は更新されます。一致する顧客がない場合は新規登録されます。' ?></p>
            <?php if (isset($report)): ?>
            <div class="settings-report">
                <div class="alert <?= $report['failed'] ? 'alert-warning' : 'alert-success' ?>">取込結果：新規登録 <?= h($report['inserted']) ?> 件 ／ 更新 <?= h($report['updated']) ?> 件 ／ エラー <?= h($report['failed']) ?> 件</div>
                <?php if ($report['errors']): ?>
                    <p class="settings-note">エラー内容を確認し、修正した行を再度取り込んでください。</p>
                    <div class="table-responsive"><table class="table table-bordered"><thead><tr><th>行番号</th><th>エラー内容</th></tr></thead><tbody>
                        <?php foreach ($report['errors'] as $error): ?><tr><td><?= h($error['row']) ?></td><td><?= h($error['message']) ?></td></tr><?php endforeach ?>
                    </tbody></table></div>
                <?php endif ?>
            </div>
            <?php endif ?>
        </div>
        <div class="settings-actions settings-actions-center">
            <?= $this->Html->link('設定へ戻る', ['controller' => 'Settings', 'action' => 'index'], ['class' => 'btn btn-default']) ?>
            <?= $this->Html->link($type === 'contacts' ? '対応履歴一覧へ戻る' : '顧客一覧へ戻る', ['controller' => $type === 'contacts' ? 'Contacts' : 'Customers', 'action' => 'index'], ['class' => 'btn btn-default']) ?>
        </div>
    </div>
</div>
