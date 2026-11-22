<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seed_verses', function (Blueprint $table) {
            $table->id();
            $table->string('book', 80);
            $table->unsignedInteger('chapter');
            $table->unsignedInteger('verse');
            $table->string('reference', 100);
            $table->text('text');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seed_verses');
    }
};
