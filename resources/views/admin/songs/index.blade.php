@extends('adminlte::page')

@php
    $heads = [
        'ID',
        'Image',
        'Title',
        'Artist',
        'Genre',
        'Mood',
        'Song Length',
        'Library Plays',
        'Posts added to',
        'Upload Date',
        ['label' => 'Actions', 'no-export' => true, 'width' => 5],
    ];

    $config = [
        'order' => [[1, 'asc']],
        'columns' => [null, null, null, null, null, null, null, null, null , null , ['orderable' => false]],
        'select' => [
            'style' => 'os',
            'selector' => 'td:first-child'
        ]
    ];

@endphp

@section('content')

<h1>Songs
    <a class="btn btn-primary float-right" href="{{route('song-create')}}">Upload Music</a>
</h1>
    <x-adminlte-datatable id="table1" :heads="$heads" :config="$config">

        @foreach($songs as $song)
            <tr>
                <td>
                    {{$song->id}}
                </td>
                <td>
                    @php
                        // if ($post->getFirstMedia('thumb') && str_contains($post->getFirstMedia('thumb')->mime_type, 'image')) {
                        //     $image = $post->getFirstMediaUrl('thumb');
                        // } else if ($post->getFirstMedia('thumb') && str_contains($post->getFirstMedia('thumb')->mime_type, 'video')) {
                        //     $image = $post->getFirstMediaUrl('thumb', 'original');
                        // } else {
						// 	if ($post->getFirstMedia('files') && str_contains($post->getFirstMedia('files')->mime_type, 'image')) {
                        //         $image = $post->getFirstMediaUrl('files');
                        //     } else if ($post->getFirstMedia('files') && str_contains($post->getFirstMedia('files')->mime_type, 'video')) {
                        //         $image = $post->getFirstMediaUrl('files', 'original');
                        //     }
						// }

                    @endphp
                    <img height="64" src="{{URL::asset('storage/'.$song->image_file)}}">
                </td>
                <td>
                   {{$song->title}}
                </td>

                <td>
                    {{$song->artist ? $song->artist->name : ''}}
                </td>
                <td>
                    {{$song->genre->name ?? ''}}
                </td>
                <td>
                    {{$song->mood->name ?? ''}}
                </td>
                <td>
                    {{$song->song_length ?? ''}}
                </td>
                <td>
                    {{$song->listens_count ?? 0}}
                </td>
                <td>
                    {{0}}
                </td>
                <td>
                    {{ \Carbon\Carbon::parse($song->created_at)->format('m/d/y') }}
                </td>

                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a class="btn btn-sm btn-default text-teal shadow" title="Details"
                                        href="{{route('song-edit-form', ['id' => $song->id])}}"
                                        >
                                            <i class="fa fa-lg fa-fw fa-eye"></i>Edit
                                        </a>

                                        <a href="{{ route('song-delete', ['id' => $song->id]) }}" class="btn btn-danger btn-sm shadow" onclick="return confirm('Are you sure?')">
                                            <i class="fa fa-trash"></i> Remove
                                        </a>
                                    </div>
                                </td>
            </tr>
         @endforeach

    </x-adminlte-datatable>

@stop

@section('plugins.Datatables', true)
