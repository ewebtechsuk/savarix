@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full pl-3 pr-4 py-2 border-l-4 border-indigo-600 text-base font-medium text-indigo-700 bg-indigo-50 focus:outline-none focus:bg-indigo-100 focus:border-indigo-600'
            : 'block w-full pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-indigo-600 hover:bg-gray-50 hover:border-indigo-600 focus:outline-none focus:bg-gray-50 focus:border-indigo-600';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
