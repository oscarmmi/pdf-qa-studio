<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_docs', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('filename')->unique();
            $table->longText('body');
            $table->timestamps();
        });

        Schema::create('doc_chunks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('library_doc_id')->constrained()->cascadeOnDelete();
            $table->longText('body');
            $table->unsignedInteger('position');
            $table->timestamps();
            $table->unique(['library_doc_id', 'position']);
        });

        Schema::create('qa_turns', function (Blueprint $table): void {
            $table->id();
            $table->text('question');
            $table->longText('answer');
            $table->json('retrieved_chunk_ids');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qa_turns');
        Schema::dropIfExists('doc_chunks');
        Schema::dropIfExists('library_docs');
    }
};
