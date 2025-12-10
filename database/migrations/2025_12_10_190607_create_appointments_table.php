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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained()->onDelete('set null');
            $table->string('first_name');
            $table->string('last_name');
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('duration')->nullable(); // délka v minutách (alternativa k end_time)
            $table->text('notes')->nullable();
            $table->integer('lane')->default(1); // pracovní místo (1 nebo 2)
            $table->integer('repeat_weeks')->default(0); // počet týdnů opakování
            $table->foreignId('parent_appointment_id')->nullable()->constrained('appointments')->onDelete('cascade');
            $table->enum('status', ['scheduled', 'completed', 'cancelled'])->default('scheduled');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
