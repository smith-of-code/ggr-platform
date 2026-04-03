<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exception_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('exception_class');
            $table->text('message');
            $table->string('code', 50)->nullable();
            $table->text('file');
            $table->unsignedInteger('line');
            $table->longText('trace')->nullable();
            $table->text('url')->nullable();
            $table->string('method', 10)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('created_at');
            $table->index('exception_class');
            $table->index('status_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exception_logs');
    }
};
