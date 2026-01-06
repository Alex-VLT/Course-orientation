<nav class="bg-[#7DC2A5] ">
    <div class="px-6 py-1 sm:px-4 lg:px-6 ">
        <div class="flex items-center justify-between">
            <!-- Logo -->
            <a href="###" {{-- ### placeholder for "{{ route('home') }}" --}} 
               class="flex items-center">
                <img src="{{ asset('images/logoEmbuscade.png') }}" 
                     alt="L'EMBUSCADE" 
                     class="h-16 sm:h-10 md:h-24 lg:h-24 w-auto object-contain">
            </a>
            
            @auth
                <div class="flex items-center gap-3 md:gap-4">
                    @if(auth()->user()->isMember())
                        <a href="##" {{-- ### placeholder for "{{ route('dashboard') }}" --}}
                        class="bg-[#A67C52] text-black font-semibold px-4 py-2 md:px-6 md:py-2 rounded-md hover:bg-[#8B623D] transition-colors duration-200 whitespace-nowrap text-sm md:text-base">
                            Dashboard
                        </a>
                    @else
                        <form action="{{route('logout')}}" method="POST">
                            @csrf
                            <button type="submit"
                            class="cursor-pointer bg-[#A67C52] text-black font-semibold px-4 py-2 md:px-6 md:py-2 rounded-md hover:bg-[#8B623D] transition-colors duration-200 whitespace-nowrap text-sm md:text-base">
                                Se Déconnecter
                            </button>
                        </form>
                        
                        <a href="##" {{-- ### placeholder for "{{ route('profile') }}" --}}
                        class="bg-[#A67C52] text-black font-semibold px-4 py-2 md:px-6 md:py-2 rounded-md hover:bg-[#8B623D] transition-colors duration-200 whitespace-nowrap text-sm md:text-base">
                            Profil
                        </a>
                    @endif
                </div>
            @endauth
            
            @guest
                @if(Route::is('login'))
                    <a href="{{ route('register') }}"
                    class="bg-[#A67C52] text-black font-semibold px-6 py-2 rounded-md hover:bg-[#8B623D] transition-colors duration-200 whitespace-nowrap text-sm md:text-base">
                        S'inscrire
                    </a>
                    
                @else
                    <a href="{{ route('login') }}"
                    class="bg-[#A67C52] text-black font-semibold px-6 py-2 rounded-md hover:bg-[#8B623D] transition-colors duration-200 whitespace-nowrap text-sm md:text-base">
                        Se Connecter
                    </a>
                @endif
                
            @endguest
        </div> 
    </div> 
</nav>