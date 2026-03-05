<?php

namespace App\Console\Commands;

use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DeleteOldNotifications extends Command
{

    protected $signature = 'payments:delete-old-notifications';

    protected $description = 'Check all 7 days old notifications and delete them';

    public function handle() {
		Log::info('Deleting 7 days old notifications.');

        $deletedCount = Notification::where('created_at', '<', Carbon::now()->subDays(7))
            ->delete();

        Log::info("Deleted {$deletedCount} notifications older than 7 days.");

        $this->info("Deleted {$deletedCount} notifications.");
    }

}
