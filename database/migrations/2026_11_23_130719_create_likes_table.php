<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('likes', function (Blueprint $table) {
            $table->id();
    
            $table->foreignId('user_id')
                ->constrained()
                //se user for excluido, suas curtidas tambem serao
                ->cascadeOnDelete();
    
            $table->foreignId('chirp_id')
                ->constrained()
                //se chirp for excluido, as curtidas tbm serao
                ->cascadeOnDelete();


    //a combinação de usuário e Chirp não pode se repetir.
            $table->unique(['user_id', 'chirp_id']);
    
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('likes');
    }
};
