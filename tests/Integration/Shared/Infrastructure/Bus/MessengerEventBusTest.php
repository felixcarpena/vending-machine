<?php

declare(strict_types=1);

namespace Tests\Integration\Shared\Infrastructure\Bus;

use DomainException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Shared\Domain\DomainEvent;
use Shared\Infrastructure\Bus\MessengerEventBus;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;

final class MessengerEventBusTest extends TestCase
{
    #[Test]
    public function delivers_every_event_published(): void
    {
        $event = new class implements DomainEvent {};
        $anotherEvent = clone $event;
        $subscriber = new class {
            /** @var list<DomainEvent> */
            public private(set) array $received = [];

            public function __invoke(DomainEvent $event): void
            {
                $this->received[] = $event;
            }
        };
        $eventBus = self::eventBusWhere($event, $subscriber);

        $eventBus->publish($event, $anotherEvent);

        self::assertSame([$event, $anotherEvent], $subscriber->received);
    }

    #[Test]
    public function lets_the_subscriber_exception_through_unwrapped(): void
    {
        $event = new class implements DomainEvent {};
        $subscriber = new class {
            public function __invoke(DomainEvent $event): never
            {
                throw new DomainException('broken subscriber');
            }
        };
        $eventBus = self::eventBusWhere($event, $subscriber);

        $this->expectExceptionObject(new DomainException('broken subscriber'));

        $eventBus->publish($event);
    }

    private static function eventBusWhere(DomainEvent $event, callable $isHeardBy): MessengerEventBus
    {
        $locator = new HandlersLocator([$event::class => [$isHeardBy]]);

        return new MessengerEventBus(new MessageBus([new HandleMessageMiddleware($locator)]));
    }
}
