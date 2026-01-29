<?php

namespace AdaReach\Sms\Console\Commands;

use Illuminate\Console\Command;
use AdaReach\Sms\Storage\SmsRepository;

class CleanupCommand extends Command
{
    protected $signature = 'adarearch:cleanup';
    protected $description = 'Clean up old SMS records based on retention policy';

    protected SmsRepository $repository;

    public function __construct(SmsRepository $repository)
    {
        parent::__construct();
        $this->repository = $repository;
    }

    public function handle()
    {
        $this->info('Cleaning up old SMS records...');
        
        $count = $this->repository->cleanup();
        
        $this->info("Cleaned up {$count} old records.");
        
        return Command::SUCCESS;
    }
}
