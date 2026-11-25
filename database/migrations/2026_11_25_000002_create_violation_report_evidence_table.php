<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('violation_report_evidence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('violation_report_id')
                ->constrained('violation_reports')
                ->cascadeOnDelete();
            $table->string('path');
            $table->string('original_name', 200);
            $table->timestamps();
            $table->index('violation_report_id');
        });

        DB::table('violation_reports')
            ->whereNotNull('evidence_path')
            ->orderBy('id')
            ->eachById(function (object $report) {
                DB::table('violation_report_evidence')->insert([
                    'violation_report_id' => $report->id,
                    'path' => $report->evidence_path,
                    'original_name' => $report->evidence_original_name ?? basename($report->evidence_path),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('violation_report_evidence');
    }
};
