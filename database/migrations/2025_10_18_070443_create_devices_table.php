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
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->string('serial_number')->unique();
            $table->string('ip_address')->nullable()->index();
            $table->string('lan_ip')->nullable();   // device’s Ethernet IP
            $table->unsignedSmallInteger('port')->default(80);
            $table->string('model')->nullable();
            $table->string('vendor')->default('zkteco');
            $table->string('push_token')->nullable(); // optional per-device token
            $table->boolean('is_active')->default(true);
            $table->string('mac')->nullable();         // MAC address
            $table->string('firmware')->nullable();       // FirmwareVersion/Build
            $table->string('pushver')->nullable();      // ADMS push version
            $table->string('device_type')->nullable();   // e.g., "middle east"
            $table->timestamp('last_seen_at')->nullable();
            $table->unsignedBigInteger("companyId")->nullable();
            $table->foreign('companyId')
                ->references('id')->on('company')
                ->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
