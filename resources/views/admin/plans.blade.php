<x-admin-layout>
    <div class="px-4 py-8 sm:px-6 lg:px-8">
        <section class="mb-8 border border-green-500/25 bg-black/55 p-5 shadow-[0_0_38px_rgba(0,255,102,0.08)]">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="terminal-label text-xs font-bold text-green-400/75">// PLAN MANAGEMENT</p>
                    <h1 class="mt-3 text-3xl font-black uppercase tracking-[0.14em] text-white sm:text-4xl">Plans</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-green-100/58">
                        Create, edit, and remove subscription plans. These are sync'd to the Task AI desktop app on startup.
                    </p>
                </div>
            </div>
        </section>

        @if(session('status'))
            <div class="mb-6 border border-green-400/25 bg-green-500/8 px-5 py-3 text-sm font-bold text-green-100">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 border border-red-400/25 bg-red-500/8 px-5 py-3 text-sm font-bold text-red-100">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Add New Plan -->
        <section class="mb-8 border border-green-500/20 bg-black/40 p-5">
            <h2 class="mb-4 text-lg font-black uppercase tracking-[0.12em] text-white">New Plan</h2>
            <form method="POST" action="{{ route('admin.plans.store') }}" class="grid gap-4 sm:grid-cols-5">
                @csrf
                <div>
                    <label class="mb-1 block text-[10px] font-bold uppercase tracking-[0.16em] text-green-400/70">Code</label>
                    <input type="text" name="code" placeholder="monthly" required maxlength="60"
                           class="w-full border border-green-500/35 bg-green-500/5 px-3 py-2 text-sm text-green-100 placeholder-green-100/25 focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-bold uppercase tracking-[0.16em] text-green-400/70">Name</label>
                    <input type="text" name="name" placeholder="Monthly" required maxlength="120"
                           class="w-full border border-green-500/35 bg-green-500/5 px-3 py-2 text-sm text-green-100 placeholder-green-100/25 focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-bold uppercase tracking-[0.16em] text-green-400/70">Price (NGN)</label>
                    <input type="number" name="price" placeholder="9.99" step="0.01" min="0" required
                           class="w-full border border-green-500/35 bg-green-500/5 px-3 py-2 text-sm text-green-100 placeholder-green-100/25 focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-bold uppercase tracking-[0.16em] text-green-400/70">Days</label>
                    <input type="number" name="duration_days" placeholder="30" min="1" max="3650" required
                           class="w-full border border-green-500/35 bg-green-500/5 px-3 py-2 text-sm text-green-100 placeholder-green-100/25 focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <div>
                    <label class="mb-1 block text-[10px] font-bold uppercase tracking-[0.16em] text-green-400/70">Sort</label>
                    <input type="number" name="sort_order" placeholder="0" min="0"
                           class="w-full border border-green-500/35 bg-green-500/5 px-3 py-2 text-sm text-green-100 placeholder-green-100/25 focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <div class="sm:col-span-5">
                    <button type="submit" class="border border-green-400/35 bg-green-500/8 px-6 py-2 text-xs font-black uppercase tracking-[0.16em] text-green-300 transition hover:bg-green-500/18 hover:text-green-100">
                        Create Plan
                    </button>
                </div>
            </form>
        </section>

        <!-- Existing Plans -->
        <section class="border border-green-500/20 bg-black/40">
            <div class="border-b border-green-500/18 px-5 py-4">
                <h2 class="text-xl font-black uppercase tracking-[0.12em] text-white">All Plans ({{ $plans->count() }})</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-green-500/12">
                    <thead class="bg-green-500/5">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-black uppercase tracking-[0.18em] text-green-400/75">Code</th>
                            <th class="px-5 py-3 text-left text-xs font-black uppercase tracking-[0.18em] text-green-400/75">Name</th>
                            <th class="px-5 py-3 text-left text-xs font-black uppercase tracking-[0.18em] text-green-400/75">Price</th>
                            <th class="px-5 py-3 text-left text-xs font-black uppercase tracking-[0.18em] text-green-400/75">Duration</th>
                            <th class="px-5 py-3 text-left text-xs font-black uppercase tracking-[0.18em] text-green-400/75">Sort</th>
                            <th class="px-5 py-3 text-right text-xs font-black uppercase tracking-[0.18em] text-green-400/75">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-green-500/10">
                        @forelse($plans as $plan)
                            <tr class="transition hover:bg-green-500/5">
                                <td class="px-5 py-4 text-sm font-bold text-green-50">{{ $plan->code }}</td>
                                <td class="px-5 py-4 text-sm text-green-100/62">{{ $plan->name }}</td>
                                <td class="px-5 py-4 text-sm text-green-100/62">₦{{ number_format($plan->price, 2) }}</td>
                                <td class="px-5 py-4 text-sm text-green-100/62">{{ $plan->duration_days }} day{{ $plan->duration_days !== 1 ? 's' : '' }}</td>
                                <td class="px-5 py-4 text-sm text-green-100/62">{{ $plan->sort_order }}</td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Edit form (inline) -->
                                        <form method="POST" action="{{ route('admin.plans.update', $plan) }}" class="flex flex-wrap items-center gap-2">
                                            @csrf
                                            @method('PUT')
                                            <input type="text" name="name" value="{{ $plan->name }}" required maxlength="120"
                                                   class="w-24 border border-green-500/25 bg-green-500/5 px-2 py-1 text-xs text-green-100 placeholder-green-100/25 focus:outline-none focus:ring-1 focus:ring-green-500">
                                            <input type="number" name="price" value="{{ $plan->price }}" step="0.01" min="0" required
                                                   class="w-20 border border-green-500/25 bg-green-500/5 px-2 py-1 text-xs text-green-100 placeholder-green-100/25 focus:outline-none focus:ring-1 focus:ring-green-500">
                                            <input type="number" name="duration_days" value="{{ $plan->duration_days }}" min="1" max="3650" required
                                                   class="w-16 border border-green-500/25 bg-green-500/5 px-2 py-1 text-xs text-green-100 placeholder-green-100/25 focus:outline-none focus:ring-1 focus:ring-green-500">
                                            <input type="number" name="sort_order" value="{{ $plan->sort_order }}" min="0"
                                                   class="w-14 border border-green-500/25 bg-green-500/5 px-2 py-1 text-xs text-green-100 placeholder-green-100/25 focus:outline-none focus:ring-1 focus:ring-green-500">
                                            <button type="submit" class="border border-cyan-400/35 bg-cyan-500/5 px-2 py-1 text-[10px] font-black uppercase tracking-[0.16em] text-cyan-300 transition hover:bg-cyan-500/15 hover:text-cyan-100">
                                                Save
                                            </button>
                                        </form>
                                        <!-- Delete -->
                                        <form method="POST" action="{{ route('admin.plans.destroy', $plan) }}" onsubmit="return confirm('Delete plan {{ $plan->code }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="border border-red-400/35 bg-red-500/5 px-2 py-1 text-[10px] font-black uppercase tracking-[0.16em] text-red-300 transition hover:bg-red-500/15 hover:text-red-100">
                                                Del
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center text-sm text-green-100/42">No plans created yet. Use the form above to add one.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-admin-layout>
