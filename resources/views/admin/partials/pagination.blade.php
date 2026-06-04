@if($paginator->hasPages())
    <div class="pagination flex flex-col gap-3 border-t border-green-500/12 px-5 py-4 md:flex-row md:items-center md:justify-between">
        <p class="text-xs text-green-100/45">
            Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} records
        </p>
        <div class="flex flex-wrap items-center gap-2">
            @if($paginator->onFirstPage())
                <span class="border border-green-500/12 px-3 py-2 text-xs font-bold uppercase tracking-[0.16em] text-green-100/25">Prev</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="border border-green-500/25 bg-green-500/5 px-3 py-2 text-xs font-bold uppercase tracking-[0.16em] text-green-200 transition hover:bg-green-400 hover:text-black">Prev</a>
            @endif

            @foreach($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
                @if($page === $paginator->currentPage())
                    <span class="border border-green-400 bg-green-500/15 px-3 py-2 text-xs font-black text-green-100">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="border border-green-500/18 bg-black/35 px-3 py-2 text-xs font-bold text-green-100/62 transition hover:border-green-400/45 hover:text-green-100">{{ $page }}</a>
                @endif
            @endforeach

            @if($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="border border-green-500/25 bg-green-500/5 px-3 py-2 text-xs font-bold uppercase tracking-[0.16em] text-green-200 transition hover:bg-green-400 hover:text-black">Next</a>
            @else
                <span class="border border-green-500/12 px-3 py-2 text-xs font-bold uppercase tracking-[0.16em] text-green-100/25">Next</span>
            @endif
        </div>
    </div>
@endif
