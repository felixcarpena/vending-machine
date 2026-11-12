<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Bus;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use ReflectionClass;
use Shared\Domain\DomainEvent;
use Shared\Domain\Refusal;
use Stringable;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use UnitEnum;

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
            $reason = $failure->getPrevious() ?? $failure;
            $this->logger->log(
                $reason instanceof Refusal ? LogLevel::NOTICE : LogLevel::ERROR,
                sprintf('%s failed: %s', $name, $reason->getMessage()),
                self::fieldsOf($message),
            );

            throw $failure;
        }

        $duration = (hrtime(true) - $startedAt) / 1_000_000;
        $this->logger->log(
            $message instanceof DomainEvent ? LogLevel::DEBUG : LogLevel::INFO,
            sprintf('%s handled in %.2f ms', $name, $duration),
            self::fieldsOf($message),
        );

        return $handled;
    }

    /** @return array<string> */
    private static function fieldsOf(object $message): array
    {
        $fields = [];
        foreach (get_object_vars($message) as $field => $value) {
            if ($value instanceof Stringable) {
                $fields[$field] = (string) $value;
            } elseif ($value instanceof UnitEnum) {
                $fields[$field] = $value->name;
            }
        }

        return $fields;
    }
}
