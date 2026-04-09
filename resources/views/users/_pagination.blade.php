<div class="flex-wrap mt-5 d-flex justify-content-center align-items-center">
    <div class="flex-wrap py-2 mr-3 d-flex">
        @if ($paginator->onFirstPage())
            <a href="#" class="my-1 mr-2 btn btn-icon btn-sm btn-light-primary disabled"><i class="ki ki-bold-double-arrow-back icon-xs"></i></a>
            <a href="#" class="my-1 mr-2 btn btn-icon btn-sm btn-light-primary disabled"><i class="ki ki-bold-arrow-back icon-xs"></i></a>
        @else
            <a href="{{ $paginator->url(1) }}" class="my-1 mr-2 btn btn-icon btn-sm btn-light-primary"><i class="ki ki-bold-double-arrow-back icon-xs"></i></a>
            <a href="{{ $paginator->previousPageUrl() }}" class="my-1 mr-2 btn btn-icon btn-sm btn-light-primary"><i class="ki ki-bold-arrow-back icon-xs"></i></a>
        @endif

        @foreach ($paginator->getUrlRange(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $page => $url)
            <a href="{{ $url }}" class="btn btn-icon btn-sm border-0 btn-hover-primary mr-2 my-1 {{ $page == $paginator->currentPage() ? 'active' : '' }}">
                {{ $page }}
            </a>
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="my-1 mr-2 btn btn-icon btn-sm btn-light-primary"><i class="ki ki-bold-arrow-next icon-xs"></i></a>
            <a href="{{ $paginator->url($paginator->lastPage()) }}" class="my-1 mr-2 btn btn-icon btn-sm btn-light-primary"><i class="ki ki-bold-double-arrow-next icon-xs"></i></a>
        @else
            <a href="#" class="my-1 mr-2 btn btn-icon btn-sm btn-light-primary disabled"><i class="ki ki-bold-arrow-next icon-xs"></i></a>
            <a href="#" class="my-1 mr-2 btn btn-icon btn-sm btn-light-primary disabled"><i class="ki ki-bold-double-arrow-next icon-xs"></i></a>
        @endif
    </div>
</div>
