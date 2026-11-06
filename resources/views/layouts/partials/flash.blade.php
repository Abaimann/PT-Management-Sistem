@if (session('success'))
    <div x-data="{ show: true }" x-show="show" x-transition.duration.300ms
         class="mb-5 flex items-start gap-3 p-4 rounded-xl border border-emerald-500/20 bg-emerald-500/10 text-emerald-300 text-sm shadow-lg shadow-emerald-950/20">
        <svg class="w-5 h-5 text-emerald-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        </svg>
        <div class="flex-1 font-medium leading-relaxed">
            {{ session('success') }}
        </div>
        <button type="button" @click="show = false" class="text-emerald-400/70 hover:text-emerald-300 transition -mr-1">
            <span class="sr-only">Tutup</span>
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
@endif

@if (session('error'))
    <div x-data="{ show: true }" x-show="show" x-transition.duration.300ms
         class="mb-5 flex items-start gap-3 p-4 rounded-xl border border-rose-500/20 bg-rose-500/10 text-rose-300 text-sm shadow-lg shadow-rose-950/20">
        <svg class="w-5 h-5 text-rose-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 7.5h.01" />
        </svg>
        <div class="flex-1 font-medium leading-relaxed">
            {{ session('error') }}
        </div>
        <button type="button" @click="show = false" class="text-rose-400/70 hover:text-rose-300 transition -mr-1">
            <span class="sr-only">Tutup</span>
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
@endif

@if (session('warning'))
    <div x-data="{ show: true }" x-show="show" x-transition.duration.300ms
         class="mb-5 flex items-start gap-3 p-4 rounded-xl border border-amber-500/20 bg-amber-500/10 text-amber-300 text-sm shadow-lg shadow-amber-950/20">
        <svg class="w-5 h-5 text-amber-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
        </svg>
        <div class="flex-1 font-medium leading-relaxed">
            {{ session('warning') }}
        </div>
        <button type="button" @click="show = false" class="text-amber-400/70 hover:text-amber-300 transition -mr-1">
            <span class="sr-only">Tutup</span>
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
@endif

@if (session('status'))
    <div x-data="{ show: true }" x-show="show" x-transition.duration.300ms
         class="mb-5 flex items-start gap-3 p-4 rounded-xl border border-blue-500/20 bg-blue-500/10 text-blue-300 text-sm shadow-lg shadow-blue-950/20">
        <svg class="w-5 h-5 text-blue-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
        </svg>
        <div class="flex-1 font-medium leading-relaxed">
            {{ session('status') }}
        </div>
        <button type="button" @click="show = false" class="text-blue-400/70 hover:text-blue-300 transition -mr-1">
            <span class="sr-only">Tutup</span>
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
@endif
