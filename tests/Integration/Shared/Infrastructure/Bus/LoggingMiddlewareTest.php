<?php

declare(strict_types=1);

namespace Tests\Integration\Shared\Infrastructure\Bus;

use DomainException;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Monolog\LogRecord;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
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
    public function logs_a_failed_message_as_an_error_with_the_reason(): void
    {
        $recorded = new TestHandler();
        $message = new class {};
        $bus = self::busWhere($message, static fn(): never => throw new DomainException('refused'), $recorded);

        try {
            $bus->dispatch($message);
        } catch (HandlerFailedException) {
        }

        self::assertStringEndsWith('failed: refused', self::onlyRecordIn($recorded)->message);
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
