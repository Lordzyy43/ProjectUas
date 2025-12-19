<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_quiz_results', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('quiz_id')
                ->constrained('quizzes')
                ->cascadeOnDelete();

            $table->integer('score');
            $table->integer('correct_count');
            $table->integer('total_questions');

            $table->timestamps();

            $table->unique(['user_id', 'quiz_id']);
        });


    }

    public function down(): void
    {
        Schema::dropIfExists('user_quiz_results');
    }
};
