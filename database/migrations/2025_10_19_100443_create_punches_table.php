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
        Schema::create('punches', function (Blueprint $table) {
            $table->id();

            // device_id — must be nullable because of nullOnDelete()
            $table->unsignedBigInteger('device_id')->nullable();
            $table->foreign('device_id')
                ->references('id')->on('devices');

            // employee_id — already nullable, fine
            $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();

            $table->string('enroll_id')->index();      // raw from device
            $table->string('verify_mode')->nullable(); // FP/FACE/CARD/PIN
            $table->string('io_mode')->nullable();     // IN/OUT/NA
            $table->timestamp('punch_time')->index();  // UTC time
            $table->string('work_code')->nullable();

            // Idempotency key to kill duplicates
            $table->string('source_uid')->unique();    // e.g., "SN:xxx|RID:yyy|TS:zzz"

            $table->json('raw_payload')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('punches');
    }
};
