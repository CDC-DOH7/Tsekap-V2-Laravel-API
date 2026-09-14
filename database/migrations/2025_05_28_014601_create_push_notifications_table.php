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
                $table->unsignedInteger('facility_id'); // [FOREIGN_KEY] facility_id -> facilities.id
                $table->unsignedInteger('user_id'); // [FOREIGN_KEY] user_id -> users.id
                $table->string('title');
                $table->text('message');
                $table->boolean('is_read')->default(false);
                $table->json('data')->nullable();
                $table->timestamps();

                $table->foreign('facility_id')->references('id')->on('facilities')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
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
                // $table->dropForeign(['facility_id']);
                // $table->dropForeign(['user_id']);

                // Drop foreign keys for the new columns
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
