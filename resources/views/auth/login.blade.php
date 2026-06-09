<x-guest-layout>
    <div class="mb-7">
        <div class="flex items-center justify-between gap-4 border-b border-green-500/20 pb-4">
            <div>
                <p class="text-xs text-green-400/70 terminal-label">admin access</p>
                <h1 class="mt-2 text-2xl font-black tracking-[0.12em] text-white">TASK AI LOGIN</h1>
            </div>
            <div class="flex items-center gap-2 text-xs text-green-300">
                <span class="pulse-dot"></span>
                ONLINE
            </div>
        </div>
        <p class="mt-4 text-sm leading-6 text-green-100/62">
            Sign in to control payments, user access, subscriptions, and desktop app update pushes.
        </p>
    </div>

    <x-auth-session-status class="mb-4 text-sm text-green-300" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="block text-xs font-bold uppercase tracking-[0.24em] text-green-400/75">Username or Email</label>
            <input id="email" type="text" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="admin"
                class="mt-2 block w-full border border-green-500/30 bg-black/55 px-4 py-3 font-mono text-sm text-green-100 placeholder:text-green-700/70 shadow-[inset_0_0_18px_rgba(0,255,102,0.05)] focus:border-cyan-300 focus:ring-1 focus:ring-cyan-300">
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-xs text-red-300" />
        </div>

        <div>
            <label for="password" class="block text-xs font-bold uppercase tracking-[0.24em] text-green-400/75">Password</label>
            <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="********"
                class="mt-2 block w-full border border-green-500/30 bg-black/55 px-4 py-3 font-mono text-sm text-green-100 placeholder:text-green-700/70 shadow-[inset_0_0_18px_rgba(0,255,102,0.05)] focus:border-cyan-300 focus:ring-1 focus:ring-cyan-300">
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-xs text-red-300" />
        </div>

        <div class="flex items-center justify-between gap-4">
            <label for="remember_me" class="inline-flex cursor-pointer items-center gap-2 text-sm text-green-100/65">
                <input id="remember_me" type="checkbox" name="remember" class="border-green-500/40 bg-black text-green-500 focus:ring-green-500">
                Remember session
            </label>

            @if (Route::has('password.request'))
                <a class="text-xs font-bold uppercase tracking-[0.18em] text-cyan-300/80 hover:text-cyan-200" href="{{ route('password.request') }}">
                    Forgot Password
                </a>
            @endif
        </div>

        <button type="submit" class="w-full border border-green-400 bg-green-500/10 px-5 py-3 text-sm font-black uppercase tracking-[0.22em] text-green-200 shadow-[0_0_28px_rgba(0,255,102,0.12)] transition hover:bg-green-400 hover:text-black hover:shadow-[0_0_36px_rgba(0,255,102,0.28)]">
            Authenticate
        </button>

        <div class="border border-cyan-400/20 bg-cyan-400/5 px-4 py-3 text-xs leading-5 text-cyan-100/65">
            <span class="text-cyan-300">root@task-ai:~$</span> admin credentials required before command-center access.
        </div>
    </form>
</x-guest-layout>
