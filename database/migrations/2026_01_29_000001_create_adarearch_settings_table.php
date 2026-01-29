<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('adarearch_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, boolean, integer, json
            $table->text('description')->nullable();
            $table->boolean('encrypted')->default(false);
            $table->timestamps();
        });

        // Insert default settings
        DB::table('adarearch_settings')->insert([
            [
                'key' => 'api_username',
                'value' => config('adarearch.username'),
                'type' => 'string',
                'description' => 'AdaReach API Username',
                'encrypted' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'api_password',
                'value' => encrypt(config('adarearch.password')),
                'type' => 'string',
                'description' => 'AdaReach API Password',
                'encrypted' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'default_sender',
                'value' => config('adarearch.default_sender'),
                'type' => 'string',
                'description' => 'Default Sender ID',
                'encrypted' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'api_base_url',
                'value' => config('adarearch.base_url'),
                'type' => 'string',
                'description' => 'AdaReach API Base URL',
                'encrypted' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adarearch_settings');
    }
};
