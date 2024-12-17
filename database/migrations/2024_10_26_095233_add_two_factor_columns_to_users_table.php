<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Laravel\Fortify\Fortify;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'two_factor_secret')) {
                $table->text('two_factor_secret')->nullable()->after('password');
            }
            if (!Schema::hasColumn('users', 'two_factor_recovery_codes')) {
                $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            }
        });
    }
    
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'two_factor_secret')) {
                $table->dropColumn('two_factor_secret');
            }
            if (Schema::hasColumn('users', 'two_factor_recovery_codes')) {
                $table->dropColumn('two_factor_recovery_codes');
            }
        });
    }
};
