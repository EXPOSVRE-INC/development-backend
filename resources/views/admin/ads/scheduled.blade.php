@extends('adminlte::page')

@php
    $heads = [
        '',
        'ID',
        'Nickname',
        'Full Name',
        'Account status',
        '# reports',
        'Reason for flagged',
        'Content',
//        ['label' => 'Actions', 'no-export' => true, 'width' => 5],
    ];

    $config = [
        'order' => [[1, 'asc']],
        'columns' => [[
                'orderable' => false,
                'className' => 'select-checkbox',
                'targets' =>  0
            ], null, null, null, null, null, null, null, ['orderable' => false]],
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
                <td></td>
                <td>
                    {{$post->id}}
                </td>
                <td>
                    {{$post->owner->username}}
                </td>
                <td>
                    {{$post->owner->profile->firstName . ' ' . $post->owner->profile->lastName}}
                </td>
                <td>
                    {{$post->status}}
                </td>
                <td>
                    {{$post->reports->count()}}
                </td>
                <td>
                    {{$post->reports->first()->reason}}
                </td>
                <td>
                    @php
                        if ($post->getFirstMedia('files') && str_contains($post->getFirstMedia('files')->mime_type, 'image')) {
                            $image = $post->getFirstMediaUrl('files');
                        } else if ($post->getFirstMedia('files') && str_contains($post->getFirstMedia('files')->mime_type, 'video')) {
                            $image = $post->getFirstMediaUrl('files', 'original');
                        }
                    @endphp
                    <img height="64" src="{{$image}}">
                </td>
{{--                <td>--}}
{{--                    <button class="btn btn-xs btn-default text-primary mx-1 shadow" title="Edit">--}}
{{--                        <i class="fa fa-lg fa-fw fa-pen"></i></button>--}}
{{--                    <button class="btn btn-xs btn-default text-danger mx-1 shadow" title="Delete">--}}
{{--                        <i class="fa fa-lg fa-fw fa-trash"></i>--}}
{{--                    </button>--}}
{{--                    <button class="btn btn-xs btn-default text-teal mx-1 shadow" title="Details">--}}
{{--                        <i class="fa fa-lg fa-fw fa-eye"></i>--}}
{{--                    </button>--}}
{{--                </td>--}}
            </tr>
        @endforeach

    </x-adminlte-datatable>

@stop

@section('plugins.Datatables', true)
