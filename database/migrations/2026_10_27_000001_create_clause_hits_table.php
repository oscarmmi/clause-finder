<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clause_hits', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('agreement_id')->constrained()->cascadeOnDelete();
            $table->string('query', 200);
            $table->text('snippet');
            $table->float('score');
            $table->string('page_label');
            $table->timestamps();

            $table->index(['agreement_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clause_hits');
    }
};
