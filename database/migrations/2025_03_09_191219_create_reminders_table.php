<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; 
class CreateRemindersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Crear la tabla
        Schema::create('reminders', function (Blueprint $table) {
            $table->id(); 
            $table->integer('reminder_days'); 
            $table->string('reminder_type'); 
            $table->timestamps(); 
        });


        DB::table('reminders')->insert([
            [
                'reminder_days' => 30,
                'reminder_type' => 'primera_notificacion',
                'created_at' => now(), 
                'updated_at' => now(), 
            ],
            [
                'reminder_days' => 15,
                'reminder_type' => 'segunda_notificacion',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'reminder_days' => 5,
                'reminder_type' => 'tercera_notificacion',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reminders'); 
    }
}