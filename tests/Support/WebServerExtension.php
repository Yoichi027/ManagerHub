<?php
/*
IMPORTANT: This server is intentionally started with APP_ENV=test so that the web tests actually run against the test environment and test database.
I couldn't find a simpler and reliable way to make Codeception consistently propagate the test environment to the application server, so this is the solution I'm using for now.
If you find a simpler solution that works reliably in all cases, please replace this setup and delete this monstrosity. Just make absolutely sure the tests are really running against the test environment before doing so. 🙏
*/

declare(strict_types=1);

namespace App\Tests\Support;

use Codeception\Events;
use Codeception\Exception\ExtensionException;
use Codeception\Extension;
use Symfony\Component\Process\Process;

use function fclose;
use function fsockopen;
use function microtime;
use function sprintf;
use function usleep;

final class WebServerExtension extends Extension
{
    protected array $config = [
        'host' => '127.0.0.1',
        'port' => 8081,
        'startupTimeout' => 5.0,
    ];

    protected static array $events = [
        Events::SUITE_BEFORE => 'startServer',
        Events::SUITE_AFTER => 'stopServer',
    ];

    private ?Process $process = null;

    public function startServer(): void
    {
        $host = (string) $this->config['host'];
        $port = (int) $this->config['port'];
        $rootDirectory = $this->getRootDir();
        $publicDirectory = $rootDirectory . DIRECTORY_SEPARATOR . 'public';

        $this->process = new Process(
            [PHP_BINARY, '-S', "$host:$port", '-t', $publicDirectory, $publicDirectory . DIRECTORY_SEPARATOR . 'index.php'],
            $rootDirectory,
            ['APP_ENV' => 'test'],
            null,
            null,
        );
        $this->process->start();

        $deadline = microtime(true) + (float) $this->config['startupTimeout'];

        while (microtime(true) < $deadline) {
            if (!$this->process->isRunning()) {
                throw new ExtensionException($this, $this->process->getErrorOutput());
            }

            $socket = @fsockopen($host, $port, $errorCode, $errorMessage, 0.1);

            if ($socket !== false) {
                fclose($socket);
                return;
            }

            usleep(100_000);
        }

        $this->stopServer();

        throw new ExtensionException($this, sprintf('Web server did not start on %s:%d.', $host, $port));
    }

    public function stopServer(): void
    {
        if ($this->process?->isRunning()) {
            $this->process->stop(3);
        }

        $this->process = null;
    }

    public function __destruct()
    {
        $this->stopServer();
    }
}
