<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CleanExpiredTokensCommand extends Command
{
    protected $signature = 'auth:clean-tokens';
    protected $description = 'Clean expired active tokens, refresh tokens, and JTI blacklist from the database';

    public function handle()
    {
        $now = Carbon::now();

        $deletedRefresh = DB::table('refresh_tokens')
            ->where('expires_at', '<', $now)
            ->delete();

        $deletedActive = DB::table('active_tokens')
            ->where('expires_at', '<', $now)
            ->delete();

        $deletedBlacklist = DB::table('jti_blacklist')
            ->where('expires_at', '<', $now)
            ->delete();

        $this->info("Cleaned up expired tokens: {$deletedRefresh} refresh, {$deletedActive} active, {$deletedBlacklist} blacklist JTIs.");
    }
}
