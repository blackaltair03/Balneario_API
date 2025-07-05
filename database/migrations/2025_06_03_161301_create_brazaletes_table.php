<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBrazaletesTable extends Migration
{
    public function up(): void
    {
        Schema::create('brazaletes', function (Blueprint $table) {
            $table->id();
            $table->enum('estado', ['activo', 'inactivo', 'perdido'])->default('activo');
            $table->string('codigo_qr')->unique();
            $table->string('status')->default('pendiente');
            $table->unsignedBigInteger('evento_id');
            $table->unsignedBigInteger('balneario_id');
            $table->unsignedBigInteger('checador_id')->nullable();
            $table->timestamp('fecha_verificacion')->nullable();
            $table->timestamps();
        });

        Schema::table('brazaletes', function (Blueprint $table) {
            //Relacion de los eventos
            $table->foreign('evento_id')
            ->references('id')
            ->on('eventos')
            ->onDelete('cascade');

            //Relacion de los balnearios 
            $table->foreign('balneario_id')
            ->references('id')
            ->on('balnearios')
            ->onDelete('cascade');

            //Relacion de los usuarios
            $table->foreign('checador_id')
            ->references('id')
            ->on('users')
            ->onDelete('set null'); //Si se elimina el usuario, se mantiene el brazalte 
        });
    }

    public function down(): void
    {
        //Primero la eliminacion de las claves foraneas 

        Schema::table('brazaletes', function (Blueprint $table) {
            $table->dropForeign(['evento_id']);
            $table->dropForeign(['balneario_id']);
            $table->dropForeign(['checador_id']);
        });

        //Luego eliminacion de la tabla 
        Schema::dropIfExists('brazaletes');
    }
};

