<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-green-500/12">
        <thead class="bg-green-500/5">
            <tr>
                <th class="px-5 py-3 text-left text-xs font-black uppercase tracking-[0.18em] text-green-400/75">User</th>
                <th class="px-5 py-3 text-left text-xs font-black uppercase tracking-[0.18em] text-green-400/75">Role</th>
                <th class="px-5 py-3 text-left text-xs font-black uppercase tracking-[0.18em] text-green-400/75">Subscription</th>
                <th class="px-5 py-3 text-left text-xs font-black uppercase tracking-[0.18em] text-green-400/75">Expires</th>
                <th class="px-5 py-3 text-left text-xs font-black uppercase tracking-[0.18em] text-green-400/75">Payments</th>
                <th class="px-5 py-3 text-left text-xs font-black uppercase tracking-[0.18em] text-green-400/75">Joined</th>
                <th class="px-5 py-3 text-right text-xs font-black uppercase tracking-[0.18em] text-green-400/75">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-green-500/10">
            @forelse($users as $user)
                @php
                    $isUpgraded = $user->taskai_upgraded_at && (! $user->taskai_upgrade_expires_at || $user->taskai_upgrade_expires_at->isFuture());
                    $trialExpiresAt = $user->created_at?->copy()->addDay();
                    $trialActive = ! $isUpgraded && $trialExpiresAt && $trialExpiresAt->isFuture();
                @endphp
                <tr class="transition hover:bg-green-500/5">
                    <td class="px-5 py-4">
                        <p class="text-sm font-bold text-green-50">{{ $user->name }}</p>
                        <p class="mt-1 text-xs text-green-100/45">{{ $user->email }}</p>
                    </td>
                    <td class="px-5 py-4">
                        @if($user->is_admin)
                            <span class="badge-amber">ADMIN</span>
                        @else
                            <span class="badge-gray">USER</span>
                        @endif
                    </td>
                    <td class="px-5 py-4">
                        @if($isUpgraded)
                            <span class="badge-green">UPGRADED</span>
                        @elseif($trialActive)
                            <span class="badge-amber">FREE TRIAL</span>
                        @else
                            <span class="badge-gray">EXPIRED</span>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-sm text-green-100/62">
                        @if($isUpgraded && $user->taskai_upgrade_expires_at)
                            {{ $user->taskai_upgrade_expires_at->format('M j, Y g:i A') }}
                        @elseif($isUpgraded)
                            No expiry
                        @elseif($trialExpiresAt)
                            Trial: {{ $trialExpiresAt->format('M j, Y g:i A') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-5 py-4 text-sm font-bold text-white">{{ $user->task_ai_payments_count }}</td>
                    <td class="px-5 py-4 text-sm text-green-100/62">{{ $user->created_at?->format('M j, Y') }}</td>
                    <td class="px-5 py-4 text-right">
                        @if(auth()->id() === $user->id)
                            <span class="text-xs font-bold uppercase tracking-[0.16em] text-green-100/35">Current</span>
                        @else
                            <!-- Upgrade Button -->
                            <form method="POST" action="{{ route('admin.users.upgrade', $user) }}" onsubmit="return confirm('Upgrade this user to Task AI Pro? This will upgrade their subscription.')">
                                @csrf
                                @method('PATCH')
                                <select name="plan_code" class="border border-green-500/35 bg-green-500/5 px-2 py-1 text-xs font-black uppercase tracking-[0.16em] text-green-300 focus:outline-none focus:ring-2 focus:ring-green-500">
                                    @foreach($plans as $plan)
                                        <option value="{{ $plan->code }}">{{ $plan->name }} ({{ $plan->duration_days }} day{{ $plan->duration_days !== 1 ? 's' : '' }})</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="ml-2 border border-green-400/35 bg-green-500/5 px-3 py-2 text-xs font-black uppercase tracking-[0.16em] text-green-300 transition hover:bg-green-500/15 hover:text-green-100">
                                    Upgrade
                                </button>
                            </form>

                            <!-- Delete Button -->
                            <form method="POST" action="{{ route('admin.users.delete', $user) }}" onsubmit="return confirm('Delete this user? This will remove the account, API tokens, and payment records. This cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="ml-2 border border-red-400/35 bg-red-500/5 px-3 py-2 text-xs font-black uppercase tracking-[0.16em] text-red-300 transition hover:bg-red-500/15 hover:text-red-100">
                                    Delete
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-5 py-12 text-center text-sm text-green-100/42">{{ request('search') ? 'No users match your search.' : 'No users yet.' }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@include('admin.partials.pagination', ['paginator' => $users])
