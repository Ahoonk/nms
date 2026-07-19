<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('device_type')->index();
            $table->string('hostname')->index();
            $table->string('ip', 45)->index();
            $table->string('vendor')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('mac', 32)->nullable();
            $table->string('os')->nullable();
            $table->string('status')->default('unknown')->index();
            $table->string('zabbix_host_id')->nullable()->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
