<?php

namespace AdaReach\Sms\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class GeneratePasswordCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'adarearch:password {password}';

    /**
     * The console command description.
     */
    protected $description = 'Generate a hashed password for AdaReach dashboard authentication';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $password = $this->argument('password');
        $hashed = Hash::make($password);

        $this->info('Hashed password generated successfully!');
        $this->line('');
        $this->line('Add this to your .env file:');
        $this->line('');
        $this->warn("ADAREARCH_DASHBOARD_PASSWORD={$hashed}");
        $this->line('');
        $this->comment('Or update your config/adarearch.php file directly.');

        return 0;
    }
}
