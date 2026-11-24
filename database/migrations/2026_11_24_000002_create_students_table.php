<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('student_number', 50)->unique(); // the school-issued Student ID
            $table->string('full_name', 150)->index();
            $table->string('course', 100);
            $table->string('year_level', 20);               // free text, e.g. "1st Year"
            $table->string('section', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
