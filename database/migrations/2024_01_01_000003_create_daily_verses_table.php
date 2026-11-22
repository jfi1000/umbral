<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_verses', function (Blueprint $table) {
            $table->id();
            $table->string('visitor_hash', 64);
            $table->string('verse_reference', 100);
            $table->text('verse_text');
            $table->string('verse_book', 80);
            $table->unsignedInteger('verse_chapter');
            $table->unsignedInteger('verse_number');
            $table->date('shown_at');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['visitor_hash', 'shown_at']);
            $table->index('shown_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_verses');
    }
};
