<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('VIK_COURSE', function (Blueprint $table) {
            if (!Schema::hasColumn('VIK_COURSE', 'COU_VALIDE')) {
                $table->boolean('COU_VALIDE')->default(false)->after('COU_REDUC_LICENCIE');
            }
        });
    }

    public function down(): void
    {
        Schema::table('VIK_COURSE', function (Blueprint $table) {
            if (Schema::hasColumn('VIK_COURSE', 'COU_VALIDE')) {
                $table->dropColumn('COU_VALIDE');
            }
        });
    }
};
