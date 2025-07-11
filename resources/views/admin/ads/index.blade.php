@extends('adminlte::page')

@php
    $heads = [
        'ID',
        'Image',
        'Title',
        'Description',
        'Category',
        'Publish date',
        'Order priority',
        'Owner',
        ['label' => 'Actions', 'no-export' => true, 'width' => 5],
    ];

    $config = [
        'order' => [[1, 'asc']],
        'columns' => [null, null, null, null, null, null, null, null, ['orderable' => false]],
        'select' => [
            'style' => 'os',
            'selector' => 'td:first-child',
        ],
    ];

@endphp
@push('css')
    <style type="text/css">
        .video-icon-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 17px;
            color: white;
            background: rgba(0, 0, 0, 0.6);
            border-radius: 50%;
            padding: 4px 10px;
            pointer-events: none;
        }
    </style>
@endpush
@section('content')

    <x-adminlte-datatable id="table1" :heads="$heads" :config="$config">

        @foreach ($posts as $post)
            <tr>
                <td>
                    {{ $post->id }}
                </td>
                <td style="position: relative;">
                    @php
                        $media = $post->getFirstMedia('header_video') ?? $post->getFirstMedia('files');

                        $image = '';
                        $isVideo = false;

                        if ($media) {
                            $mime = $media->mime_type ?? '';
                            $isVideo = str_contains($mime, 'video');
                            $image = $media->getUrl($isVideo ? 'original' : '');
                        }
                    @endphp

                    <a href="{{ $isVideo ? $image : '#' }}" target="_blank" style="display: inline-block; position: relative;">
                        <img height="65" src="{{ $image }}" style="display: block; border-radius: 4px;">
                        @if ($isVideo)
                            <span class="video-icon-overlay">&#11208;</span> {{-- Unicode play symbol --}}
                        @endif
                    </a>
                </td>
                <td>
                    {{ $post->title }}
                </td>
                <td>
                    {{ strlen($post->description) > 300 ? substr($post->description, 0, 300) . ' ...' : $post->description }}
                </td>
                <td>
                    {{ $post->interests()->first() ? $post->interests()->first()->name : '' }}
                </td>
                <td>
                    {{ $post->publish_date }}
                </td>
                <td>
                    {{ $post->order_priority }}
                </td>
                <td>
                    {{ $post->owner->profile->firstName . ' ' . $post->owner->profile->lastName }}
                </td>

                <td>
                    <div class="btn-group btn-group-sm">
                        <a class="btn btn-sm btn-primary"
                            href="{{ Route::is('ads-archive') ? route('ads-from-archive', $post->id) : route('ads-to-archive', $post->id) }}">
                            <i
                                class="fa fa-lg fa-fw fa-archive"></i>{{ Route::is('ads-archive') ? 'From archive' : 'To Archive' }}
                        </a>
                        {{--                    <button class="btn btn-xs btn-default text-danger mx-1 shadow" title="Delete"> --}}
                        {{--                        <i class="fa fa-lg fa-fw fa-trash"></i> --}}
                        {{--                    </button> --}}
                        <a class="btn btn-sm btn-default text-teal shadow" title="Details"
                            href="{{ route('ads-edit', ['id' => $post->id]) }}">
                            <i class="fa fa-lg fa-fw fa-eye"></i>Edit
                        </a>
                        <a class="btn btn-sm btn-default text-teal shadow" title="Move to top"
                            href="{{ route('prioritise-post', ['id' => $post->id]) }}">
                            <i class="fa fa-lg fa-fw fa-eye"></i>Move to top
                        </a>
                        <a class="btn btn-sm btn-danger shadow" title="Remove"
                            href="{{ route('post-delete', ['id' => $post->id]) }}">
                            <i class="fa fa-lg fa-fw fa-trash"></i>Remove
                        </a>
                    </div>
                </td>
            </tr>
        @endforeach

    </x-adminlte-datatable>

@stop

@section('plugins.Datatables', true)
