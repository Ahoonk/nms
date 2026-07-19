<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zabbix_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('base_url');
            $table->string('username')->nullable();
            $table->text('password')->nullable();
            $table->text('api_token')->nullable();
            $table->unsignedSmallInteger('timeout_seconds')->default(30);
            $table->boolean('is_default')->default(false);
            $table->string('status')->default('active')->index();
            $table->timestamps();

            $table->index(['company_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zabbix_connections');
    }
};
