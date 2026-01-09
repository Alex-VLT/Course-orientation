<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestDataSeeder extends Seeder
{
    /**
     * Seed the test database with production-like data
     * Data extracted from g1_db production database
     */
    public function run(): void
    {
        // Disable foreign key checks during seeding
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Clear existing data
        DB::table('vik_participer')->truncate();
        DB::table('vik_equipe')->truncate();
        DB::table('vik_course')->truncate();
        DB::table('vik_accepter')->truncate();
        DB::table('vik_raid')->truncate();
        DB::table('vik_adherer')->truncate();
        DB::table('vik_club')->truncate();
        DB::table('vik_inscrit')->truncate();
        DB::table('vik_type_course')->truncate();
        DB::table('vik_tranche_age')->truncate();

        // Insert type course
        DB::table('vik_type_course')->insert([
            ['TYP_NUM' => 1, 'TYP_LABEL' => 'Loisir'],
            ['TYP_NUM' => 2, 'TYP_LABEL' => 'Compétition'],
        ]);

        // Insert inscrit (users)
        $inscrits = [
            [
                'INS_ID' => 1, 'INS_NOM' => 'admin', 'INS_PRENOM' => 'admin',
                'INS_NAISSANCE' => '2000-01-01', 'INS_CODE_PO' => 10000,
                'INS_MAIL' => 'admin@gmail.com', 'INS_VILLE' => 'admin',
                'INS_ADRESSE' => '1 rue admin', 'INS_TEL' => '0600000000',
                'INS_NUM_LICENCE' => null, 'INS_MDP' => '$2y$12$dvYDbDoyxomjmsnsDDgkM.kzd0RrrShyyn5OENVfVyWJeg6EPZ6vi',
                'INS_IS_ADMIN' => 1
            ],
            [
                'INS_ID' => 6, 'INS_NOM' => 'DUMONT', 'INS_PRENOM' => 'Clara',
                'INS_NAISSANCE' => '1985-09-22', 'INS_CODE_PO' => 14123,
                'INS_MAIL' => 'c.dumont@email.fr', 'INS_VILLE' => 'IFS',
                'INS_ADRESSE' => '45 rue des plantes', 'INS_TEL' => '0698765432',
                'INS_NUM_LICENCE' => '25004567', 'INS_MDP' => '$2y$12$KVjEWzeRpq0ujQYpEy9y8.yX2lMSC03g9mducAF5Et3IKQX.tTcFa',
                'INS_IS_ADMIN' => 0
            ],
            [
                'INS_ID' => 21, 'INS_NOM' => 'Dupont', 'INS_PRENOM' => 'Claire',
                'INS_NAISSANCE' => '1992-05-14', 'INS_CODE_PO' => 77100,
                'INS_MAIL' => 'claire.dupont@test.fr', 'INS_VILLE' => 'Meaux',
                'INS_ADRESSE' => '12 rue des Pins', 'INS_TEL' => '0612457890',
                'INS_NUM_LICENCE' => '1204558', 'INS_MDP' => '$2y$12$gB6rualPb/pav.tTF8vNquTkscYNmnPi3W2lUd22BlS6Z0e7PO/Iq',
                'INS_IS_ADMIN' => 0
            ],
            [
                'INS_ID' => 22, 'INS_NOM' => 'Dorbec', 'INS_PRENOM' => 'Paul',
                'INS_NAISSANCE' => '1980-04-02', 'INS_CODE_PO' => 77000,
                'INS_MAIL' => 'paul.dorbec@unicaen.fr', 'INS_VILLE' => 'Melun',
                'INS_ADRESSE' => '22 rue des roses', 'INS_TEL' => '0743672311',
                'INS_NUM_LICENCE' => '23456789', 'INS_MDP' => '$2y$12$.GMNaQbPEKDCvrbK51nPpupkeqMoQ1dUvsyFfOsXYLkF1qNiDBtOe',
                'INS_IS_ADMIN' => 0
            ],
        ];
        DB::table('vik_inscrit')->insert($inscrits);

        // Insert clubs
        DB::table('vik_club')->insert([
            ['CLU_NUM' => 1, 'INS_ID' => 21, 'CLU_NOM' => 'CO Azimut 77', 'CLU_ADRESSE' => '24 Rue de la Rochette', 'CLU_CODE_POSTAL' => 77000, 'CLU_VILLE' => 'Melun'],
            ['CLU_NUM' => 2, 'INS_ID' => 7, 'CLU_NOM' => 'Balise 25', 'CLU_ADRESSE' => '2 Avenue Léo Lagrange', 'CLU_CODE_POSTAL' => 25000, 'CLU_VILLE' => 'Besançon'],
        ]);

        // Insert raids
        DB::table('vik_raid')->insert([
            [
                'RAID_NUM' => 1, 'CLU_NUM' => 1, 'INS_ID' => 22, 'RAID_RESP_INS_ID' => 22,
                'RAID_NOM' => 'Raid CHAMPETRE', 'RAID_DATE_DEBUT_INSCRI' => '2025-08-10',
                'RAID_DATE_FIN_INSCRI' => '2025-10-30', 'RAID_DATE_DEBUT' => '2025-11-13',
                'RAID_DATE_FIN' => '2025-11-14', 'RAID_CONTACT' => 'Paul Dorbec',
                'RAID_CONTACT_MAIL' => 'paul.dorbec@unicaen.fr', 'RAID_LIEN_SITE_WEB' => null,
                'RAID_LATITUDE' => 48.45121, 'RAID_LONGITUDE' => 0.47461,
                'RAID_ILLUSTRATION' => 'image_champetre.png'
            ],
            [
                'RAID_NUM' => 2, 'CLU_NUM' => 1, 'INS_ID' => 21, 'RAID_RESP_INS_ID' => 6,
                'RAID_NOM' => 'Raid O\'Bivwak', 'RAID_DATE_DEBUT_INSCRI' => '2026-01-09',
                'RAID_DATE_FIN_INSCRI' => '2026-04-30', 'RAID_DATE_DEBUT' => '2026-05-23',
                'RAID_DATE_FIN' => '2026-05-24', 'RAID_CONTACT' => 'claire.dupont@test.fr',
                'RAID_CONTACT_MAIL' => 'claire.dupont@test.fr', 'RAID_LIEN_SITE_WEB' => null,
                'RAID_LATITUDE' => 48.98793, 'RAID_LONGITUDE' => 1.21289,
                'RAID_ILLUSTRATION' => 'raid_obivwak.png'
            ],
        ]);

        // Insert courses
        DB::table('vik_course')->insert([
            [
                'COU_NUM' => 1, 'INS_ID' => 20, 'TYP_NUM' => 1, 'RAID_NUM' => 1,
                'COU_NOM' => 'LUTIN', 'COU_DUREE' => 150, 'COU_DIFFICULTE' => 'Licorne',
                'COU_DATE_DEPART' => '2025-11-13 10:00:00', 'COU_DATE_FIN' => '2025-11-13 18:00:00',
                'COU_NB_PART_MIN' => 2, 'COU_NB_PART_MAX' => 8, 'COU_NB_EQU_MIN' => 3,
                'COU_NB_EQU_MAX' => 3, 'COU_PART_PAR_EQU_MAX' => 2, 'COU_PRIX_REPAS' => null,
                'COU_REDUC_LICENCIE' => null, 'COU_VALIDE' => 1, 'COU_AGE_A' => 12,
                'COU_AGE_B' => 16, 'COU_AGE_C' => 18
            ],
            [
                'COU_NUM' => 2, 'INS_ID' => 22, 'TYP_NUM' => 2, 'RAID_NUM' => 1,
                'COU_NOM' => 'ELFE', 'COU_DUREE' => 420, 'COU_DIFFICULTE' => 'Gazelle',
                'COU_DATE_DEPART' => '2025-11-14 05:00:00', 'COU_DATE_FIN' => '2025-11-14 18:00:00',
                'COU_NB_PART_MIN' => 2, 'COU_NB_PART_MAX' => 8, 'COU_NB_EQU_MIN' => 4,
                'COU_NB_EQU_MAX' => 4, 'COU_PART_PAR_EQU_MAX' => 2, 'COU_PRIX_REPAS' => null,
                'COU_REDUC_LICENCIE' => null, 'COU_VALIDE' => 1, 'COU_AGE_A' => 18,
                'COU_AGE_B' => 18, 'COU_AGE_C' => 18
            ],
        ]);

        // Insert equipes
        DB::table('vik_equipe')->insert([
            ['COU_NUM' => 1, 'EQU_NUM' => 1, 'INS_ID' => 19, 'EQU_NOM' => 'les Balises Furtives', 'EQU_PAIEMENT_VALIDE' => 1, 'EQU_ORDRE_ARRIVEE' => 1, 'EQU_TEMPS' => 165.00, 'EQU_POINTS' => 199],
            ['COU_NUM' => 1, 'EQU_NUM' => 2, 'INS_ID' => 9, 'EQU_NOM' => 'Les traqueurs du Nord', 'EQU_PAIEMENT_VALIDE' => 1, 'EQU_ORDRE_ARRIVEE' => 2, 'EQU_TEMPS' => 180.25, 'EQU_POINTS' => 145],
            ['COU_NUM' => 2, 'EQU_NUM' => 1, 'INS_ID' => 21, 'EQU_NOM' => 'Black Compass', 'EQU_PAIEMENT_VALIDE' => 1, 'EQU_ORDRE_ARRIVEE' => 4, 'EQU_TEMPS' => 381.35, 'EQU_POINTS' => 199],
        ]);

        // Insert participations
        DB::table('vik_participer')->insert([
            ['INS_ID' => 8, 'COU_NUM' => 1, 'EQU_NUM' => 1, 'PAR_PARTICIPE' => 1, 'PAR_NUM_PPS' => null],
            ['INS_ID' => 9, 'COU_NUM' => 1, 'EQU_NUM' => 2, 'PAR_PARTICIPE' => 1, 'PAR_NUM_PPS' => null],
        ]);

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
