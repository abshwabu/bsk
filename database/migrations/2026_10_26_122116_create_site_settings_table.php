<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_name')->nullable();
            $table->string('tagline')->nullable();
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
            $table->string('primary_color')->nullable();
            $table->string('accent_color')->nullable();
            $table->string('font_heading')->nullable();
            $table->string('font_body')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('whatsapp')->nullable();
            $table->json('social_links')->nullable();
            $table->text('footer_text')->nullable();
            $table->string('analytics_id')->nullable();
            $table->text('telegram_bot_token')->nullable();
            $table->string('telegram_chat_id')->nullable();
            $table->json('enabled_modules')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
