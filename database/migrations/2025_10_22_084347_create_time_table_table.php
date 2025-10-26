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
        Schema::create('time_table', function (Blueprint $table) {
            $table->id();
            $table->string("ttable");
            $table->time("in_time");
            $table->time("out_time");
            $table->time("in_start_time");
            $table->time("out_start_time");
            $table->time("in_end_time");
            $table->time("out_end_time");
            $table->boolean("in_required")->default(true);
            $table->boolean("out_required")->default(false);
            $table->boolean("deleted")->default(false);
            $table->unsignedBigInteger("company_id");
            $table->foreign("company_id")->references("id")->on('company');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_table');
    }
};
