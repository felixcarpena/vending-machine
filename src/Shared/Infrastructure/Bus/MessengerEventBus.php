<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Bus;

use Shared\Application\Bus\EventBus;
use Shared\Domain\DomainEvent;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class MessengerEventBus implements EventBus
{
    public function __construct(private MessageBusInterface $bus) {}

    public function publish(DomainEvent ...$events): void
    {
        foreach ($events as $event) {
            try {
                $this->bus->dispatch($event);
            } catch (HandlerFailedException $failure) {
                throw $failure->getPrevious() ?? $failure;
            }
        }
    }
}
