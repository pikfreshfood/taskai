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

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-green-500/12">
                    <thead class="bg-green-500/5">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-black uppercase tracking-[0.18em] text-green-400/75">User</th>
                            <th class="px-5 py-3 text-left text-xs font-black uppercase tracking-[0.18em] text-green-400/75">Plan</th>
                            <th class="px-5 py-3 text-left text-xs font-black uppercase tracking-[0.18em] text-green-400/75">Amount</th>
                            <th class="px-5 py-3 text-left text-xs font-black uppercase tracking-[0.18em] text-green-400/75">Status</th>
                            <th class="px-5 py-3 text-left text-xs font-black uppercase tracking-[0.18em] text-green-400/75">Reference</th>
                            <th class="px-5 py-3 text-right text-xs font-black uppercase tracking-[0.18em] text-green-400/75">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-green-500/10">
                        @forelse($recentPayments as $payment)
                            <tr class="transition hover:bg-green-500/5">
                                <td class="px-5 py-4">
                                    <p class="text-sm font-bold text-green-50">{{ $payment->user?->name ?? 'Deleted user' }}</p>
                                    <p class="mt-1 text-xs text-green-100/45">{{ $payment->user?->email ?? 'No email' }}</p>
                                </td>
                                <td class="px-5 py-4 text-sm text-green-100/70">{{ $payment->plan_name ?? ucfirst($payment->plan_code ?? 'Upgrade') }}</td>
                                <td class="px-5 py-4 text-sm font-bold text-white">{{ $payment->currency }} {{ number_format($payment->amount / 100) }}</td>
                                <td class="px-5 py-4">
                                    @if($payment->status === 'paid')
                                        <span class="badge-green">PAID</span>
                                    @elseif($payment->status === 'pending')
                                        <span class="badge-amber">PENDING</span>
                                    @else
                                        <span class="badge-gray">{{ strtoupper($payment->status) }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-xs text-green-100/45">{{ $payment->reference }}</td>
                                <td class="px-5 py-4 text-right">
                                    @if($payment->status !== 'paid')
                                        <form action="{{ route('admin.taskai-payments.approve', $payment) }}" method="POST" onsubmit="return confirm('Approve this payment and upgrade the user?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="border border-green-400/35 bg-green-500/10 px-3 py-2 text-xs font-bold uppercase tracking-[0.14em] text-green-200 transition hover:bg-green-400 hover:text-black">Approve</button>
                                        </form>
                                    @else
                                        <span class="text-xs font-bold uppercase tracking-[0.16em] text-green-300/55">Approved</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center text-sm text-green-100/42">No Task AI payments yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @include('admin.partials.pagination', ['paginator' => $recentPayments])
        </section>
    </div>
</x-admin-layout>
