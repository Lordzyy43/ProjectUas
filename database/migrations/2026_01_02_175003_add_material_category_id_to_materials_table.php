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
        Schema::table('materials', function (Blueprint $table) {
            $table->foreignId('material_category_id')
                ->nullable()
                ->after('course_id')
                ->constrained('material_categories')
                ->nullOnDelete();
        });

    }

    /**
     * Reverse the migrations.
     */
        public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->dropForeign(['material_category_id']);
            $table->dropColumn('material_category_id');
        });
    }

};

