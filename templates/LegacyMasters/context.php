<?php
/** Data adapter for the original app view markup. */
$this->Form->setTemplates([
    'inputContainer' => '{{content}}',
    'inputContainerError' => '{{content}}{{error}}',
    'submitContainer' => '{{content}}',
]);
$opeOpt = $users;
$legacyValues = static function ($entity) use ($activeField): array {
    $values = $entity->toArray();
    $values['active_flg'] = $entity->get($activeField);
    foreach (['created', 'updated', 'deleted'] as $field) {
        if (isset($values[$field]) && is_object($values[$field]) && method_exists($values[$field], 'format')) {
            $values[$field] = $values[$field]->format('Y-m-d H:i:s');
        }
    }
    return $values;
};
if (isset($record)) {
    $legacyRecord = [$section['model'] => $legacyValues($record)];
    ${lcfirst($section['model'])} = $legacyRecord;
    foreach (['created_user_id', 'updated_user_id', 'deleted_user_id'] as $field) {
        $id = $record->get($field);
        $opeOpt[$id ?? ''] = $users[$id ?? ''] ?? '';
    }
}
if (isset($records)) {
    $legacyRows = [];
    foreach ($records as $item) {
        $legacyRows[] = [$section['model'] => $legacyValues($item)];
    }
    $sourceNames = ['MtProduct' => 'mtProducts', 'MtDisposition' => 'mtDispositions', 'MtCategory' => 'mtCategories',
        'MtSubcategory' => 'mtSubcategories', 'MtStatus' => 'mtStatuses'];
    ${$sourceNames[$section['model']]} = $legacyRows;
}
