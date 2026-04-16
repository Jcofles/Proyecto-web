@props(['items' => []])

<nav class="flex items-center space-x-1 text-sm text-[#706f6c] dark:text-[#A1A09A] mb-6" aria-label="Breadcrumb">
    <ol class="flex items-center space-x-1">
        @foreach($items as $index => $item)
            <li class="flex items-center">
                @if($index > 0)
                    <svg class="w-4 h-4 mx-2 text-[#e3e3e0] dark:text-[#3E3E3A]" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                @endif
                
                @if($loop->last)
                    <span class="font-medium text-[#1b1b18] dark:text-[#EDEDEC]">{{ $item['title'] }}</span>
                @else
                    <a href="{{ $item['url'] }}" 
                       class="hover:text-[#00ff88] transition-colors duration-200 font-medium">
                        {{ $item['title'] }}
                    </a>
                @endif
            </li>
        @endforeach
    </ol>
</nav>