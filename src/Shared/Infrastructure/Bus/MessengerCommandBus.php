<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Bus;

use Shared\Application\Bus\Command;
use Shared\Application\Bus\CommandBus;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class MessengerCommandBus implements CommandBus
{
    public function __construct(private MessageBusInterface $bus) {}

    public function dispatch(Command $command): void
    {
        try {
            $this->bus->dispatch($command);
        } catch (HandlerFailedException $failure) {
            throw $failure->getPrevious() ?? $failure;
        }
    }
}
