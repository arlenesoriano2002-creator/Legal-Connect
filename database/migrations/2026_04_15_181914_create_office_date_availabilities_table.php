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
        Schema::create('office_date_availabilities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('law_office_id');
            $table->date('date');
            $table->string('color')->default('green'); // green, yellow, red
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('law_office_id')->references('id')->on('law_offices')->onDelete('cascade');
            $table->unique(['law_office_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('office_date_availabilities');
    }
};
