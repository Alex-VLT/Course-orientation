<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('VIK_DOSSARD', function (Blueprint $table) {
            $table->bigIncrements('DOSS_ID');
            $table->integer('COU_NUM')->index();
            $table->integer('DOSS_NUM');
            $table->integer('EQUIPE_NUM')->nullable();
            $table->boolean('DOSS_DISTRIBUE')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('VIK_DOSSARD');
    }
};
