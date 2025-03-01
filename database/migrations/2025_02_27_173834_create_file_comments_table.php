<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('file_comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('archivo_id'); 
            $table->unsignedBigInteger('user_id'); 
            $table->text('comment');
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('file_comments');
    }
};


