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
        Schema::create('pictures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_villa')->nullable()->constrained('villas')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('id_expense')->nullable()->constrained('expenses')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('id_income')->nullable()->constrained('incomes')->onUpdate('cascade')->onDelete('cascade');
            $table->string('generated_name');
            $table->string('title')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pictures');
    }
};
