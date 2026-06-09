<x-admin-layout>
    <div class="px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col gap-4 border-b border-green-500/20 pb-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="terminal-label text-xs font-bold text-green-400/70">admin broadcast</p>
                <h1 class="mt-3 text-3xl font-black uppercase tracking-[0.14em] text-white">Bulk Email</h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-green-100/55">
                    Send reminders, update notices, payment prompts, and account messages to Task AI users.
                </p>
            </div>
        </div>

        @if (session('status'))
            <div class="mb-6 border border-green-400/25 bg-green-500/10 px-4 py-3 text-sm font-bold text-green-100">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 border border-red-400/30 bg-red-500/10 px-4 py-3 text-sm text-red-100">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-[1fr_360px]">
            <form method="POST" action="{{ route('admin.bulk-email.send') }}" class="card p-6">
                @csrf

                <div class="grid gap-5">
                    <div>
                        <label for="audience" class="terminal-label block text-xs font-bold text-cyan-300/75">Audience</label>
                        <select id="audience" name="audience" required class="input-field mt-2">
                            <option value="all" @selected(old('audience') === 'all')>All users ({{ number_format($recipientCounts['all']) }})</option>
                            <option value="free" @selected(old('audience') === 'free')>Free users ({{ number_format($recipientCounts['free']) }})</option>
                            <option value="active" @selected(old('audience') === 'active')>Active subscribers ({{ number_format($recipientCounts['active']) }})</option>
                            <option value="expired" @selected(old('audience') === 'expired')>Expired subscribers ({{ number_format($recipientCounts['expired']) }})</option>
                        </select>
                    </div>

                    <div>
                        <label for="subject" class="terminal-label block text-xs font-bold text-cyan-300/75">Subject</label>
                        <input id="subject" name="subject" type="text" maxlength="180" value="{{ old('subject') }}" required
                            placeholder="Task AI update notice"
                            class="input-field mt-2">
                    </div>

                    <div>
                        <label for="message" class="terminal-label block text-xs font-bold text-cyan-300/75">Message</label>
                        <textarea id="message" name="message" rows="13" maxlength="10000" required
                            placeholder="Write the email message here..."
                            class="input-field mt-2 resize-y">{{ old('message') }}</textarea>
                    </div>

                    <button type="submit"
                        onclick="return confirm('Send this email to the selected users?')"
                        class="border border-green-400 bg-green-500/10 px-5 py-3 text-sm font-black uppercase tracking-[0.22em] text-green-200 shadow-[0_0_28px_rgba(0,255,102,0.12)] transition hover:bg-green-400 hover:text-black">
                        Send Bulk Email
                    </button>
                </div>
            </form>

            <aside class="grid gap-4 content-start">
                <div class="card p-5">
                    <p class="terminal-label text-xs font-bold text-green-400/70">recipients</p>
                    <div class="mt-4 grid gap-3 text-sm text-green-100/70">
                        <div class="flex justify-between border-b border-green-500/10 pb-2">
                            <span>All users</span>
                            <strong class="text-green-100">{{ number_format($recipientCounts['all']) }}</strong>
                        </div>
                        <div class="flex justify-between border-b border-green-500/10 pb-2">
                            <span>Free users</span>
                            <strong class="text-green-100">{{ number_format($recipientCounts['free']) }}</strong>
                        </div>
                        <div class="flex justify-between border-b border-green-500/10 pb-2">
                            <span>Active subscribers</span>
                            <strong class="text-green-100">{{ number_format($recipientCounts['active']) }}</strong>
                        </div>
                        <div class="flex justify-between">
                            <span>Expired subscribers</span>
                            <strong class="text-green-100">{{ number_format($recipientCounts['expired']) }}</strong>
                        </div>
                    </div>
                </div>

                <div class="border border-cyan-400/20 bg-cyan-400/5 p-5 text-xs leading-6 text-cyan-100/65">
                    <span class="font-bold text-cyan-300">SMTP:</span>
                    messages use the configured Task AI mail account. Keep messages short and send only useful updates to avoid spam complaints.
                </div>
            </aside>
        </div>
    </div>
</x-admin-layout>
