@props(['items' => []])

<nav class="mb-6" aria-label="Breadcrumb">
    <div class="border-l-2 border-[#00ff88] pl-4">
        <ol class="flex items-center space-x-2 text-sm">
            @foreach($items as $index => $item)
                <li class="flex items-center">
                    @if($index > 0)
                        <span class="mx-2 text-[#e3e3e0] dark:text-[#3E3E3A]">/</span>
                    @endif
                    
                    @if($loop->last)
                        <span class="font-medium text-[#1b1b18] dark:text-[#EDEDEC]">
                            {{ $item['title'] }}
                        </span>
                    @else
                        <a href="{{ $item['url'] }}" 
                           class="text-[#706f6c] dark:text-[#A1A09A] hover:text-[#00ff88] transition-colors duration-200 underline-offset-4 hover:underline">
                            {{ $item['title'] }}
                        </a>
                    @endif
                </li>
            @endforeach
        </ol>
    </div>
</nav>