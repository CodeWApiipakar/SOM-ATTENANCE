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
        Schema::create('report_params', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("label");
            $table->string("type");
            $table->string("placeholder");
            $table->unsignedBigInteger("report_id");
            $table->foreign("report_id")->references("id")->on("reports");
            $table->string("valueType");
            $table->string("values");
            $table->integer("parentId");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_params');
    }
};
