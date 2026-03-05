<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_logs', function (Blueprint $table) {
            $table->id();

            $table->string('action_type');
            $table->string('action_by');
            $table->string('action');

			$table->foreignId('user_id')
				->constrained('users')
				->onDelete('cascade');
            $table->foreignId('created_by')
				->constrained('users')
				->onDelete('cascade');
			$table->timestamps(); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_logs');
    }
};
