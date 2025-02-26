<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('deleted_files', function (Blueprint $table) {
            $table->id();
            $table->string('file_name'); // Nombre del archivo
            $table->string('file_path'); // Ruta del archivo
            $table->unsignedBigInteger('requirement_id'); // ID del requisito
            $table->string('evidence'); // Evidencia
            $table->date('compliance_deadline'); // Fecha límite de cumplimiento
            $table->string('user_name'); // Nombre del usuario que subió el archivo
            $table->string('user_position'); // Puesto del usuario que subió el archivo
            $table->unsignedBigInteger('user_id'); // ID del usuario que subió el archivo
            $table->unsignedBigInteger('deleted_by'); // ID del usuario que eliminó el archivo
            $table->timestamps(); // Fechas de creación y actualización
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('deleted_files');
    }
};
