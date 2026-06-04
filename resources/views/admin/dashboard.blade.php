<x-admin-layout>
    <div class="px-4 py-8 sm:px-6 lg:px-8">
        <section class="mb-8 border border-green-500/25 bg-black/55 p-5 shadow-[0_0_38px_rgba(0,255,102,0.08)]">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="terminal-label text-xs font-bold text-green-400/75">// COMMAND AUTHORITY</p>
                    <h1 class="mt-3 text-3xl font-black uppercase tracking-[0.14em] text-white sm:text-4xl">Dashboard</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-green-100/58">
                        Overview of Task AI users, upgrades, payments, revenue, and the latest desktop update status.
                    </p>
                </div>
                <div class="grid grid-cols-3 gap-2 text-center text-xs">
                    <div class="border border-green-500/25 bg-green-500/5 px-4 py-3">
                        <p class="text-green-400/70">USERS</p>
                        <p class="mt-1 text-lg font-black text-green-100">{{ $totalUsers }}</p>
                    </div>
                    <div class="border border-cyan-400/25 bg-cyan-400/5 px-4 py-3">
                        <p class="text-cyan-300/70">ACTIVE</p>
                        <p class="mt-1 text-lg font-black text-cyan-100">{{ $activeTaskAiSubscriptions }}</p>
                    </div>
                    <div class="border border-amber-300/25 bg-amber-300/5 px-4 py-3">
                        <p class="text-amber-200/70">PENDING</p>
                        <p class="mt-1 text-lg font-black text-amber-100">{{ $pendingPayments }}</p>
                    </div>
                </div>
            </div>
        </section>

        @if(session('status'))
            <div class="mb-6 border border-green-500/30 bg-green-500/10 px-4 py-3 text-sm font-semibold text-green-100">
                {{ session('status') }}
            </div>
        @endif

        <section class="mb-8 grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            <div class="card p-5">
                <p class="terminal-label text-xs text-green-400/70">Total Users</p>
                <p class="mt-3 text-3xl font-black text-white">{{ $totalUsers }}</p>
                <p class="mt-2 text-xs text-green-100/45">Registered portal accounts.</p>
            </div>
            <div class="card p-5">
                <p class="terminal-label text-xs text-cyan-300/70">Task AI Users</p>
                <p class="mt-3 text-3xl font-black text-cyan-100">{{ $taskAiUsers }}</p>
                <p class="mt-2 text-xs text-cyan-100/45">Users with payment or upgrade activity.</p>
            </div>
            <div class="card p-5">
                <p class="terminal-label text-xs text-green-400/70">Active Upgrades</p>
                <p class="mt-3 text-3xl font-black text-green-100">{{ $activeTaskAiSubscriptions }}</p>
                <p class="mt-2 text-xs text-green-100/45">Subscriptions currently valid.</p>
            </div>
            <div class="card p-5">
                <p class="terminal-label text-xs text-amber-200/70">Revenue</p>
                <p class="mt-3 text-3xl font-black text-amber-100">NGN {{ number_format($totalRevenue / 100) }}</p>
                <p class="mt-2 text-xs text-amber-100/45">Confirmed paid transactions.</p>
            </div>
            <div class="card p-5">
                <p class="terminal-label text-xs text-cyan-300/70">Downloads</p>
                <p class="mt-3 text-3xl font-black text-cyan-100">{{ number_format($totalDownloads) }}</p>
                <p class="mt-2 text-xs text-cyan-100/45">Tracked installer downloads.</p>
            </div>
        </section>

        <section class="mb-8 grid gap-4 lg:grid-cols-3">
            <a href="{{ route('admin.updates') }}" class="card block p-6 transition hover:border-cyan-300/45">
                <p class="terminal-label text-xs font-bold text-cyan-300/75">// UPDATE NODE</p>
                <h2 class="mt-3 text-xl font-black uppercase tracking-[0.12em] text-white">App Updates</h2>
                <p class="mt-3 text-sm leading-6 text-green-100/52">Publish a new desktop version and download link for users.</p>
                <p class="mt-5 text-xs text-cyan-100/55">
                    Current: {{ $latestAppUpdate ? 'v'.$latestAppUpdate->version : 'No update published' }}
                </p>
            </a>

            <a href="{{ route('admin.payments') }}" class="card block p-6 transition hover:border-green-300/45">
                <p class="terminal-label text-xs font-bold text-green-400/75">// PAYMENT NODE</p>
                <h2 class="mt-3 text-xl font-black uppercase tracking-[0.12em] text-white">Payments</h2>
                <p class="mt-3 text-sm leading-6 text-green-100/52">Review transactions and manually approve confirmed payments.</p>
                <p class="mt-5 text-xs text-green-100/55">{{ $pendingPayments }} pending payment(s)</p>
            </a>

            <a href="{{ route('admin.authorization') }}" class="card block p-6 transition hover:border-amber-300/45">
                <p class="terminal-label text-xs font-bold text-amber-200/75">// AUTHO FILE</p>
                <h2 class="mt-3 text-xl font-black uppercase tracking-[0.12em] text-white">Auth JSON</h2>
                <p class="mt-3 text-sm leading-6 text-green-100/52">Update the public Codex and OpenCode configuration from the admin console.</p>
                <p class="mt-5 text-xs text-amber-100/55">Writes to /autho.json and /opencode.json</p>
            </a>
        </section>

        <section class="mb-8 grid gap-4 lg:grid-cols-3">
            <div class="card p-6">
                <p class="terminal-label text-xs font-bold text-amber-200/75">// SYSTEM SNAPSHOT</p>
                <h2 class="mt-3 text-xl font-black uppercase tracking-[0.12em] text-white">Payment State</h2>
                <div class="mt-4 grid grid-cols-3 gap-2 text-center text-xs">
                    <div class="border border-green-500/20 bg-green-500/5 p-3">
                        <p class="text-green-300/65">PAID</p>
                        <p class="mt-1 text-lg font-black text-green-100">{{ $paidPayments }}</p>
                    </div>
                    <div class="border border-amber-300/20 bg-amber-300/5 p-3">
                        <p class="text-amber-200/65">WAIT</p>
                        <p class="mt-1 text-lg font-black text-amber-100">{{ $pendingPayments }}</p>
                    </div>
                    <div class="border border-red-300/20 bg-red-300/5 p-3">
                        <p class="text-red-200/65">FAIL</p>
                        <p class="mt-1 text-lg font-black text-red-100">{{ $failedPayments }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="card overflow-hidden">
            <div class="border-b border-green-500/18 px-5 py-4">
                <p class="terminal-label text-xs font-bold text-green-400/75">// RECENT ACTIVITY</p>
                <h2 class="mt-2 text-xl font-black uppercase tracking-[0.12em] text-white">Latest Payments</h2>
            </div>
            <div class="divide-y divide-green-500/10">
                @forelse($recentPayments as $payment)
                    <div class="grid gap-2 px-5 py-4 md:grid-cols-[1fr_auto_auto] md:items-center">
                        <div>
                            <p class="text-sm font-bold text-green-50">{{ $payment->user?->name ?? 'Deleted user' }}</p>
                            <p class="mt-1 text-xs text-green-100/45">{{ $payment->reference }}</p>
                        </div>
                        <p class="text-sm font-bold text-white">{{ $payment->currency }} {{ number_format($payment->amount / 100) }}</p>
                        @if($payment->status === 'paid')
                            <span class="badge-green">PAID</span>
                        @elseif($payment->status === 'pending')
                            <span class="badge-amber">PENDING</span>
                        @else
                            <span class="badge-gray">{{ strtoupper($payment->status) }}</span>
                        @endif
                    </div>
                @empty
                    <div class="px-5 py-10 text-center text-sm text-green-100/42">No Task AI payments yet.</div>
                @endforelse
            </div>
        </section>
    </div>
</x-admin-layout>
