<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBalneariosTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('balnearios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_completo');
            $table->string('nombre');
            $table->integer('capacidad');
            $table->integer('aforo_actual');
            $table->time('horario_apertura');
            $table->time('horario_cierre');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('balnearios');
    }
};
