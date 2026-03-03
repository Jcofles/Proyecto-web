<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CleanDeletedUsers extends Command
{
    protected $signature = 'users:clean-deleted';
    protected $description = 'Eliminar permanentemente usuarios que fueron eliminados hace más de 30 días';

    public function handle()
    {
        $count = User::onlyTrashed()
            ->where('deleted_at', '<', now()->subDays(30))
            ->forceDelete();

        $this->info("Se eliminaron permanentemente {$count} usuarios.");
        return 0;
    }
}
