<x-admin-layout>
    <div class="px-4 py-8 sm:px-6 lg:px-8">
        <section class="mb-8 border border-cyan-400/25 bg-black/55 p-5 shadow-[0_0_38px_rgba(0,255,255,0.08)]">
            <p class="terminal-label text-xs font-bold text-cyan-300/75">// APP UPDATE PUSH</p>
            <h1 class="mt-3 text-3xl font-black uppercase tracking-[0.14em] text-white sm:text-4xl">App Updates</h1>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-green-100/58">
                Publish desktop app versions and download links. The app shows users the newest version once until you push a newer update.
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
                    <h2 class="text-xl font-black uppercase tracking-[0.12em] text-white">Desktop Update Control</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-green-100/52">
                        Enter a higher version than the current desktop app, then paste your EXE download link.
                    </p>
                </div>
                @if($latestAppUpdate)
                    <span class="{{ $latestAppUpdate->is_active ? 'badge-green' : 'badge-gray' }}">
                        {{ $latestAppUpdate->is_active ? 'ACTIVE' : 'INACTIVE' }} v{{ $latestAppUpdate->version }}
                    </span>
                @endif
            </div>

            <form method="POST" action="{{ route('admin.taskai-update.publish') }}" class="grid gap-4">
                @csrf
                <div class="grid gap-4 lg:grid-cols-2">
                    <div>
                        <label for="version" class="terminal-label block text-xs font-bold text-green-400/75">Version</label>
                        <input id="version" name="version" type="text" required placeholder="1.0.1" value="{{ old('version', $latestAppUpdate?->version) }}" class="input-field mt-2">
                    </div>
                    <div>
                        <label for="download_url" class="terminal-label block text-xs font-bold text-cyan-300/75">Download Link</label>
                        <input id="download_url" name="download_url" type="url" placeholder="https://example.com/taskai.exe" value="{{ old('download_url', $latestAppUpdate?->download_url) }}" class="input-field mt-2">
                    </div>
                </div>

                <div>
                    <label for="release_notes" class="terminal-label block text-xs font-bold text-green-400/75">Update Message</label>
                    <textarea id="release_notes" name="release_notes" rows="5" placeholder="Tell users what changed in this update." class="input-field mt-2">{{ old('release_notes', $latestAppUpdate?->release_notes) }}</textarea>
                </div>

                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex flex-wrap gap-4">
                        <label class="inline-flex items-center gap-2 text-sm font-semibold text-green-100/68">
                            <input type="checkbox" name="is_active" value="1" class="border-green-500/40 bg-black text-green-500 focus:ring-green-500" @checked(old('is_active', $latestAppUpdate?->is_active ?? true))>
                            Active update
                        </label>
                        <label class="inline-flex items-center gap-2 text-sm font-semibold text-green-100/68">
                            <input type="checkbox" name="is_required" value="1" class="border-green-500/40 bg-black text-green-500 focus:ring-green-500" @checked(old('is_required', $latestAppUpdate?->is_required ?? false))>
                            Required update
                        </label>
                    </div>
                    <button type="submit" class="gradient-btn justify-center">Save Update</button>
                </div>
            </form>
        </section>

        <section class="card overflow-hidden">
            <div class="border-b border-green-500/18 px-5 py-4">
                <p class="terminal-label text-xs font-bold text-green-400/75">// UPDATE HISTORY</p>
                <h2 class="mt-2 text-xl font-black uppercase tracking-[0.12em] text-white">Published Versions</h2>
                <p class="mt-1 text-xs text-green-100/48">Edit any row and press Save to update that version.</p>
            </div>
            <div class="divide-y divide-green-500/10">
                @forelse($appUpdates as $update)
                    <div class="grid gap-4 px-5 py-5 xl:grid-cols-[130px_1.2fr_1.4fr_170px_120px] xl:items-start">
                        <form id="update-row-{{ $update->id }}" method="POST" action="{{ route('admin.taskai-update.update', $update) }}" class="contents">
                            @csrf
                            @method('PATCH')
                        </form>
                        <div>
                            <label for="version-{{ $update->id }}" class="terminal-label block text-[10px] font-bold text-green-400/75">Version</label>
                            <input form="update-row-{{ $update->id }}" id="version-{{ $update->id }}" name="version" type="text" required value="{{ old('version', $update->version) }}" class="input-field mt-2 py-2">
                            <p class="mt-2 text-[11px] text-green-100/38">{{ $update->published_at?->format('M j, Y g:i A') ?? 'Not active' }}</p>
                        </div>

                        <div>
                            <label for="download-url-{{ $update->id }}" class="terminal-label block text-[10px] font-bold text-cyan-300/75">Download Link</label>
                            <input form="update-row-{{ $update->id }}" id="download-url-{{ $update->id }}" name="download_url" type="url" value="{{ old('download_url', $update->download_url) }}" placeholder="https://example.com/taskai.exe" class="input-field mt-2 py-2">
                        </div>

                        <div>
                            <label for="release-notes-{{ $update->id }}" class="terminal-label block text-[10px] font-bold text-green-400/75">Message</label>
                            <textarea form="update-row-{{ $update->id }}" id="release-notes-{{ $update->id }}" name="release_notes" rows="3" class="input-field mt-2 py-2">{{ old('release_notes', $update->release_notes) }}</textarea>
                        </div>

                        <div class="grid gap-3 pt-1">
                            <label class="inline-flex items-center gap-2 text-sm font-semibold text-green-100/68">
                                <input form="update-row-{{ $update->id }}" type="checkbox" name="is_active" value="1" class="border-green-500/40 bg-black text-green-500 focus:ring-green-500" @checked(old('is_active', $update->is_active))>
                                Active
                            </label>
                            <label class="inline-flex items-center gap-2 text-sm font-semibold text-green-100/68">
                                <input form="update-row-{{ $update->id }}" type="checkbox" name="is_required" value="1" class="border-green-500/40 bg-black text-green-500 focus:ring-green-500" @checked(old('is_required', $update->is_required))>
                                Required
                            </label>
                            <div class="flex flex-wrap gap-2">
                                <span class="{{ $update->is_active ? 'badge-green' : 'badge-gray' }}">{{ $update->is_active ? 'ACTIVE' : 'OFF' }}</span>
                                @if($update->is_required)
                                    <span class="badge-amber">REQUIRED</span>
                                @endif
                            </div>
                        </div>

                        <div class="grid gap-2 xl:pt-7">
                            <button form="update-row-{{ $update->id }}" type="submit" class="w-full border border-green-400/45 bg-green-500/10 px-3 py-2 text-xs font-black uppercase tracking-[0.16em] text-green-100 transition hover:bg-green-400 hover:text-black">
                                Save
                            </button>
                            <form method="POST" action="{{ route('admin.taskai-update.delete', $update) }}" onsubmit="return confirm('Delete Task AI update v{{ $update->version }}? This cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full border border-red-400/45 bg-red-500/10 px-3 py-2 text-xs font-black uppercase tracking-[0.16em] text-red-100 transition hover:bg-red-400 hover:text-black">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-10 text-center text-sm text-green-100/42">No app update has been published yet.</div>
                @endforelse
            </div>
        </section>
    </div>
</x-admin-layout>
