<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\LegacySettings;

class SettingsController extends AppController
{
    public function index()
    {
        $this->set(['sections' => LegacySettings::sections(), 'warning' => 0,
            'canWrite' => $this->getCurrentUser() && (string)$this->getCurrentUser()->auth !== '0',
            'canImportContacts' => (string)$this->getCurrentUser()?->auth === '9']);
    }
}
