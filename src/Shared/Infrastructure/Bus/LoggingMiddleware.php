<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Bus;

use Psr\Log\LoggerInterface;
use ReflectionClass;
use Stringable;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

final readonly class LoggingMiddleware implements MiddlewareInterface
{
    public function __construct(private LoggerInterface $logger) {}

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $message = $envelope->getMessage();
        $name = new ReflectionClass($message)->getShortName();
        $startedAt = hrtime(true);

        try {
            $handled = $stack->next()->handle($envelope, $stack);
        } catch (HandlerFailedException $failure) {
            $reason = ($failure->getPrevious() ?? $failure)->getMessage();
            $this->logger->error(sprintf('%s failed: %s', $name, $reason), self::fieldsOf($message));

            throw $failure;
        }

        $duration = (hrtime(true) - $startedAt) / 1_000_000;
        $this->logger->info(sprintf('%s handled in %.2f ms', $name, $duration), self::fieldsOf($message));

        return $handled;
    }

    /** @return array<string> */
    private static function fieldsOf(object $message): array
    {
        return array_map(
            static fn(mixed $value): string => $value instanceof Stringable ? (string) $value : get_debug_type($value),
            get_object_vars($message),
        );
    }
}
