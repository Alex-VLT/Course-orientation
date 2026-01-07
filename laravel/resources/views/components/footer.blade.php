<footer class="bg-[#7DC2A5] w-full text-black mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            
            <div class="flex flex-col space-y-4">
                <h2 class="text-2xl font-bold tracking-tight">Embuscade</h2>
                <p class="text-sm text-black/80 max-w-xs">
                    L'application de référence pour gérer vos événements sportifs et associatifs simplement.
                </p>
                <div class="flex items-center space-x-4 pt-2">
                    <a href="#" class="p-2 bg-black/5 rounded-full hover:bg-black/10 hover:scale-110 transition-all duration-300">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                    </a>
                    <a href="#" class="p-2 bg-black/5 rounded-full hover:bg-black/10 hover:scale-110 transition-all duration-300">
                         <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                    </a>
                </div>
            </div>

            <div class="flex flex-col space-y-4 md:items-center">
                <h3 class="font-bold text-lg">Navigation</h3>
                <ul class="space-y-2 text-sm md:text-center text-black/80">
                    <li><a href="{{ url('/') }}" class="hover:text-black hover:underline transition">Accueil</a></li>
                    <li><a href="{{ route('profil') }}" class="hover:text-black hover:underline transition">Mon Compte</a></li>
                </ul>
            </div>

            <div class="flex flex-col space-y-4 md:items-end">
                <h3 class="font-bold text-lg">Besoin d'aide ?</h3>
                <p class="text-sm text-black/80 text-right max-w-xs">
                    Notre équipe est disponible pour répondre à toutes vos questions.
                </p>
                <a href="#" class="inline-flex items-center justify-center px-4 py-2 border border-black rounded-lg text-sm font-medium hover:bg-black hover:text-[#FFFFFF] transition-colors duration-300">
                    Contactez le support
                </a>
            </div>
        </div>
    </div>

    <div class="border-t border-black/10">
    <div class="max-w-7xl mx-auto px-4 py-6 flex flex-col md:flex-row justify-between items-center text-sm text-black/60">
        <p>&copy; {{ date('Y') }} Embuscade. Tous droits réservés.</p>
        <div class="flex space-x-6 mt-4 md:mt-0">
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