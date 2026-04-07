<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('event_type', 100)->index();
            $table->timestamp('datetime')->useCurrent()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('page_url', 500)->nullable();
            $table->string('policy_version', 100)->default('1.0');
            $table->boolean('checkbox_value')->default(true);
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('email', 255)->nullable()->index();
            $table->string('phone', 20)->nullable()->index();
            $table->string('session_id', 255)->nullable()->index();
            $table->text('additional_data')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'event_type']);
            $table->index(['email', 'event_type']);
            $table->index(['datetime', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consents');
    }
};
