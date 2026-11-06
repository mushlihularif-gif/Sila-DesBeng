<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ambulance routes, catalog entries and booking entry points are retired
        // in application code. Keep these rows: deleting either parent cascades
        // into historical bookings, including payment/financial records.
        // Service switches are disabled by disable_retired_region_services.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rolling back must not re-enable a retired service.
    }
};
