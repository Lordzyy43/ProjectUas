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
         Schema::create('user_progress', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
        $table->foreignId('material_id')->nullable()->constrained('materials')->onDelete('cascade');
        $table->foreignId('quiz_id')->nullable()->constrained('quizzes')->onDelete('cascade');
        $table->integer('score')->nullable();
        $table->integer('progress_percent')->default(0);
        $table->integer('weekly_target')->nullable();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_progress');
    }
};
