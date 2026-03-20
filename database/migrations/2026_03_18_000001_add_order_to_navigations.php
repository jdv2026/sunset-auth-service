<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('navigations', function (Blueprint $table) {
            $table->unsignedInteger('order')->default(0)->after('header');
        });

        $orders = [
            '/dashboard/home'                => 1,
            '/dashboard/budget/overview'     => 2,
            '/dashboard/budget/transactions' => 3,
            '/dashboard/budget/categories'   => 4,
            '/dashboard/budget/reports'      => 5,
            '/dashboard/budget/wallets'      => 6,
            '/dashboard/budget/goals'        => 7,
            '/dashboard/budget/bills'        => 8,
        ];

        foreach ($orders as $link => $order) {
            DB::table('navigations')->where('link', $link)->update(['order' => $order]);
        }
    }

    public function down(): void
    {
        Schema::table('navigations', function (Blueprint $table) {
            $table->dropColumn('order');
        });
    }
};
