<?php
declare(strict_types=1);

namespace App\View\Helper;

use Cake\Utility\Inflector;
use Cake\View\Helper\FormHelper;

/** Adapt the original form calls without changing their visible labels or layout. */
class LegacyCrmFormHelper extends FormHelper
{
    private array $checkboxGroups = [];

    public function create(mixed $context = null, array $options = []): string
    {
        $model = is_string($context) ? $context : null;
        if ($model !== null) {
            $context = $model === 'Customer' ? $this->getView()->get('customerEntity') : $this->getView()->get('contactEntity');
        }
        if (isset($options['action']) || isset($options['controller'])) {
            $parts = explode('/', (string)($options['action'] ?? $this->getView()->getRequest()->getParam('action')));
            $controller = $options['controller'] ?? $this->getView()->getRequest()->getParam('controller');
            $controller = strtolower((string)$controller) === 'contact' ? 'Contacts' : Inflector::camelize((string)$controller);
            $url = ['controller' => $controller, 'action' => array_shift($parts)];
            foreach ($parts as $part) {
                if ($part !== '') $url[] = $part;
            }
            if ($model === 'Contact' && $url['action'] === 'add' && !$parts) {
                $url[] = $this->getView()->get('customerEntity')?->id;
            }
            $options['url'] = $url;
            unset($options['action'], $options['controller']);
        }
        $this->checkboxGroups = [];
        $html = parent::create($context, $options);
        if ($model === 'Contact' && $this->getView()->getRequest()->getParam('action') === 'view'
            && $this->getView()->getRequest()->getParam('controller') === 'Contacts') {
            $html .= $this->hidden('_legacy_contact_view', ['value' => '1']);
        }
        return $html;
    }

    public function input(string $fieldName, array $options = []): string
    {
        unset($options['div']);
        if (str_starts_with($fieldName, 's_')) {
            $search = $this->getView()->get('searchData') ?? [];
            $options['value'] = $search[$fieldName] ?? $search[substr($fieldName, 2)] ?? '';
            $fieldName = 'Search.' . $fieldName;
        }
        return parent::control($fieldName, $options);
    }

    public function checkbox(string $fieldName, array $options = [], array $legacyOptions = []): array|string
    {
        $options = array_merge($options, $legacyOptions);
        unset($options['div'], $options['label'], $options['type']);
        $prefix = '';
        if (str_ends_with($fieldName, '.')) {
            $fieldName = rtrim($fieldName, '.');
            if (!isset($this->checkboxGroups[$fieldName])) {
                $prefix = $this->hidden($fieldName, ['value' => '']);
            }
            $this->checkboxGroups[$fieldName] = ($this->checkboxGroups[$fieldName] ?? 0) + 1;
            $options['name'] = $fieldName . '[]';
            $options['hiddenField'] = false;
            $options['id'] ??= $fieldName . '-' . $this->checkboxGroups[$fieldName];
        }
        if (array_key_exists('checked', $options)) $options['checked'] = (bool)$options['checked'];
        return $prefix . parent::checkbox($fieldName, $options);
    }

    public function submit(?string $caption = null, array $options = []): string
    {
        unset($options['div']);
        if (in_array($options['name'] ?? '', ['back', 'delete', 'clear', 'download'], true)) {
            $options['formnovalidate'] = true;
        }
        return parent::submit($caption, $options);
    }

    public function end(array|string $secureAttributes = []): string
    {
        if (is_string($secureAttributes)) return $this->submit($secureAttributes) . parent::end();
        return parent::end($secureAttributes);
    }
}
