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
        Schema::create('servers', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('name');
            $table->string('description');
            $table->integer('project');
            $table->integer('disk')->nullable();
            $table->integer('ram')->nullable();
            $table->integer('vcpu')->nullable();
            $table->integer('plan_id')->nullable();
            $table->string('plan_name')->nullable();
            $table->string('status')->nullable();
            $table->string('ips')->nullable();
            $table->integer('location')->nullable();
            $table->integer('os')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servers');
    }
};
