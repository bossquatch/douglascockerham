<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instructor_capabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instructor_profile_id')->constrained()->cascadeOnDelete();
            $table->string('course_code');
            $table->string('course_title');
            $table->string('flex_status');
            $table->date('flex_expiration_date')->nullable();
            $table->string('delivery_role');
            $table->string('delivery_mode');
            $table->string('willing_to_travel');
            $table->string('availability');
            $table->unsignedInteger('prior_deliveries')->nullable();
            $table->date('last_taught_at')->nullable();
            $table->string('regional_priority');
            $table->string('review_status')->default('pending');
            $table->text('notes')->nullable();
            $table->text('review_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['course_code', 'review_status']);
            $table->index(['flex_status', 'review_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instructor_capabilities');
    }
};
