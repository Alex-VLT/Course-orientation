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
        Schema::table('VIK_RAID', function (Blueprint $table) {
            if (!Schema::hasColumn('VIK_RAID', 'RAID_RESP_INS_ID')) {
                $table->integer('RAID_RESP_INS_ID')->nullable()->after('INS_ID');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('VIK_RAID', function (Blueprint $table) {
            if (Schema::hasColumn('VIK_RAID', 'RAID_RESP_INS_ID')) {
                $table->dropColumn('RAID_RESP_INS_ID');
            }
        });
    }
};
