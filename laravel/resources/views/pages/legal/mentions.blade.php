@extends('layouts.app')

@section('title', 'Mentions Légales - L\'Embuscade')

@section('content')
<div class="bg-[#f7f5e6] min-h-screen py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-lg overflow-hidden">
        
        {{-- Header --}}
        <div class="bg-[#7DC2A5] px-6 py-8">
            <h1 class="text-3xl font-bold text-white text-center">Mentions Légales</h1>
        </div>

        {{-- Content --}}
        <div class="p-8 space-y-8 text-gray-700">

            {{-- Section 1 --}}
            <section>
                <h2 class="text-xl font-bold text-[#A67C52] mb-4 border-b pb-2">1. Édition du site</h2>
                <p class="mb-2">En vertu de l'article 6 de la loi n° 2004-575 du 21 juin 2004 pour la confiance dans l'économie numérique, il est précisé aux utilisateurs du site internet <strong>L'Embuscade</strong> l'identité des différents intervenants dans le cadre de sa réalisation et de son suivi :</p>
                <ul class="list-disc list-inside space-y-1 ml-4">
                    <li><strong>Propriétaire du site :</strong> Embuscade - Adresse : Rue Anton Pavlovitch Tchekhov, 14123 Ifs</li>
                    <li><strong>Contact :</strong> contact.embuscadesae@gmail.com - Téléphone : 02 31 52 55 00</li>
                </ul>
            </section>

            {{-- Section 2 --}}
            <section>
                <h2 class="text-xl font-bold text-[#A67C52] mb-4 border-b pb-2">2. Hébergement</h2>
                <p>Le site L'Embuscade est hébergé par :</p>
                <p class="font-medium mt-2">
                    Université Caen Normandie<br>
                    Boulevard de la Paix, 14000 Caen<br>
                    02 31 56 55 00
                </p>
            </section>

            {{-- Section 3 --}}
            <section>
                <h2 class="text-xl font-bold text-[#A67C52] mb-4 border-b pb-2">3. Propriété intellectuelle</h2>
                <p>
                    Embuscade est propriétaire des droits de propriété intellectuelle et détient les droits d’usage sur tous les éléments accessibles sur le site internet, notamment les textes, images, graphismes, logos, vidéos, architecture, icônes et sons.
                </p>
                <p class="mt-2">
                    Toute reproduction, représentation, modification, publication, adaptation de tout ou partie des éléments du site, quel que soit le moyen ou le procédé utilisé, est interdite, sauf autorisation écrite préalable.
                </p>
            </section>

            {{-- Section 4 --}}
            <section>
                <h2 class="text-xl font-bold text-[#A67C52] mb-4 border-b pb-2">4. Limitations de responsabilité</h2>
                <p>
                    Embuscade ne pourra être tenu pour responsable des dommages directs et indirects causés au matériel de l’utilisateur, lors de l’accès au site L'Embuscade.
                </p>
                <p class="mt-2">
                    Embuscade décline toute responsabilité quant à l’utilisation qui pourrait être faite des informations et contenus présents sur L'Embuscade.
                </p>
            </section>

        </div>
    </div>
</div>
@endsection