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
        Schema::create('overtimes', function (Blueprint $table) {
            $table->id();
            $table->string("emp_code");
            $table->date("start_date");
            $table->date("end_date");
            $table->decimal("amount");
            $table->boolean("approved")->default(0);
            $table->text("reason");
            $table->integer("total_time");
            $table->string("userid");
            $table->unsignedBigInteger("company_id");
            $table->foreign("company_id")->references("id")->on('company');
            $table->boolean("deleted")->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('overtimes');
    }
};
