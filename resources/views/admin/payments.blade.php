<x-admin-layout>
    <div class="px-4 py-8 sm:px-6 lg:px-8">
        <section class="mb-8 border border-green-500/25 bg-black/55 p-5 shadow-[0_0_38px_rgba(0,255,102,0.08)]">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="terminal-label text-xs font-bold text-green-400/75">// PAYMENT OPERATIONS</p>
                    <h1 class="mt-3 text-3xl font-black uppercase tracking-[0.14em] text-white sm:text-4xl">Payments</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-green-100/58">
                        Review payment records and manually approve pending or failed payments only after confirming the transaction.
                    </p>
                </div>
                <a href="{{ route('admin.payments') }}" class="border border-cyan-400/25 bg-cyan-400/5 px-4 py-2 text-xs font-bold uppercase tracking-[0.18em] text-cyan-300 transition hover:bg-cyan-400/12">Refresh</a>
            </div>
        </section>

        @if(session('status'))
            <div class="mb-6 border border-green-500/30 bg-green-500/10 px-4 py-3 text-sm font-semibold text-green-100">
                {{ session('status') }}
            </div>
        @endif

        <section class="mb-8 grid gap-4 md:grid-cols-4">
            <div class="card p-5">
                <p class="terminal-label text-xs text-green-400/70">All Payments</p>
                <p class="mt-3 text-2xl font-black text-white">{{ $totalPayments }}</p>
            </div>
            <div class="card p-5">
                <p class="terminal-label text-xs text-green-400/70">Paid</p>
                <p class="mt-3 text-2xl font-black text-green-100">{{ $paidPayments }}</p>
            </div>
            <div class="card p-5">
                <p class="terminal-label text-xs text-amber-200/70">Pending</p>
                <p class="mt-3 text-2xl font-black text-amber-100">{{ $pendingPayments }}</p>
            </div>
            <div class="card p-5">
                <p class="terminal-label text-xs text-red-300/70">Failed</p>
                <p class="mt-3 text-2xl font-black text-red-100">{{ $failedPayments }}</p>
            </div>
        </section>

        <section class="card overflow-hidden">
            <div class="border-b border-green-500/18 px-5 py-4">
                <h2 class="text-xl font-black uppercase tracking-[0.12em] text-white">Payment Records</h2>
                <p class="mt-1 text-xs text-green-100/48">Showing 10 payments per page.</p>
            </div>

            <div id="payments-table-wrapper">
                @include('admin.partials.payments-table')
            </div>
        </section>
    </div>
</x-admin-layout>
