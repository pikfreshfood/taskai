<x-guest-layout>
    <div class="text-center mb-8">
        <div class="w-14 h-14 bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-indigo-500/25">
            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
        </div>
        <h2 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">Reset Password</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 font-medium">We'll send you a reset link</p>
    </div>

    <div class="mb-6 text-sm text-gray-600 dark:text-gray-400 text-center">
        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link.') }}
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-2 w-full" type="email" name="email" :value="old('email')" required autofocus placeholder="you@example.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <button type="submit" class="w-full gradient-btn justify-center py-3">
            {{ __('Send Reset Link') }}
        </button>

        <p class="text-center text-sm text-gray-500 dark:text-gray-400 mt-4">
            <a href="{{ route('login') }}" class="font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 transition-colors">Back to login</a>
        </p>
    </form>
</x-guest-layout>
