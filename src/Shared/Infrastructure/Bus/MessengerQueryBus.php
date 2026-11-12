<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Bus;

use Shared\Application\Bus\Query;
use Shared\Application\Bus\QueryBus;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

final readonly class MessengerQueryBus implements QueryBus
{
    public function __construct(private MessageBusInterface $bus) {}

    public function ask(Query $query): mixed
    {
        try {
            $envelope = $this->bus->dispatch($query);
        } catch (HandlerFailedException $failure) {
            throw $failure->getPrevious() ?? $failure;
        }

        return $envelope->last(HandledStamp::class)?->getResult();
    }
}
