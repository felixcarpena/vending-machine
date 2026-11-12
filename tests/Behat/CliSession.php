<?php

declare(strict_types=1);

namespace Tests\Behat;

use RuntimeException;
use Symfony\Component\Process\InputStream;
use Symfony\Component\Process\Process;

final class CliSession
{
    private const string PROMPT = '> ';

    private string $pending = '';

    private function __construct(
        private readonly Process $process,
        private readonly InputStream $input,
    ) {}

    public static function start(string $projectDir): self
    {
        $input = new InputStream();
        $process = new Process([$projectDir . '/bin/vending-machine'], $projectDir, timeout: 30);
        $process->setInput($input);
        $process->start();

        $session = new self($process, $input);
        $session->nextResponse();

        return $session;
    }

    public function send(string $line): string
    {
        $this->input->write($line . PHP_EOL);

        return $this->nextResponse();
    }

    public function stop(): void
    {
        $this->input->close();
        $this->process->stop();
    }

    private function nextResponse(): string
    {
        while (!str_ends_with($this->pending, self::PROMPT)) {
            $running = $this->process->isRunning();
            $this->pending .= $this->process->getIncrementalOutput();
            if (!$running && !str_ends_with($this->pending, self::PROMPT)) {
                throw new RuntimeException(sprintf(
                    "The machine stopped with exit code %s:\n%s",
                    $this->process->getExitCode(),
                    $this->process->getErrorOutput(),
                ));
            }
            $this->process->checkTimeout();
            usleep(1_000);
        }

        $response = substr($this->pending, 0, -strlen(self::PROMPT));
        $this->pending = '';

        return $response;
    }
}
