<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Características técnicas: [{ "label": "...", "value": "..." }, ...]
            $table->json('specs')->nullable()->after('description');
            // Imágenes promocionales adicionales (URLs): ["https://...", ...]
            $table->json('gallery')->nullable()->after('image');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['specs', 'gallery']);
        });
    }
};
