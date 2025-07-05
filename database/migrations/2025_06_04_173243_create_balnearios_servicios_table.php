<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBalneariosServiciosTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('balnearios_servicios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('balneario_id')->constrained()->onDelete('cascade');
            $table->foreignId('servicio_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('balnearios_servicios');
    }
};
