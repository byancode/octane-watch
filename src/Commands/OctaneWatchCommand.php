<?php

namespace Byancode\OctaneWatch\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class OctaneWatchCommand extends Command
{
    public $signature = 'octane:watch
        {--server= : The server that should be used to serve the application}
        {--host=127.0.0.1 : The IP address the server should bind to [default: "127.0.0.1"]}
        {--port=8000 : The port the server should be available on [default: "8000"]}
        {--rpc-port= : The RPC port the server should be available on}
        {--workers= : The number of workers that should be available to handle requests [default: "auto"]}
        {--task-workers= : The number of task workers that should be available to handle tasks [default: "auto"]}
        {--max-requests= : The number of requests to process before reloading the server [default: "500"]}
        {--rr-config= : The path to the RoadRunner .rr.yaml file}
        {--env= : The environment the command should run under}
    ';

    public $description = 'Alternative watch command for Laravel Octane';

    public function handle(): int
    {
        $process = new Process([
            'php',
            base_path('artisan'),
            'octane:start',
            ...$this->optionsToArray(),
        ]);

        $files = $this->files();
        $count = count($files);
        $current_hash = $this->watch($files);

        $process->start([$this, 'buffer']);
        $this->comment("Watching $count files...");
        $this->line('');
        $this->info('🚀 Server running...');
        $this->showUrlServer();

        while (true) {
            usleep(500);

            if ($current_hash != ($new_hash = $this->watch())) {
                $this->info('Change detected. 🚀 Restarting process...');

                $process->stop();

                $current_hash = $new_hash;

                $process->start([$this, 'buffer']);
                $this->showUrlServer();

                continue;
            }
        }

        return self::SUCCESS;
    }

    public function showUrlServer()
    {
        $port = $this->option('port');
        $host = $this->option('host');

        $this->line('');
        $this->line("Local: http://$host:$port");
        $this->line('');
        $this->comment('Press Ctrl+C to stop the server');
    }

    public function buffer($type, $buffer)
    {
        if ($type === Process::ERR) {
            $this->error($buffer);
        }
    }

    public function watch(array $files = null): string
    {
        $files ??= $this->files();
        sort($files);
        $watch = array_map([$this, 'hash'], $files);

        return md5(implode('', $watch));
    }

    public function hash(string $pathname): string
    {
        if (is_file($pathname) === false) {
            return md5($pathname);
        }

        return md5($pathname.sha1_file($pathname));
    }

    public function optionsToArray(): array
    {
        $options = [];
        foreach ($this->options() as $key => $value) {
            if (is_bool($value)) {
                if ($value === true) {
                    $options[] = "--$key";
                }
            } elseif (empty($value) === false) {
                if ($this->checkIsEmptyStringOrHasSpace($value)) {
                    $options[] = "--$key=\"$value\"";
                } else {
                    $options[] = "--$key=$value";
                }
            }
        }

        return $options;
    }

    public function checkIsEmptyStringOrHasSpace($value): bool
    {
        return empty($value) === true || strpos($value, ' ') !== false;
    }

    public function files(): array
    {
        $directory = new \RecursiveDirectoryIterator(base_path());
        $filter = new RecursiveFilterIteratorCustom($directory);
        $iterator = new \RecursiveIteratorIterator($filter);

        return array_map(function ($fileInfo) {
            return $fileInfo->getPathname();
        }, iterator_to_array($iterator));
    }
}

class RecursiveFilterIteratorCustom extends \RecursiveFilterIterator
{
    public function accept(): bool
    {
        $filename = $this->current()->getFilename();
        if ($this->current()->isFile() === false) {
            return ! in_array($filename, ['.git', 'vendor', '.', '..', '.phan', 'storage']);
        }

        return preg_match('/\.(php|[jt]sx?|css)$/', $filename) !== true;
    }
}
