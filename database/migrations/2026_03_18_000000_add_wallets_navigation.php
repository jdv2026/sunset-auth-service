<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('navigations')->upsert(
            [
                ['logo' => 'mat:account_balance_wallet', 'name' => 'Wallets', 'link' => '/dashboard/budget/wallets', 'header' => 'Budget', 'created_at' => $now, 'updated_at' => $now],
            ],
            ['link'],
            ['logo', 'name', 'header', 'updated_at']
        );
    }

    public function down(): void
    {
        DB::table('navigations')->where('link', '/dashboard/budget/wallets')->delete();
    }
};
