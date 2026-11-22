<?php

namespace App\Console\Commands;

use App\Models\DailyVerse;
use Illuminate\Console\Command;

class CleanOldVerses extends Command
{
    protected $signature = 'umbral:clean-old-verses';

    protected $description = 'Purge daily_verses older than 90 days';

    public function handle(): int
    {
        $cutoff = now()->subDays(90)->toDateString();

        $deleted = DailyVerse::query()
            ->whereDate('shown_at', '<', $cutoff)
            ->delete();

        $this->info("Deleted {$deleted} daily verse(s) older than 90 days.");

        return self::SUCCESS;
    }
}
