<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();

            $table->string('label');
            $table->string('icon');
            $table->string('color_class');
            $table->string('created_by');
            $table->string('user_id')->nullable();
            $table->string('notify_id');
            $table->boolean('read')->default(false);

			$table->timestamps(); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
