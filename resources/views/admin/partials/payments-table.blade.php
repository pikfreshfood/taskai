<div class="overflow-x-auto">
    <table class="w-full divide-y divide-green-500/12" style="table-layout:auto">
        <thead class="bg-green-500/5">
            <tr>
                <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-black uppercase tracking-[0.18em] text-green-400/75">User</th>
                <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-black uppercase tracking-[0.18em] text-green-400/75">Plan</th>
                <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-black uppercase tracking-[0.18em] text-green-400/75">Amount</th>
                <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-black uppercase tracking-[0.18em] text-green-400/75">Status</th>
                <th class="whitespace-nowrap px-5 py-3 text-left text-xs font-black uppercase tracking-[0.18em] text-green-400/75">Reference</th>
                <th class="whitespace-nowrap px-5 py-3 text-right text-xs font-black uppercase tracking-[0.18em] text-green-400/75">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-green-500/10">
            @forelse($recentPayments as $payment)
                <tr class="transition hover:bg-green-500/5">
                    <td class="whitespace-nowrap px-5 py-4">
                        <p class="text-sm font-bold text-green-50">{{ $payment->user?->name ?? 'Deleted user' }}</p>
                        <p class="mt-1 text-xs text-green-100/45">{{ $payment->user?->email ?? 'No email' }}</p>
                    </td>
                    <td class="whitespace-nowrap px-5 py-4 text-sm text-green-100/70">{{ $payment->plan_name ?? ucfirst($payment->plan_code ?? 'Upgrade') }}</td>
                    <td class="whitespace-nowrap px-5 py-4 text-sm font-bold text-white">{{ $payment->currency }} {{ number_format($payment->amount / 100) }}</td>
                    <td class="whitespace-nowrap px-5 py-4">
                        @if($payment->status === 'paid')
                            <span class="badge-green">PAID</span>
                        @elseif($payment->status === 'pending')
                            <span class="badge-amber">PENDING</span>
                        @else
                            <span class="badge-gray">{{ strtoupper($payment->status) }}</span>
                        @endif
                    </td>
                    <td class="whitespace-nowrap px-5 py-4 text-xs text-green-100/45">{{ $payment->reference }}</td>
                    <td class="whitespace-nowrap px-5 py-4 text-right">
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
