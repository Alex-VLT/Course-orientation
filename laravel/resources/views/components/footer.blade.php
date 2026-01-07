<footer class="bg-[#7DC2A5] w-full text-black mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            
            <div class="flex flex-col space-y-4 items-center md:items-start text-center md:text-left">
                <h2 class="text-2xl font-bold tracking-tight">Embuscade</h2>
                <p class="text-sm text-black/80 max-w-xs">
                    L'application de référence pour gérer vos événements sportifs et associatifs simplement.
                </p>
            </div>

            <div class="flex flex-col space-y-4 items-center">
                <h3 class="font-bold text-lg">Navigation</h3>
                <ul class="space-y-2 text-sm text-center text-black/80">
                    <li>
                        <a href="{{ url('/') }}" class="hover:text-black hover:underline transition">
                            Accueil
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('profil') }}" class="hover:text-black hover:underline transition">
                            Mon Compte
                        </a>
                    </li>
                </ul>
            </div>

            <div class="flex flex-col space-y-4 items-center md:items-end text-center md:text-right">
                <h3 class="font-bold text-lg">Besoin d'aide ?</h3>
                <p class="text-sm text-black/80 max-w-xs">
                    Notre équipe est disponible pour répondre à toutes vos questions à l'adresse :<br>
                    
                    <a href="mailto:contact.embuscadesae@gmail.com" 
                       class="font-bold underline hover:text-white transition-colors duration-200 block mt-1">
                        contact.embuscadesae@gmail.com
                    </a>
                </p>
            </div>
        </div>
    </div>

    <div class="border-t border-black/10">
        <div class="max-w-7xl mx-auto px-4 py-6 flex flex-col md:flex-row justify-between items-center text-sm text-black/60">
            <p class="mb-4 md:mb-0 text-center md:text-left">
                &copy; {{ date('Y') }} Embuscade. Tous droits réservés.
            </p>
            
            <div class="flex space-x-6">
                <a href="{{ route('mentions-legacy') }}" class="hover:text-black transition-colors">
                    Mentions légales
                </a>
                <a href="{{ route('confidentiality-legacy') }}" class="hover:text-black transition-colors">
                    Confidentialité
                </a>
            </div>
        </div>
    </div>
</footer>