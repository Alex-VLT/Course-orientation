<?php

namespace Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait CreatesVikSchema
{
    protected function createVikSchema(): void
    {
        if (! Schema::hasTable('vik_inscrit')) {
            Schema::create('vik_inscrit', function (Blueprint $table) {
                $table->increments('INS_ID');
                $table->string('INS_NOM')->nullable();
                $table->string('INS_PRENOM')->nullable();
                $table->date('INS_NAISSANCE')->nullable();
                $table->string('INS_CODE_PO')->nullable();
                $table->string('INS_MAIL')->nullable();
                $table->string('INS_VILLE')->nullable();
                $table->string('INS_ADRESSE')->nullable();
                $table->string('INS_TEL')->nullable();
                $table->string('INS_MDP')->nullable();
                $table->string('INS_NUM_LICENCE')->nullable();
                $table->string('INS_NUM_PPS')->nullable();
                $table->integer('INS_IS_ADMIN')->default(0);
            });
        }

        if (! Schema::hasTable('vik_club')) {
            Schema::create('vik_club', function (Blueprint $table) {
                $table->integer('CLU_NUM')->primary();
                $table->string('CLU_NOM')->nullable();
                $table->integer('INS_ID')->nullable();
            });
        }

        if (! Schema::hasTable('vik_adherer')) {
            Schema::create('vik_adherer', function (Blueprint $table) {
                $table->integer('INS_ID');
                $table->integer('CLU_NUM');
            });
        }

        if (! Schema::hasTable('vik_raid')) {
            Schema::create('vik_raid', function (Blueprint $table) {
                $table->integer('RAID_NUM')->primary();
                $table->integer('CLU_NUM')->nullable();
                $table->integer('INS_ID')->nullable();
                $table->string('RAID_NOM')->nullable();
                $table->date('RAID_DATE_DEBUT_INSCRI')->nullable();
                $table->date('RAID_DATE_FIN_INSCRI')->nullable();
                $table->date('RAID_DATE_DEBUT')->nullable();
                $table->date('RAID_DATE_FIN')->nullable();
                $table->string('RAID_CONTACT')->nullable();
                $table->string('RAID_CONTACT_MAIL')->nullable();
                $table->string('RAID_LIEN_SITE_WEB')->nullable();
                $table->decimal('RAID_LATITUDE', 10, 6)->nullable();
                $table->decimal('RAID_LONGITUDE', 10, 6)->nullable();
                $table->string('RAID_ILLUSTRATION')->nullable();
            });
        }

        if (! Schema::hasTable('vik_course')) {
            Schema::create('vik_course', function (Blueprint $table) {
                $table->integer('COU_NUM')->primary();
                $table->integer('RAID_NUM')->nullable();
                $table->integer('INS_ID')->nullable();
                $table->integer('TYP_NUM')->nullable();
                $table->string('COU_NOM')->nullable();
                $table->dateTime('COU_DATE_DEPART')->nullable();
                $table->dateTime('COU_DATE_FIN')->nullable();
                $table->integer('COU_NB_EQU_MIN')->nullable();
                $table->integer('COU_NB_EQU_MAX')->nullable();
                $table->integer('COU_PART_PAR_EQU_MAX')->nullable();
            });
        }

        if (! Schema::hasTable('vik_equipe')) {
            Schema::create('vik_equipe', function (Blueprint $table) {
                $table->integer('EQU_NUM');
                $table->integer('COU_NUM');
                $table->integer('INS_ID')->nullable();
            });
        }

        if (! Schema::hasTable('vik_participer')) {
            Schema::create('vik_participer', function (Blueprint $table) {
                $table->integer('COU_NUM');
                $table->integer('INS_ID');
                $table->integer('EQU_NUM')->nullable();
            });
        }

        if (! Schema::hasTable('vik_tranche_age')) {
            Schema::create('vik_tranche_age', function (Blueprint $table) {
                $table->integer('TRA_ID')->primary();
                $table->integer('TRA_AGE_MIN')->nullable();
                $table->integer('TRA_AGE_MAX')->nullable();
            });
        }

        if (! Schema::hasTable('vik_accepter')) {
            Schema::create('vik_accepter', function (Blueprint $table) {
                $table->integer('COU_NUM');
                $table->integer('TRA_ID');
                $table->decimal('ACC_PRIX', 10, 2)->nullable();
            });
        }
    }
}
