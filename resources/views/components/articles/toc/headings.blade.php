@foreach ($headings as $heading)
    <div>
        <a
            href="#{{ $heading['id'] }}"
            class="text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300"
            >{{ $heading['title'] }}</a
        >
        @isset($heading['children'])
            <ul class="mt-1 pl-4">
                <x-articles.toc.headings :headings="$heading['children']" />
            </ul>
        @endisset
    </div>
@endforeach
