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
       Schema::table('users', function (Blueprint $table) {

            // Role & Status
           ;

            // Customer Details
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->date('dob')->nullable();

          
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
              
                'gender',
                'dob',
               
            ]);
        });
    }
};
