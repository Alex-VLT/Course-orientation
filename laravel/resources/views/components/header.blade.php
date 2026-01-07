<nav class="bg-[#7DC2A5] shadow-md z-50 transition-all duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20">

            {{-- 1. LOGO --}}
            <div class="flex-shrink-0">
                <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                    <img src="{{ asset('images/logoEmbuscade.png') }}" 
                         alt="L'EMBUSCADE" 
                         class="h-12 w-auto object-contain transition-transform duration-300 group-hover:scale-105">
                    
                    <span class="font-bold text-xl tracking-wider text-black group-hover:text-black/80 transition-colors">
                        L'EMBUSCADE
                    </span>
                </a>
            </div>
            
            {{-- 2. ACTIONS AREA --}}
            <div class="flex items-center gap-2 md:gap-4">

                @auth
                    {{-- A. Members Actions --}}
                    @if(auth()->user()->isMember())
                        <a href="{{ route('dashboard') }}"
                           class="hidden md:flex items-center gap-2 bg-[#A67C52] text-black font-bold px-5 py-2.5 rounded-full shadow-sm hover:bg-[#8B623D] hover:shadow-md transform hover:-translate-y-0.5 transition-all duration-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            <span>Dashboard</span>
                        </a>
                        {{-- Mobile Version --}}
                        <a href="{{ route('dashboard') }}" class="md:hidden p-2 bg-[#A67C52] rounded-full text-black hover:bg-[#8B623D]">
                             <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        </a>
                    @endif
                    
                    {{-- B. Profil --}}
                    <a href="{{ route('profil') }}"
                       title="Mon Profil"
                       class="flex items-center justify-center w-10 h-10 rounded-full bg-black/10 text-black hover:bg-black/20 hover:scale-110 transition-all duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </a>

                    {{-- C. Logout --}}
                    <form action="{{route('logout')}}" method="POST" class="flex items-center">
                        @csrf
                        <button type="submit"
                                title="Se déconnecter"
                                class="flex items-center gap-2 text-black hover:text-red-700 font-medium px-3 py-2 rounded-lg hover:bg-red-50/20 transition-colors duration-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            <span class="hidden lg:inline text-sm">Déconnexion</span>
                        </button>
                    </form>
                @endauth
                
                @guest                    
                    {{-- Login Button --}}
                    <a href="{{ route('login') }}"
                       class="flex items-center gap-2 text-black font-bold px-4 py-2.5 rounded-full hover:bg-black/10 transition-all duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                        <span class="hidden md:inline">Connexion</span>
                    </a>

                    {{-- REgister Button --}}
                    <a href="{{ route('register') }}"
                       class="flex items-center gap-2 bg-[#A67C52] text-black font-bold px-5 py-2.5 rounded-full shadow-sm hover:bg-[#8B623D] hover:shadow-md transform hover:-translate-y-0.5 transition-all duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        <span>S'inscrire</span>
                    </a>
                @endguest

            </div> 
        </div> 
    </div> 
</nav>