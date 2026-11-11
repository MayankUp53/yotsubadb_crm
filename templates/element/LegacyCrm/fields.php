<?php
$multi = ['shoujou', 'impression', 'joukyou', 'day_service_days'];
$textareas = ['memo', 'contact_detail', 'answer_detail', 'remark'];
?>
<div class="row" style="padding:15px">
<?php foreach ($fields as $field => $label): ?>
    <div class="<?= in_array($field, $textareas, true) ? 'col-md-6' : 'col-md-3 col-sm-6' ?>" style="margin-bottom:10px">
    <?php
    $config = ['label' => $label, 'class' => 'form-control', 'type' => 'text', 'data-field' => $field];
    if (isset($options[$field])) {
        $available = $inputOptions[$field] ?? $options[$field];
        $selected = (string)$entity->get($field);
        if ($selected !== '' && isset($options[$field][$selected])) {
            $available[$selected] = $options[$field][$selected];
        }
        $config += ['options' => $available, 'empty' => '-----'];
        $config['type'] = 'select';
    }
    if (in_array($field, $multi, true)) {
        preg_match_all('/\[([^\]]*)\]/u', (string)$entity->get($field), $matches);
        $config += ['multiple' => 'checkbox', 'value' => $matches[1]];
        $config['class'] = '';
        unset($config['empty']);
    }
    if (in_array($field, $textareas, true)) {
        $config['type'] = 'textarea';
        $config['rows'] = 4;
    }
    if ($field === 'sakiokuri_datetime') {
        $config['type'] = 'datetime-local';
    }
    if (preg_match('/^(quantity|unit_price|price)\d$/', $field) || in_array($field, ['total_price', 'age', 'dob', 'day_service_times'], true)) {
        $config['type'] = 'number';
        $config['min'] = 0;
        $config['step'] = in_array($field, ['age', 'dob', 'day_service_times'], true) ? '1' : '0.01';
    }
    if ($field === 'total_price' || str_starts_with($field, 'unit_price')) {
        $config['readonly'] = true;
    }
    if (in_array($field, ['ap_id', 'ap_name'], true)) {
        $config['readonly'] = true;
    }
    ?>
    <?= $this->Form->control($field, $config) ?>
    </div>
<?php endforeach ?>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('[data-field]')?.closest('form');
    if (!form) return;
    const prices = <?= json_encode($productPrices ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const input = key => form.querySelector('[data-field="' + key + '"]');
    form.addEventListener('input', function (event) {
        const key = event.target.dataset.field || '';
        if (event.target.closest('[data-field="day_service_days"]') || event.target.name === 'day_service_days[]') {
            const count = form.querySelectorAll('input[name="day_service_days[]"]:checked').length;
            if (input('day_service_times')) input('day_service_times').value = count;
        }
        if (key === 'age' || key === 'dob') {
            const value = Number(event.target.value);
            const other = input(key === 'age' ? 'dob' : 'age');
            if (other) other.value = event.target.value && value >= 0 && value <= new Date().getFullYear() ? new Date().getFullYear() - value : '';
        }
        if (!/^(product_name|quantity|unit_price|price)\d$/.test(key)) return;
        const index = key.slice(-1);
        if (key.startsWith('product_name') && input('unit_price' + index)) input('unit_price' + index).value = prices[event.target.value] ?? '';
        if (!key.startsWith('price') && input('price' + index)) input('price' + index).value = (Number(input('quantity' + index)?.value || 0) * Number(input('unit_price' + index)?.value || 0)).toFixed(2);
        if (input('total_price')) input('total_price').value = [1,2,3,4,5].reduce((sum, i) => sum + Number(input('price' + i)?.value || 0), 0).toFixed(2);
    });
});
</script>
