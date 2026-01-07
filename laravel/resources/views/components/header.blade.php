<nav class="bg-[#7DC2A5]">
    <div class="px-2 py-1 sm:px-4 lg:px-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2 md:gap-4">
                <a href="{{ url('/') }}"
                   class="flex items-center">
                    <img src="{{ asset('images/logoEmbuscade.png') }}" 
                         alt="L'EMBUSCADE" 
                         class="h-16 sm:h-10 md:h-24 lg:h-16 w-auto object-contain">
                </a>
                
                <a  href="{{ url('/') }}"
                   class="flex items-center gap-2 bg-[#A67C52] text-black font-bold px-2 py-2 md:px-4 md:py-2 rounded-md hover:bg-[#8B623D] transition-colors duration-200 text-sm md:text-base lg:text-lg whitespace-nowrap">
                    <svg class="w-5 h-5 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span class="hidden md:inline">L'EMBUSCADE</span>
                </a>
            </div>
            
            @auth
                <div class="flex items-center gap-2 md:gap-3 lg:gap-4">
                    @if(auth()->user()->isMember())
                            <a href="{{ route('dashboard') }}"
                           class="flex items-center gap-2 bg-[#A67C52] text-black font-semibold px-2 py-2 md:px-4 md:py-2 lg:px-6 rounded-md hover:bg-[#8B623D] transition-colors duration-200 whitespace-nowrap text-sm md:text-base">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            <span class="hidden md:inline">Dashboard</span>
                        </a>
                    @endif
                    
                    <form action="{{route('logout')}}" method="POST">
                        @csrf
                        <button type="submit"
                                class="flex items-center gap-2 cursor-pointer bg-[#A67C52] text-black font-semibold px-2 py-2 md:px-4 md:py-2 lg:px-6 rounded-md hover:bg-[#8B623D] transition-colors duration-200 whitespace-nowrap text-sm md:text-base">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <span class="hidden md:inline">Se Déconnecter</span>
                        </button>
                    </form>
                    
                    <a href="{{ route('profil') }}"
                       class="flex items-center gap-2 bg-[#A67C52] text-black font-semibold px-2 py-2 md:px-4 md:py-2 lg:px-6 rounded-md hover:bg-[#8B623D] transition-colors duration-200 whitespace-nowrap text-sm md:text-base">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span class="hidden md:inline">Profile</span>
                    </a>
                </div>
            @endauth
            
            @guest
                @if(Route::is('login'))
                    <a href="{{ route('register') }}"
                       class="flex items-center gap-2 bg-[#A67C52] text-black font-semibold px-2 py-2 md:px-6 md:py-2 rounded-md hover:bg-[#8B623D] transition-colors duration-200 whitespace-nowrap text-sm md:text-base">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                        <span class="hidden md:inline">S'inscrire</span>
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="flex items-center gap-2 bg-[#A67C52] text-black font-semibold px-2 py-2 md:px-6 md:py-2 rounded-md hover:bg-[#8B623D] transition-colors duration-200 whitespace-nowrap text-sm md:text-base">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                        <span class="hidden md:inline">Se Connecter</span>
                    </a>
                @endif
            @endguest
        </div> 
    </div> 
</nav>