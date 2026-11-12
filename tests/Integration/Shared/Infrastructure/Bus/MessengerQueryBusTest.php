<?php

declare(strict_types=1);

namespace Tests\Integration\Shared\Infrastructure\Bus;

use DomainException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Shared\Application\Bus\Query;
use Shared\Infrastructure\Bus\MessengerQueryBus;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;

final class MessengerQueryBusTest extends TestCase
{
    #[Test]
    public function returns_what_the_handler_answers(): void
    {
        $query = new /** @implements Query<string> */ class implements Query {};
        $answerer = new class {
            /** @param Query<string> $query */
            public function __invoke(Query $query): string
            {
                return 'the answer';
            }
        };
        $queryBus = self::queryBusWhere($query, $answerer);

        $answer = $queryBus->ask($query);

        self::assertSame('the answer', $answer);
    }

    #[Test]
    public function lets_the_handler_exception_through_unwrapped(): void
    {
        $query = new /** @implements Query<string> */ class implements Query {};
        $refuser = new class {
            /** @param Query<string> $query */
            public function __invoke(Query $query): never
            {
                throw new DomainException('not found');
            }
        };
        $queryBus = self::queryBusWhere($query, $refuser);

        $this->expectExceptionObject(new DomainException('not found'));

        $queryBus->ask($query);
    }

    /** @param Query<mixed> $query */
    private static function queryBusWhere(Query $query, callable $isAnsweredBy): MessengerQueryBus
    {
        $locator = new HandlersLocator([$query::class => [$isAnsweredBy]]);

        return new MessengerQueryBus(new MessageBus([new HandleMessageMiddleware($locator)]));
    }
}
