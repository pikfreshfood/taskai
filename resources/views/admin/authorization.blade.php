<x-admin-layout>
    <div class="px-4 py-8 sm:px-6 lg:px-8">
        <section class="mb-8 border border-amber-300/25 bg-black/55 p-5 shadow-[0_0_38px_rgba(251,191,36,0.08)]">
            <p class="terminal-label text-xs font-bold text-amber-200/75">// AUTHORIZATION CONFIG</p>
            <h1 class="mt-3 text-3xl font-black uppercase tracking-[0.14em] text-white sm:text-4xl">Auth JSON</h1>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-green-100/58">
                Update the public autho.json and opencode.json files used by Task AI. Each file is validated before it is saved.
            </p>
        </section>

        @if(session('status'))
            <div class="mb-6 border border-green-500/30 bg-green-500/10 px-4 py-3 text-sm font-semibold text-green-100">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 border border-red-400/35 bg-red-500/10 px-4 py-3 text-sm font-semibold text-red-100">
                {{ $errors->first() }}
            </div>
        @endif

        <section class="card mb-8 p-6">
            <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <h2 class="text-xl font-black uppercase tracking-[0.12em] text-white">File Editor</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-green-100/52">
                        Saving here writes directly to <span class="text-amber-100">public/autho.json</span> and <span class="text-amber-100">public/opencode.json</span>.
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <span class="{{ $authoExists ? 'badge-green' : 'badge-gray' }}">
                        {{ $authoExists ? 'CODEX FILE FOUND' : 'NEW CODEX FILE' }}
                    </span>
                    @if($authoUpdatedAt)
                        <span class="badge-amber">Codex {{ $authoUpdatedAt }}</span>
                    @endif
                    <span class="{{ $opencodeExists ? 'badge-green' : 'badge-gray' }}">
                        {{ $opencodeExists ? 'OPENCODE FILE FOUND' : 'NEW OPENCODE FILE' }}
                    </span>
                    @if($opencodeUpdatedAt)
                        <span class="badge-amber">OpenCode {{ $opencodeUpdatedAt }}</span>
                    @endif
                </div>
            </div>

            <form method="POST" action="{{ route('admin.authorization.update') }}" class="grid gap-4">
                @csrf
                <div>
                    <label for="autho_json" class="terminal-label block text-xs font-bold text-amber-200/75">autho.json</label>
                    <textarea id="autho_json" name="autho_json" rows="22" required spellcheck="false" class="input-field mt-2 leading-6">{{ old('autho_json', $authoJson) }}</textarea>
                </div>

                <div>
                    <label for="opencode_json" class="terminal-label block text-xs font-bold text-amber-200/75">opencode.json</label>
                    <textarea id="opencode_json" name="opencode_json" rows="16" required spellcheck="false" class="input-field mt-2 leading-6">{{ old('opencode_json', $opencodeJson) }}</textarea>
                </div>

                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="grid gap-1">
                        <p class="break-all text-xs leading-5 text-green-100/42">{{ $authoPath }}</p>
                        <p class="break-all text-xs leading-5 text-green-100/42">{{ $opencodePath }}</p>
                    </div>
                    <button type="submit" class="gradient-btn justify-center">Save JSON files</button>
                </div>
            </form>
        </section>

        <section class="card p-5">
            <p class="terminal-label text-xs font-bold text-cyan-300/75">// PUBLIC URL</p>
            <a href="{{ asset('autho.json') }}" target="_blank" rel="noopener" class="mt-2 block break-all text-sm font-bold text-cyan-100 hover:text-cyan-200">
                {{ asset('autho.json') }}
            </a>
            <a href="{{ asset('opencode.json') }}" target="_blank" rel="noopener" class="mt-2 block break-all text-sm font-bold text-cyan-100 hover:text-cyan-200">
                {{ asset('opencode.json') }}
            </a>
        </section>
    </div>
</x-admin-layout>
