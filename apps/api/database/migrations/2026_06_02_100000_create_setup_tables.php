<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('luma_installation', function (Blueprint $table): void {
            $table->id();
            $table->timestamp('completed_at')->nullable();
            $table->string('version', 32)->nullable();
            $table->timestamps();
        });

        Schema::create('setup_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('step');
            $table->string('status');
            $table->text('message');
            $table->json('context')->nullable();
            $table->timestamps();

            $table->index(['step', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setup_logs');
        Schema::dropIfExists('luma_installation');
    }
};
