<nav class="bg-[#7DC2A5]">
    <div class="px-2 sm:px-4 lg:px-6 py-1">
        <div class="flex items-center justify-between">
            <!-- Logo cliquable -->
            <a href="###" {{-- ### placeholder for "{{ route('home') }}" --}} 
               class="flex items-center">
                <img src="{{ asset('images/logoEmbuscade.png') }}" 
                     alt="L'EMBUSCADE" 
                     class="h-16 sm:h-10 md:h-24 lg:h-16 w-auto object-contain">
            </a>
            
            <!-- Bouton Se Connecter -->
            <a href="##" {{-- ### placeholder for "{{ route('login') }}" --}}
               class="bg-[#A67C52] text-black font-semibold px-6 py-2 rounded-md hover:bg-[#8B623D] transition-colors duration-200 whitespace-nowrap text-sm md:text-base">
                Se Connecter
            </a>
        </div> 
    </div>
</nav>