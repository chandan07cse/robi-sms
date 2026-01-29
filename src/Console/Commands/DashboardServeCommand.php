<?php

namespace AdaReach\Sms\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class DashboardServeCommand extends Command
{
    protected $signature = 'adarearch:serve {--port=8090}';
    protected $description = 'Start the SMS Dashboard server';

    public function handle()
    {
        $port = $this->option('port');
        
        $this->info("Starting SMS Dashboard on port {$port}...");
        $this->info("Dashboard URL: http://localhost:{$port}/" . config('adarearch.dashboard.path', 'sms-dashboard'));
        $this->line('');
        $this->comment('Press Ctrl+C to stop the server');

        $process = new Process([
            PHP_BINARY,
            'artisan',
            'serve',
            '--port=' . $port,
            '--host=0.0.0.0'
        ]);

        $process->setTimeout(null);
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });

        return Command::SUCCESS;
    }
}
