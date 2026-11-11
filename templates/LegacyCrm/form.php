<?php
require __DIR__ . '/context.php';
$sourceAction = ($record->isNew() ? 'add' : 'edit');
$sourceController = $isContact ? 'Contacts' : 'Customers';
if ($isContact && $sourceAction === 'add') {
    $sourceController = 'Customers';
    $sourceAction = 'view';
}
?>
<div class="legacy-crm-page">
<?php require __DIR__ . '/source/' . $sourceController . '/' . $sourceAction . '.php'; ?>
</div>
