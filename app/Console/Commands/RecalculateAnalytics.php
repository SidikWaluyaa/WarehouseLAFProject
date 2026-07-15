<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AnalyticsService;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class RecalculateAnalytics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analytics:recalculate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate average sales, safety stock, minimum and maximum stocks for all products';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting recalculation of product analytics...');
        
        try {
            AnalyticsService::recalculateAll();
            $this->info('Product analytics recalculated successfully!');
            return SymfonyCommand::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to recalculate analytics: ' . $e->getMessage());
            return SymfonyCommand::FAILURE;
        }
    }
}
