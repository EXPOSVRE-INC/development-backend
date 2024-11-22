@extends('adminlte::page')
@php
$heads = [
    'ID',
    'Name',
    ['label' => 'Actions', 'no-export' => true , 'width' => '20'],
];

$config = [
    'order' => [[0, 'asc']],
    'columns' => [
        ['orderable' => true],
        ['orderable' => true],
        ['orderable' => false],
    ]
];
@endphp

@push('css')
<link type="text/css" href="{{asset('vendor/bootstrap-treeview/css/bootstrap-treeview.css')}}" />

<style>
    .node-default-tree:not(.node-disabled):hover {
        background-color: #e83e8c;
    }

    li .list-group-item.node-selected {
        background-color: #000 !important;
    }
</style>
@endpush

@section('content')
<h1>Genres
    <a class="btn btn-primary float-right" href="{{route('song-genre-form')}}">Create</a>
</h1>
<x-adminlte-datatable id="table1" :heads="$heads" :config="$config">
    @if ($genres->isEmpty())
    <tr>
        <td colspan="2" class="text-center">No data found</td>
    </tr>
    @else
    @foreach ($genres as $genre)
    <tr>
        <td>
            {{$genre->id}}
        </td>
        <td>{{ $genre['name'] }}</td>
        <td>
            <div class="btn-group btn-group-sm">
                <a class="btn btn-sm btn-default text-teal shadow" title="Details"
                    href="{{ route('genre-edit', ['id' => $genre['id']]) }}">
                    <i class="fa fa-lg fa-fw fa-eye"></i>Edit
                </a>

                <a href="{{ route('genre-delete', ['id' => $genre['id']]) }}" class="btn btn-danger btn-sm shadow"
                    onclick="return confirm('Are you sure?')">
                    <i class="fa fa-trash"></i> Remove
                </a>
            </div>
        </td>
    </tr>
    @endforeach
    @endif
</x-adminlte-datatable>
@endsection
