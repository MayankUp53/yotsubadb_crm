<?php
require __DIR__ . '/context.php';
$this->Html->css('legacy-settings', ['block' => true]);
?>
<div class="settings-page">
<?php require __DIR__ . '/source/' . $section['model'] . '/' . (!$record->isNew() ? 'edit' : 'add') . '.php'; ?>
</div>
