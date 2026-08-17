<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instructor_profiles', function (Blueprint $table) {
            $table->id();
            $table->uuid('reference')->unique();
            $table->string('submitted_by_name');
            $table->string('submitted_by_email');
            $table->string('instructor_name');
            $table->string('agency');
            $table->string('instructor_email')->nullable();
            $table->string('instructor_phone')->nullable();
            $table->string('county');
            $table->string('review_status')->default('pending');
            $table->text('review_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['county', 'review_status']);
            $table->index('instructor_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instructor_profiles');
    }
};
