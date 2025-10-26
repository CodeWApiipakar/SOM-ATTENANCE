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
        Schema::create('leaves', function (Blueprint $table) {
            $table->id();
            $table->string("emp_code");
            $table->date("start_date");
            $table->date("end_date");
            $table->integer("diff");
            $table->boolean("returned")->default(0);
            $table->date("return_date");
            $table->string("lave_type");
            $table->text("reason");
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
        Schema::dropIfExists('leaves');
    }
};
