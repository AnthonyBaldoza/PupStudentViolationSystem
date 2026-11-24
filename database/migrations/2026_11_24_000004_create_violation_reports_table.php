<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('violation_reports', function (Blueprint $table) {
            $table->id();

            // Who / what the report is about
            $table->foreignId('student_id')->constrained('students')->restrictOnDelete();
            $table->foreignId('violation_category_id')->constrained('violation_categories')->restrictOnDelete();

            // The officer who submitted it
            $table->foreignId('reported_by')->constrained('users')->restrictOnDelete();

            // Incident details
            $table->dateTime('incident_at');
            $table->string('location', 255);
            $table->text('description');

            // Optional single evidence file, stored on the private disk (never public)
            $table->string('evidence_path')->nullable();
            $table->string('evidence_original_name')->nullable();

            // Review workflow
            $table->string('status', 20)->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_remarks')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index(['student_id', 'status']);
            $table->index('reported_by');
        });

        DB::statement("ALTER TABLE violation_reports ADD CONSTRAINT violation_reports_status_check CHECK (status IN ('pending', 'approved', 'rejected'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('violation_reports');
    }
};
