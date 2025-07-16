<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feature_flags_history', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->boolean('enabled')->default(false);
            $table->string('event')->nullable();  // 'created', 'updated', 'deleted'
            $table->json('metadata')->nullable();
            $table->json('environments')->nullable();
            $table->timestamp('changed_at')->useCurrent();
            $table->string('changed_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_flags_history');
    }
};
