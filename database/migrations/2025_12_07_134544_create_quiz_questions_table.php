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
        Schema::create('quiz_questions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('quiz_id')
            ->constrained('quizzes')
            ->cascadeOnDelete();

        $table->text('question_text');
        $table->string('option_a');
        $table->string('option_b');
        $table->string('option_c')->nullable();
        $table->string('option_d')->nullable();
        $table->enum('correct_answer', ['a','b','c','d']);
        $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_questions');
    }
};
