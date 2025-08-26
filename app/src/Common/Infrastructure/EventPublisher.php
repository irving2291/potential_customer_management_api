<?php
declare(strict_types=1);

namespace App\Common\Infrastructure;

use App\Common\Domain\DomainEvent;

interface EventPublisher
{
    public function publish(DomainEvent $event): void;
}
