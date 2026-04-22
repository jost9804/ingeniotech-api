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
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('client_name', 120);
            $table->string('client_phone', 20);
            $table->enum('device_type', ['computador', 'celular', 'camara', 'otro']);
            $table->text('problem_description');
            $table->enum('status', ['recibido', 'en_diagnostico', 'en_reparacion', 'listo', 'entregado']);
            $table->foreignId('assigned_to')->constrained('users')->onDelete('restrict');
            $table->unsignedTinyInteger('progress')->default(0);
            $table->decimal('price', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
