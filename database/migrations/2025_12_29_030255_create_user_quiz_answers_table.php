<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_quiz_answers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('quiz_id')
                ->constrained('quizzes')
                ->cascadeOnDelete();

            $table->foreignId('question_id')
                ->constrained('quiz_questions')
                ->cascadeOnDelete();

            // 🔑 PENENTU ATTEMPT
            $table->unsignedInteger('attempt');

            $table->enum('selected_answer', ['a','b','c','d']);
            $table->boolean('is_correct');

            $table->timestamps();

            // ✅ unik per ATTEMPT
            $table->unique([
                'user_id',
                'quiz_id',
                'question_id',
                'attempt'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_quiz_answers');
    }
};
