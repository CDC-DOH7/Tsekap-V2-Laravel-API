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
        Schema::create('push_notifications', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('origin_facility_id')->index();
            $table->unsignedInteger('destination_facility_id')->index();
            $table->unsignedInteger('sent_by_user_id')->index();
            $table->unsignedInteger('sent_to_user_id')->nullable()->index();
            $table->string('title');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->foreign('sent_by_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->foreign('sent_to_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
            $table->foreign('origin_facility_id')
                ->references('id')
                ->on('facilities')
                ->onDelete('cascade');
            $table->foreign('destination_facility_id')
                ->references('id')
                ->on('facilities')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('push_notifications');
    }
};
