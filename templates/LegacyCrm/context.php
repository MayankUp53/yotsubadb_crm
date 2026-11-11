<?php
$this->loadHelper('LegacyCrmForm');
$this->LegacyCrmForm->setTemplates(['inputContainer' => '{{content}}', 'inputContainerError' => '{{content}}{{error}}', 'submitContainer' => '{{content}}']);
$this->Html->css('legacy-crm', ['block' => true]);
$this->Html->css('legacy-jquery.datetimepicker.min', ['block' => true]);
$this->Html->css('legacy-jquery.timepicker', ['block' => true]);
$this->Html->script('legacy-jquery.datetimepicker.full.min', ['block' => true]);
$this->Html->script('legacy-jquery.timepicker', ['block' => true]);
$this->Html->script('legacy-crm', ['block' => true]);
if ($this->getRequest()->getParam('controller') === 'Customers' && in_array($this->getRequest()->getParam('action'), ['add', 'edit'], true)) {
    $this->Html->script('https://yubinbango.github.io/yubinbango/yubinbango.js', ['block' => true]);
}

$customerEntity = $isContact ? ($customer ?? $record->customer ?? null) : ($customer ?? $record ?? null);
$contactEntity = $isContact ? ($record ?? null) : ($contact ?? null);
$this->set(compact('customerEntity', 'contactEntity'));
$legacyValues = static function ($entity, string $type): array {
    $fields = $type === 'Customer' ? \App\Service\LegacyCrm::customerFields() : \App\Service\LegacyCrm::contactFields();
    $fields += array_fill_keys(['id', 'customer_id', 'created', 'updated', 'deleted', 'created_user_id', 'updated_user_id', 'deleted_user_id', 'article_name', 'article_addr', 'date_from', 'date_to'], '');
    for ($i = 1; $i <= 12; $i++) $fields['p_detail' . $i] = '';
    $values = array_replace(array_fill_keys(array_keys($fields), ''), $entity?->toArray() ?? []);
    foreach ($values as $field => $value) {
        if ($value === null) $values[$field] = '';
        elseif (is_object($value) && method_exists($value, 'format')) $values[$field] = $value->format('Y-m-d H:i:s');
    }
    return $values;
};
$customer = ['Customer' => $legacyValues($customerEntity, 'Customer')];
$contact = ['Contact' => $legacyValues($contactEntity, 'Contact'), 'Customer' => $customer['Customer']];
$legacyRecord = $isContact ? $contact : $customer;
$historyRows = [];
foreach ($contacts ?? [] as $history) {
    $historyRows[] = ['Contact' => $legacyValues($history, 'Contact'), 'Customer' => $legacyValues($history->customer ?? $customerEntity, 'Customer')];
}
$contacts = $historyRows;
$customers = [];
if (isset($records)) {
    foreach ($records as $item) {
        if ($isContact) $contacts[] = ['Contact' => $legacyValues($item, 'Contact'), 'Customer' => $legacyValues($item->customer, 'Customer')];
        else $customers[] = ['Customer' => $legacyValues($item, 'Customer')];
    }
}
$row = $contact;
$legacyAuth = $canDelete ? '9' : '0';
$lead_name = $isContact && $contactEntity && !$contactEntity->isNew()
    ? (string)$contactEntity->list_name : ($this->getRequest()->getSession()->read('lead_name') ?? '');
$tel = null;
$genderOpt = $options['gender'];
$nendaiOpt = $options['nendai'];
$productOpt = $inputOptions['product_name1'];
$dispositionOpt = $legacyActiveDispositions ?? $inputOptions['kekka'];
$dispositionAllOpt = $legacyAllDispositions ?? $options['kekka'];
$OPdispOpt = array_intersect_key($dispositionOpt, array_flip([1, 2, 3, 4, 5, 7, 8, 9, 10]));
$statusOpt = $inputOptions['reason'];
$categoryOpt = $inputOptions['category_cd'];
$subcategoryOpt = array_values($options['joukyou']);
$famOpt = $options['kazoku_kankyou'];
$impOpt = $options['impression'];
$correspondOpt = $inputOptions['correspond_cd'];
$agentOpt = $inputOptions['agent_cd'];
$contractOpt = [];
$hvOpt = ['1' => '対象'];
$cbkOpt = ['1' => '不要', '2' => '要', '3' => '完了'];
$opeOpt = $users;
$opeOpt[''] = '';
$opeOpt[0] ??= '';
foreach ([$customer, $contact, ...$contacts] as $item) {
    foreach (['Customer', 'Contact'] as $type) {
        foreach (['created_user_id', 'updated_user_id', 'deleted_user_id'] as $field) {
            $id = $item[$type][$field] ?? '';
            $opeOpt[$id] ??= '';
        }
    }
}
$products = [];
foreach ($productPrices as $id => $price) $products[] = ['MtProduct' => ['id' => $id, 'unit_price' => $price]];
