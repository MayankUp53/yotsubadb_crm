<?php
declare(strict_types=1);

namespace App\Model\Behavior;

use Cake\Cache\Cache;
use Cake\Event\EventInterface;
use Cake\ORM\Behavior;

/** Customer changes can affect both Customer and Contact list totals. */
class PaginationCountBehavior extends Behavior
{
    public function afterSave(EventInterface $event): void
    {
        Cache::clearGroup('crm_pagination', 'crm_pagination');
    }

    public function afterDelete(EventInterface $event): void
    {
        Cache::clearGroup('crm_pagination', 'crm_pagination');
    }
}
