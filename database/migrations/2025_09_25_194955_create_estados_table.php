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
        Schema::create('estados', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique()->comment('Nombre del estado de México');
            $table->string('codigo_postal', 10)->nullable()->comment('Código postal principal del estado');
            $table->timestamps();

            $table->index('nombre', 'idx_estados_nombre');
            $table->index('created_at', 'idx_estados_created_at');
        });

        DB::statement("ALTER TABLE estados COMMENT = 'Catálogo de estados de México obtenidos de COPOMEX'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estados');
    }
};
