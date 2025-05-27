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
        Schema::create('profiling_total_population', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('muncity_id')->index();
            $table->unsignedInteger('total_population')->default(0);
            $table->timestamps();

            $table->foreign('muncity_id')
                ->references('id')
                ->on('muncity')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiling_total_population');
    }
};
