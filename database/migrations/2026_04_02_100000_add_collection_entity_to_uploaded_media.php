<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('uploaded_media', function (Blueprint $table) {
            $table->string('collection')->nullable()->after('size');
            $table->string('entity_type')->nullable()->after('collection');
            $table->unsignedBigInteger('entity_id')->nullable()->after('entity_type');

            $table->index(['collection', 'entity_type', 'entity_id'], 'uploaded_media_collection_entity_idx');
        });
    }

    public function down(): void
    {
        Schema::table('uploaded_media', function (Blueprint $table) {
            $table->dropIndex('uploaded_media_collection_entity_idx');
            $table->dropColumn(['collection', 'entity_type', 'entity_id']);
        });
    }
};
