<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('VIK_RAID')) {
            Schema::table('VIK_RAID', function (Blueprint $table) {
                if (!Schema::hasColumn('VIK_RAID', 'RAID_CONTACT_MAIL')) {
                    $table->string('RAID_CONTACT_MAIL')->nullable()->after('RAID_CONTACT');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('VIK_RAID')) {
            Schema::table('VIK_RAID', function (Blueprint $table) {
                if (Schema::hasColumn('VIK_RAID', 'RAID_CONTACT_MAIL')) {
                    $table->dropColumn('RAID_CONTACT_MAIL');
                }
            });
        }
    }
};
