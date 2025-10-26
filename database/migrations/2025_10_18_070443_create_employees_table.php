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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('enroll_id')->unique(); // device user id (PIN/UID)
            $table->string('emp_code')->nullable()->index(); // your HR code
            $table->string('name')->nullable();
            $table->string("phone");
            $table->date("dob")->nullable();;
            $table->string("pob");
            $table->unsignedBigInteger("companyId")->nullable();
            $table->unsignedBigInteger("departmentId")->nullable();;
            $table->unsignedBigInteger("sectionId")->nullable();;
            $table->foreign('companyId')
                ->references('id')->on('company')
                ->nullOnDelete();           // if a company is deleted, keep employee and null the FK

            $table->foreign('departmentId')
                ->references('id')->on('department')
                ->nullOnDelete();           // same idea for department

            $table->foreign('sectionId')
                ->references('id')->on('section')
                ->nullOnDelete();
            $table->bigInteger("account");
            $table->string("jop_title");
            $table->boolean("status");
            $table->decimal("salary");
            $table->decimal("bonus");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
