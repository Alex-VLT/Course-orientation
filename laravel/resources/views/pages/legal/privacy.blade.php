@extends('layouts.app')

@section('title', 'Politique de Confidentialité - L\'Embuscade')

@section('content')
<div class="bg-[#f7f5e6] min-h-screen py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-lg overflow-hidden">
        
        {{-- En-tête --}}
        <div class="bg-[#7DC2A5] px-6 py-8">
            <h1 class="text-3xl font-bold text-white text-center">Politique de Confidentialité</h1>
        </div>

        {{-- Contenu --}}
        <div class="p-8 space-y-8 text-gray-700">

            <p class="italic text-sm text-gray-500">Dernière mise à jour : {{ date('d/m/Y') }}</p>

            <section>
                <h2 class="text-xl font-bold text-[#A67C52] mb-4 border-b pb-2">1. Gestion des données personnelles</h2>
                <p>
                    Le Client est informé des réglementations concernant la communication marketing, la loi du 21 Juin 2014 pour la confiance dans l’Economie Numérique, la Loi Informatique et Liberté du 06 Août 2004 ainsi que du Règlement Général sur la Protection des Données (RGPD : n° 2016-679).
                </p>
            </section>

            <section>
                <h2 class="text-xl font-bold text-[#A67C52] mb-4 border-b pb-2">2. Collecte des données</h2>
                <p>Pour les besoins de l'application <strong>L'Embuscade</strong>, nous collectons les données suivantes lors de l'inscription :</p>
                <ul class="list-disc list-inside mt-2 ml-4 space-y-1">
                    <li>Nom et Prénom</li>
                    <li>Adresse email</li>
                    <li>Date de naissance</li>
                    <li>Adresse postale et Ville</li>
                    <li>Numéro de téléphone</li>
                    <li>Numéro de licence (si applicable)</li>
                </ul>
                <p class="mt-2">Ces données sont nécessaires à la gestion de votre compte, à l'inscription aux événements sportifs et à la communication liée à l'association.</p>
            </section>

            <section>
                <h2 class="text-xl font-bold text-[#A67C52] mb-4 border-b pb-2">3. Droit d’accès, de rectification et d’opposition</h2>
                <p>
                    Conformément à la réglementation européenne en vigueur, les Utilisateurs de L'Embuscade disposent des droits suivants :
                </p>
                <ul class="list-disc list-inside mt-2 ml-4 space-y-1">
                    <li>Droit d'accès (article 15 RGPD) et de rectification (article 16 RGPD), de mise à jour, de complétude des données des Utilisateurs.</li>
                    <li>Droit de verrouillage ou d’effacement des données des Utilisateurs à caractère personnel (article 17 du RGPD).</li>
                    <li>Droit de retirer à tout moment un consentement (article 13-2c RGPD).</li>
                </ul>
                <p class="mt-4">
                    Pour exercer ces droits, vous pouvez nous contacter par email à : <strong>contact.embuscadesae@gmail.com</strong>
                    ou via la section "Supprimer mon compte" dans votre profil.
                </p>
            </section>

            <section>
                <h2 class="text-xl font-bold text-[#A67C52] mb-4 border-b pb-2">4. Sécurité</h2>
                <p>
                    Pour assurer la sécurité et la confidentialité des Données Personnelles, L'Embuscade utilise des réseaux protégés par des dispositifs standards. Les mots de passe sont cryptés dans notre base de données.
                </p>
            </section>

        </div>
    </div>
</div>
@endsection