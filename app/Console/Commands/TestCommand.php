<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestCommand extends Command
{
    protected $signature = 'test {--filter=}';

    protected $description = 'Run the tests';

    public function handle()
    {
        $filter = $this->option('filter');
        $command = 'vendor/bin/phpunit';

        if ($filter) {
            $command .= ' --filter ' . escapeshellarg($filter);
        }

        passthru($command);
    }
}
