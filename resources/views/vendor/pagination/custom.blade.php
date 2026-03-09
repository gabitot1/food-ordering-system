@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between">
        <div class="flex-1 flex items-center justify-between sm:justify-end">
            <ul class="inline-flex items-center -space-x-px">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <li aria-disabled="true" aria-label="&laquo; Previous">
                        <span class="px-3 py-1 rounded-l-lg border border-gray-200 bg-white text-gray-400">&laquo;</span>
                    </li>
                @else
                    <li>
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="px-3 py-1 rounded-l-lg border border-gray-200 bg-white hover:bg-green-50">&laquo;</a>
                    </li>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <li><span class="px-3 py-1 border border-gray-200 bg-white">{{ $element }}</span></li>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li aria-current="page"><span class="px-3 py-1 border border-green-600 bg-green-600 text-white font-medium">{{ $page }}</span></li>
                            @else
                                <li><a href="{{ $url }}" class="px-3 py-1 border border-gray-200 bg-white hover:bg-green-50">{{ $page }}</a></li>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <li>
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next &raquo;" class="px-3 py-1 rounded-r-lg border border-gray-200 bg-white hover:bg-green-50">&raquo;</a>
                    </li>
                @else
                    <li aria-disabled="true" aria-label="Next &raquo;">
                        <span class="px-3 py-1 rounded-r-lg border border-gray-200 bg-white text-gray-400">&raquo;</span>
                    </li>
                @endif
            </ul>
        </div>
    </nav>
@endif
