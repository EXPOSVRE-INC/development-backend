@extends('adminlte::page')

@php
    $heads = [
        'ID',
        'Image',
        'Title',
        'Description',
        'Category',
        'Publish date',
        'Owner',
        ['label' => 'Actions', 'no-export' => true, 'width' => 5],
    ];

    $config = [
        'order' => [[1, 'asc']],
        'columns' => [null, null, null, null, null, null, null, ['orderable' => false]],
        'select' => [
            'style' => 'os',
            'selector' => 'td:first-child'
        ]
    ];

@endphp

@section('content')

    <x-adminlte-datatable id="table1" :heads="$heads" :config="$config">

        @foreach($posts as $post)
            <tr>
                <td>
                    {{$post->id}}
                </td>
                <td>
                    @php
                        if ($post->getFirstMedia('files') && str_contains($post->getFirstMedia('files')->mime_type, 'image')) {
                            $image = $post->getFirstMediaUrl('files');
                        } else if ($post->getFirstMedia('files') && str_contains($post->getFirstMedia('files')->mime_type, 'video')) {
                            $image = $post->getFirstMediaUrl('files', 'original');
                        }
                    @endphp
                    <img height="64" src="{{ $image ?? '' }}">
                </td>
                <td>
                    {{$post->title}}
                </td>
                <td>
                    {{$post->description}}
                </td>
                <td>
                    {{$post->interests()->first() ? $post->interests()->first()->name : ''}}
                </td>
                <td>
                    {{$post->publish_date}}
                </td>
                <td>
                    {{$post->owner->profile->firstName . ' ' . $post->owner->profile->lastName}}
                </td>

                <td>
                    <a class="btn btn-primary" href="{{ Route::is('articles-archive') ? route('articles-from-archive', $post->id) : route('articles-to-archive', $post->id) }}">
                        <i class="fa fa-lg fa-fw fa-archive"></i>{{Route::is('articles-archive') ? "From archive" : "To Archive"}}</a>
                    <a class="btn btn-default text-teal shadow" title="Details" href="{{route('articles-edit', ['id' => $post->id])}}">
                        <i class="fa fa-lg fa-fw fa-eye"></i>Edit
                    </a>
{{--                    <button class="btn btn-xs btn-default text-primary mx-1 shadow" title="Edit">--}}
{{--                        <i class="fa fa-lg fa-fw fa-pen"></i></button>--}}
{{--                    <button class="btn btn-xs btn-default text-danger mx-1 shadow" title="Delete">--}}
{{--                        <i class="fa fa-lg fa-fw fa-trash"></i>--}}
{{--                    </button>--}}
{{--                    <button class="btn btn-xs btn-default text-teal mx-1 shadow" title="Details">--}}
{{--                        <i class="fa fa-lg fa-fw fa-eye"></i>--}}
{{--                    </button>--}}
                </td>
            </tr>
        @endforeach

    </x-adminlte-datatable>

@stop

@section('plugins.Datatables', true)
