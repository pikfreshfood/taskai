<x-admin-layout>
    <div class="px-4 py-8 sm:px-6 lg:px-8">
        <section class="mb-8 border border-green-500/25 bg-black/55 p-5 shadow-[0_0_38px_rgba(0,255,102,0.08)]">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="terminal-label text-xs font-bold text-green-400/75">// USER OPERATIONS</p>
                    <h1 class="mt-3 text-3xl font-black uppercase tracking-[0.14em] text-white sm:text-4xl">Users</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-green-100/58">
                        View Task AI accounts, payment activity, free-trial status, and active subscription time.
                    </p>
                </div>
                <div class="grid grid-cols-3 gap-2 text-center text-xs">
                    <div class="border border-green-500/25 bg-green-500/5 px-4 py-3">
                        <p class="text-green-400/70">USERS</p>
                        <p class="mt-1 text-lg font-black text-green-100">{{ $totalUsers }}</p>
                    </div>
                    <div class="border border-cyan-400/25 bg-cyan-400/5 px-4 py-3">
                        <p class="text-cyan-300/70">TASK AI</p>
                        <p class="mt-1 text-lg font-black text-cyan-100">{{ $taskAiUsers }}</p>
                    </div>
                    <div class="border border-amber-300/25 bg-amber-300/5 px-4 py-3">
                        <p class="text-amber-200/70">ACTIVE</p>
                        <p class="mt-1 text-lg font-black text-amber-100">{{ $activeTaskAiSubscriptions }}</p>
                    </div>
                </div>
            </div>
        </section>

        @if(session('status'))
            <div class="mb-6 border border-green-400/25 bg-green-500/8 px-5 py-3 text-sm font-bold text-green-100">
                {{ session('status') }}
            </div>
        @endif

        <section class="card overflow-hidden">
            <div class="border-b border-green-500/18 px-5 py-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-xl font-black uppercase tracking-[0.12em] text-white">User Records</h2>
                        <p class="mt-1 text-xs text-green-100/48">Showing 10 users per page.</p>
                    </div>
                    <form method="GET" action="{{ route('admin.users') }}" class="flex gap-2">
                        <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search name or email..." class="w-56 border border-green-500/30 bg-black/55 px-3 py-2 font-mono text-sm text-green-100 placeholder:text-green-700/70 focus:border-cyan-300 focus:ring-1 focus:ring-cyan-300">
                        <button type="submit" class="border border-green-400/35 bg-green-500/5 px-3 py-2 text-xs font-black uppercase tracking-[0.16em] text-green-300 transition hover:bg-green-500/15 hover:text-green-100">Search</button>
                        @if(request('search'))
                            <a href="{{ route('admin.users') }}" class="border border-red-400/35 bg-red-500/5 px-3 py-2 text-xs font-black uppercase tracking-[0.16em] text-red-300 transition hover:bg-red-500/15 hover:text-red-100">Clear</a>
                        @endif
                    </form>
                </div>
            </div>

            <div id="users-table-wrapper">
                @include('admin.partials.users-table')
            </div>
        </section>
    </div>
</x-admin-layout>
