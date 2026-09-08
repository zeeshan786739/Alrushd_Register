@props(['name'])
<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
    @switch($name)
        @case('view')
            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>
            @break
        @case('edit')
            <path d="m16 3 5 5M3 21l5-1L21 7a2.8 2.8 0 0 0-4-4L4 16l-1 5Z"/>
            @break
        @case('settings')
            <path d="m10 3-.6 2.2-2 .9-2-.7-2 3.4L5 10.4v2.3l-1.6 1.7 2 3.4 2-.7 2 .9.6 3h4l.6-3 2-.9 2 .7 2-3.4-1.6-1.7v-2.3l1.6-1.6-2-3.4-2 .7-2-.9L14 3Z"/><circle cx="12" cy="12" r="3"/>
            @break
        @case('link')
            <path d="m10 13 4-4M8 16l-1 1a4.2 4.2 0 0 1-6-6l4-4a4.2 4.2 0 0 1 6 0m2 10a4.2 4.2 0 0 0 6 0l4-4a4.2 4.2 0 0 0-6-6l-1 1" transform="translate(1 0) scale(.92 1)"/>
            @break
        @case('duplicate')
            <rect x="8" y="8" width="12" height="13" rx="2"/><path d="M16 8V3H3v13h5m4-3h4m-4 4h4"/>
            @break
        @case('delete')
            <path d="M3 6h18M9 6V3h6v3M5 6l1 15h12l1-15M10 10v7m4-7v7"/>
            @break
    @endswitch
</svg>
