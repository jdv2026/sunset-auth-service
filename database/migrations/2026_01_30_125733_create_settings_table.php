<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();

            $table->boolean('isGeneralSetting')->default(false);

            $table->string('theme_name');
            $table->string('theme_className');

            $table->enum('orientation', ['left', 'right']);

            $table->boolean('toolbar')->default(false);
            $table->boolean('footer')->default(false);
            $table->boolean('footer_fixed')->default(false);
            $table->enum('isDarkMode', ['dark', 'light'])->default('light');

            $table->foreignId('created_by')
				->constrained('users')
				->onDelete('cascade');

			$table->foreignId('updated_by')
				->constrained('users')
				->onDelete('cascade');

			$table->timestamps(); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
