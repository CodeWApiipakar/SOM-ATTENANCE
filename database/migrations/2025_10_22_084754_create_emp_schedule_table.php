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
        Schema::create('emp_schedule', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("time_table");
            $table->foreign("time_table")->references("id")->on('time_table');
            $table->unsignedBigInteger("shift");
            $table->foreign("shift")->references("id")->on('shift');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emp_schedule');
    }
};
