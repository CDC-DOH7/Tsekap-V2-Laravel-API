<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            Schema::create('push_notifications', function (Blueprint $table) {
                $table->increments('id'); // [PRIMARY_KEY]
                $table->unsignedInteger('origin_facility_id')->nullable(); // [FOREIGN_KEY] origin_facility_id
                $table->unsignedInteger('destination_facility_id')->nullable(); // [FOREIGN_KEY] destination_facility_id
                $table->unsignedInteger('sent_by_user_id')->nullable(); // [FOREIGN_KEY] sent_by_user_id
                $table->unsignedInteger('sent_to_user_id')->nullable(); // [FOREIGN_KEY] sent_to_user_id
                $table->string('title');
                $table->text('message');
                $table->boolean('is_read')->default(false);
                $table->json('data')->nullable();
                $table->timestamps();

                $table->foreign('origin_facility_id')->references('id')->on('facilities')->onDelete('cascade'); // origin_facility_id
                $table->foreign('destination_facility_id')->references('id')->on('facilities')->onDelete('cascade'); // destination_facility_id
                $table->foreign('sent_by_user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('sent_to_user_id')->references('id')->on('users')->onDelete('cascade');
            });
        } catch (\Exception $e) {
            Log::error('Migration Error (create_push_notifications_table): ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // retain new migration
        try {
            Schema::table('push_notifications', function (Blueprint $table) {
                $table->dropForeign(['origin_facility_id']);
                $table->dropForeign(['destination_facility_id']);
                $table->dropForeign(['sent_by_user_id']);
                $table->dropForeign(['sent_to_user_id']);
            });

            Schema::dropIfExists('push_notifications');
        } catch (\Exception $e) {
            Log::error('Migration failed (Push Notifications Table - Down): ' . $e->getMessage());
            throw $e;
        }
    }
};
