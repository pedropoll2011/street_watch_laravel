<div class="min-h-screen flex items-center justify-center bg-slate-900 px-4">
    <div class="w-full max-w-md bg-slate-950/80 border border-slate-800 rounded-2xl shadow-xl p-8">
        <h1 class="text-2xl font-semibold text-slate-50 mb-2 text-center">
            Street Watch
        </h1>
        <p class="text-sm text-slate-400 mb-6 text-center">
            Entrar com sua conta do aplicativo
        </p>

        @if($errorMessage)
            <div class="mb-4 text-sm text-red-400 bg-red-900/30 border border-red-700/60 rounded-lg px-3 py-2">
                {{ $errorMessage }}
            </div>
        @endif

        <form wire:submit.prevent="login" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1">
                    Email
                </label>
                <input
                    type="email"
                    wire:model.defer="email"
                    class="w-full rounded-lg bg-slate-900 border border-slate-700 px-3 py-2 text-sm text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                    placeholder="seu@email.com"
                >
                @error('email')
                    <div class="text-xs text-red-400 mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1">
                    Senha
                </label>
                <input
                    type="password"
                    wire:model.defer="password"
                    class="w-full rounded-lg bg-slate-900 border border-slate-700 px-3 py-2 text-sm text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                    placeholder="••••••••"
                >
                @error('password')
                    <div class="text-xs text-red-400 mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="flex items-center justify-between text-xs text-slate-400">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" wire:model="remember" class="rounded bg-slate-900 border-slate-700 text-emerald-500">
                    Manter conectado
                </label>
            </div>

            <button
                type="submit"
                class="w-full inline-flex items-center justify-center gap-2 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-semibold rounded-lg py-2.5 text-sm transition disabled:opacity-50 disabled:cursor-not-allowed"
                @disabled($loading)
            >
                @if($loading)
                    <svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" opacity=".25"/>
                        <path d="M4 12a8 8 0 0 1 8-8" stroke="currentColor" stroke-width="4" fill="none" stroke-linecap="round"/>
                    </svg>
                    Entrando...
                @else
                    Entrar
                @endif
            </button>
        </form>
    </div>
</div>
