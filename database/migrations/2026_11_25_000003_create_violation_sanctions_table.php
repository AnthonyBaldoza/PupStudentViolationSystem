<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('violation_sanctions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('violation_report_id')
                ->unique()
                ->constrained('violation_reports')
                ->cascadeOnDelete();
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->date('due_at')->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'due_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('violation_sanctions');
    }
};
