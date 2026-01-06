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
        Schema::table('milk_batches', function (Blueprint $table) {
            // Destination factory/facility
            $table->foreignUuid('destination_farm_id')->nullable()->after('farm_id')
                ->constrained('farms')->onDelete('set null');

            // Courier/Driver information
            $table->foreignUuid('courier_user_id')->nullable()->after('collected_by_user_id')
                ->constrained('users')->onDelete('set null');
            $table->string('courier_name', 100)->nullable()->after('courier_user_id')
                ->comment('Name if courier is not a system user');
            $table->string('courier_phone', 20)->nullable()->after('courier_name');
            $table->string('vehicle_number', 50)->nullable()->after('courier_phone')
                ->comment('Vehicle plate number');

            // Transportation tracking
            $table->string('transport_status', 30)->nullable()->after('status')
                ->comment('pending, dispatched, in_transit, delivered, returned');
            $table->timestamp('dispatched_at')->nullable()->after('collected_at')
                ->comment('When batch was sent from origin');
            $table->timestamp('delivered_at')->nullable()->after('dispatched_at')
                ->comment('When batch arrived at destination');
            $table->timestamp('expected_delivery_at')->nullable()->after('delivered_at')
                ->comment('Expected delivery time');

            // Transport documentation
            $table->string('tracking_number', 100)->nullable()->after('batch_code')
                ->comment('Unique transport tracking number');
            $table->json('transport_photos')->nullable()->after('transport_notes')
                ->comment('Photos during transport (pickup, delivery)');
            $table->text('delivery_notes')->nullable()->after('transport_photos')
                ->comment('Notes upon delivery');

            // Add index for better query performance
            $table->index('destination_farm_id');
            $table->index('courier_user_id');
            $table->index('transport_status');
            $table->index('tracking_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('milk_batches', function (Blueprint $table) {
            $table->dropForeign(['destination_farm_id']);
            $table->dropForeign(['courier_user_id']);

            $table->dropIndex(['destination_farm_id']);
            $table->dropIndex(['courier_user_id']);
            $table->dropIndex(['transport_status']);
            $table->dropIndex(['tracking_number']);

            $table->dropColumn([
                'destination_farm_id',
                'courier_user_id',
                'courier_name',
                'courier_phone',
                'vehicle_number',
                'transport_status',
                'dispatched_at',
                'delivered_at',
                'expected_delivery_at',
                'tracking_number',
                'transport_photos',
                'delivery_notes',
            ]);
        });
    }
};
