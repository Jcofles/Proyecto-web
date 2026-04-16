@props(['items' => []])

<nav class="flex items-center mb-6" aria-label="Breadcrumb">
    <div class="flex items-center bg-white dark:bg-[#161615] border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-sm px-4 py-2 shadow-[0px_0px_1px_0px_rgba(0,0,0,0.03),0px_1px_2px_0px_rgba(0,0,0,0.06)]">
        <ol class="flex items-center space-x-2">
            @foreach($items as $index => $item)
                <li class="flex items-center">
                    @if($index > 0)
                        <div class="flex items-center mx-2">
                            <div class="w-1 h-1 bg-[#00ff88] rounded-full"></div>
                        </div>
                    @endif
                    
                    @if($loop->last)
                        <span class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] flex items-center">
                            @if(isset($item['icon']))
                                <span class="mr-2">{!! $item['icon'] !!}</span>
                            @endif
                            {{ $item['title'] }}
                        </span>
                    @else
                        <a href="{{ $item['url'] }}" 
                           class="text-sm text-[#706f6c] dark:text-[#A1A09A] hover:text-[#00ff88] transition-colors duration-200 font-medium flex items-center">
                            @if(isset($item['icon']))
                                <span class="mr-2">{!! $item['icon'] !!}</span>
                            @endif
                            {{ $item['title'] }}
                        </a>
                    @endif
                </li>
            @endforeach
        </ol>
    </div>
</nav>