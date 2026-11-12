<?php

declare(strict_types=1);

namespace Tests\Integration\Shared\Infrastructure\Bus;

use DomainException;
use LogicException;
use Monolog\Handler\TestHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\LogRecord;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Shared\Domain\DomainEvent;
use Shared\Domain\Refusal;
use Shared\Infrastructure\Bus\LoggingMiddleware;
use Stringable;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;

final class LoggingMiddlewareTest extends TestCase
{
    #[Test]
    public function logs_a_handled_message_as_info(): void
    {
        $recorded = new TestHandler();
        $message = new class {};

        self::busWhere($message, static function (): void {}, $recorded)->dispatch($message);

        self::assertSame(['INFO'], self::levelsIn($recorded));
    }

    #[Test]
    public function logs_a_published_event_as_debug(): void
    {
        $recorded = new TestHandler();
        $event = new class implements DomainEvent {};

        self::busWhere($event, static function (): void {}, $recorded)->dispatch($event);

        self::assertSame(['DEBUG'], self::levelsIn($recorded));
    }

    #[Test]
    public function says_how_long_the_message_took(): void
    {
        $recorded = new TestHandler();
        $message = new class {};

        self::busWhere($message, static function (): void {}, $recorded)->dispatch($message);

        self::assertMatchesRegularExpression('/ handled in \d+\.\d{2} ms$/', self::onlyRecordIn($recorded)->message);
    }

    #[Test]
    public function adds_the_message_fields_as_text(): void
    {
        $recorded = new TestHandler();
        $message = new class {
            public Stringable $machineId;

            public function __construct()
            {
                $this->machineId = new class implements Stringable {
                    public function __toString(): string
                    {
                        return 'machine-1';
                    }
                };
            }
        };

        self::busWhere($message, static function (): void {}, $recorded)->dispatch($message);

        self::assertSame(['machineId' => 'machine-1'], self::onlyRecordIn($recorded)->context);
    }

    #[Test]
    public function writes_an_enum_field_by_its_name(): void
    {
        $recorded = new TestHandler();
        $message = new class {
            public Level $level = Level::Warning;
        };

        self::busWhere($message, static function (): void {}, $recorded)->dispatch($message);

        self::assertSame(['level' => 'Warning'], self::onlyRecordIn($recorded)->context);
    }

    #[Test]
    public function leaves_out_the_fields_it_cannot_write_as_text(): void
    {
        $recorded = new TestHandler();
        $message = new class {
            /** @var list<int> */
            public array $counts = [1, 2];
        };

        self::busWhere($message, static function (): void {}, $recorded)->dispatch($message);

        self::assertSame([], self::onlyRecordIn($recorded)->context);
    }

    #[Test]
    public function logs_a_business_refusal_as_a_notice(): void
    {
        $recorded = new TestHandler();
        $message = new class {};
        $bus = self::busWhere($message, static fn(): never => throw new class ('refused') extends Refusal {}, $recorded);

        try {
            $bus->dispatch($message);
        } catch (HandlerFailedException) {
        }

        self::assertSame(['NOTICE'], self::levelsIn($recorded));
    }

    #[Test]
    public function logs_a_domain_exception_that_is_not_a_refusal_as_an_error(): void
    {
        $recorded = new TestHandler();
        $message = new class {};
        $bus = self::busWhere($message, static fn(): never => throw new DomainException('broken'), $recorded);

        try {
            $bus->dispatch($message);
        } catch (HandlerFailedException) {
        }

        self::assertSame(['ERROR'], self::levelsIn($recorded));
    }

    #[Test]
    public function logs_an_unexpected_failure_as_an_error(): void
    {
        $recorded = new TestHandler();
        $message = new class {};
        $bus = self::busWhere($message, static fn(): never => throw new LogicException('broken'), $recorded);

        try {
            $bus->dispatch($message);
        } catch (HandlerFailedException) {
        }

        self::assertSame(['ERROR'], self::levelsIn($recorded));
    }

    #[Test]
    public function says_why_the_message_failed(): void
    {
        $recorded = new TestHandler();
        $message = new class {};
        $bus = self::busWhere($message, static fn(): never => throw new LogicException('broken'), $recorded);

        try {
            $bus->dispatch($message);
        } catch (HandlerFailedException) {
        }

        self::assertStringEndsWith('failed: broken', self::onlyRecordIn($recorded)->message);
    }

    #[Test]
    public function lets_the_failure_through(): void
    {
        $message = new class {};
        $bus = self::busWhere($message, static fn(): never => throw new DomainException('refused'), new TestHandler());

        $this->expectException(HandlerFailedException::class);

        $bus->dispatch($message);
    }

    private static function busWhere(object $message, callable $isHandledBy, TestHandler $recorded): MessageBus
    {
        return new MessageBus([
            new LoggingMiddleware(new Logger('bus', [$recorded])),
            new HandleMessageMiddleware(new HandlersLocator([$message::class => [$isHandledBy]])),
        ]);
    }

    /** @return list<string> */
    private static function levelsIn(TestHandler $recorded): array
    {
        return array_values(array_map(
            static fn(LogRecord $record): string => $record->level->getName(),
            $recorded->getRecords(),
        ));
    }

    private static function onlyRecordIn(TestHandler $recorded): LogRecord
    {
        [$record] = $recorded->getRecords();

        return $record;
    }
}
