<nav class="bg-[#5a7f5a] shadow-md transition-all duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-24">

            {{-- 1. LOGO & LIEN ACCUEIL --}}
            <div class="flex-shrink-0">
                <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                    <img src="{{ asset('images/logoEmbuscade.png') }}" 
                         alt="L'EMBUSCADE" 
                         class="h-12 w-auto object-contain transition-transform duration-300 group-hover:scale-105">
                    
                    {{-- Modification ici : Texte changé en "Accueil" --}}
                    <span class="font-bold text-xl tracking-wider text-black group-hover:text-black/80 transition-colors">
                        Accueil
                    </span>
                </a>
            </div>
            
            {{-- 2. ACTIONS AREA --}}
            <div class="flex items-center gap-3 md:gap-4">

                @auth
                    {{-- Gestion : boutons pour responsables (club / raid / course) --}}
                    @if(auth()->user()->managesClub())
                        <a href="{{ route('dashboard') }}#club"
                           class="flex items-center gap-2 bg-[#A67C52] text-black font-bold px-4 py-2.5 md:px-5 md:py-2.5 rounded-full shadow-sm hover:bg-[#8B623D] hover:shadow-md transform hover:-translate-y-0.5 transition-all duration-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <!-- House / Home icon -->
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 11.5L12 5l9 6.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V11.5z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 21v-6h6v6" />
                            </svg>
                            <span class="hidden md:inline">Gérer mon club</span>
                        </a>
                    @endif

                    @if(auth()->user()->managesRaid())
                        <a href="{{ route('raids.manager') }}"
                           class="flex items-center gap-2 bg-[#A67C52] text-black font-bold px-4 py-2.5 md:px-5 md:py-2.5 rounded-full shadow-sm hover:bg-[#8B623D] hover:shadow-md transform hover:-translate-y-0.5 transition-all duration-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11a3 3 0 100-6 3 3 0 000 6z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2C8 2 5 5 5 9c0 5 7 13 7 13s7-8 7-13c0-4-3-7-7-7z"/>
                            </svg>
                            <span class="hidden md:inline">Gérer mes raids</span>
                        </a>
                    @endif

                    @if(auth()->user()->managesCourse())
                        <a href="{{ route('race.organizer_index') }}"
                           class="flex items-center gap-2 bg-[#A67C52] text-black font-bold px-4 py-2.5 md:px-5 md:py-2.5 rounded-full shadow-sm hover:bg-[#8B623D] hover:shadow-md transform hover:-translate-y-0.5 transition-all duration-200">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 3v18" />
                                <!-- Checkered squares -->
                                <rect x="7" y="4" width="3" height="3" fill="currentColor" />
                                <rect x="10" y="4" width="3" height="3" stroke="currentColor" fill="none" stroke-width="1.5" />
                                <rect x="13" y="4" width="3" height="3" fill="currentColor" />

                                <rect x="7" y="7" width="3" height="3" stroke="currentColor" fill="none" stroke-width="1.5" />
                                <rect x="10" y="7" width="3" height="3" fill="currentColor" />
                                <rect x="13" y="7" width="3" height="3" stroke="currentColor" fill="none" stroke-width="1.5" />

                                <!-- Flag outline -->
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4c4-1 6-1 10 0v6c-4-1-6-1-10 0V4z" />
                            </svg>
                            <span class="hidden md:inline">Gérer mes courses</span>
                        </a>
                    @endif

                    {{-- Admin Page (visible only when INS_IS_ADMIN == 1) --}}
                    @if(auth()->user()->isAdmin())
                        <a href="{{ url('/clubs') }}"
                           class="flex items-center gap-2 bg-[#A67C52] text-black font-bold px-4 py-2.5 md:px-5 md:py-2.5 rounded-full shadow-sm hover:bg-[#8B623D] hover:shadow-md transform hover:-translate-y-0.5 transition-all duration-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2l7 4v6c0 5-3.58 9-7 10-3.42-1-7-5-7-10V6l7-4z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.5 11.5l2 2 4-4"/>
                            </svg>
                            <span class="hidden md:inline">Page Admin</span>
                        </a>
                    @endif
                    
                    {{-- B. Profil (Même style que Dashboard) --}}
                    <a href="{{ route('profil') }}"
                       class="flex items-center gap-2 bg-[#A67C52] text-black font-bold px-4 py-2.5 md:px-5 md:py-2.5 rounded-full shadow-sm hover:bg-[#8B623D] hover:shadow-md transform hover:-translate-y-0.5 transition-all duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span class="hidden md:inline">Profil</span>
                    </a>

                    {{-- C. Déconnexion (Même style que Dashboard) --}}
                    <form action="{{route('logout')}}" method="POST" class="flex items-center">
                        @csrf
                        <button type="submit"
                                class="cursor-pointer flex items-center gap-2 bg-[#A67C52] text-black font-bold px-4 py-2.5 md:px-5 md:py-2.5 rounded-full shadow-sm hover:bg-[#8B623D] hover:shadow-md transform hover:-translate-y-0.5 transition-all duration-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            <span class="hidden md:inline">Déconnexion</span>
                        </button>
                    </form>
                @endauth
                
                @guest                    
                    {{-- Login Button --}}
                    <a href="{{ route('login') }}"
                       class="flex items-center gap-2 bg-[#A67C52] text-black font-bold px-5 py-2.5 rounded-full shadow-sm hover:bg-[#8B623D] hover:shadow-md transform hover:-translate-y-0.5 transition-all duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                        <span class="hidden md:inline">Connexion</span>
                    </a>

                    {{-- Register Button --}}
                    <a href="{{ route('register') }}"
                       class="flex items-center gap-2 bg-[#A67C52] text-black font-bold px-5 py-2.5 rounded-full shadow-sm hover:bg-[#8B623D] hover:shadow-md transform hover:-translate-y-0.5 transition-all duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        <span class="hidden md:inline">S'inscrire</span>
                    </a>
                @endguest

            </div> 
        </div> 
    </div> 
</nav>