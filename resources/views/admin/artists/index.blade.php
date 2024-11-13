@extends('adminlte::page')
@php
$heads = [
    'ID',
    'Profile Image',
    'Name',
    ['label' => 'Actions', 'no-export' => true, 'width' => 10],
];

$config = [
    'order' => [[0, 'asc']], // Sort by the first column (ID) initially
    'columns' => [
        ['orderable' => true],  // ID column (sortable)
        null,                   // Profile Image column (not sortable)
        ['orderable' => true],  // Name column (sortable)
        ['orderable' => false], // Actions column (not sortable)
    ],
    'select' => [
        'style' => 'os',
        'selector' => 'td:first-child'
    ]
];
@endphp
@push('css')
<link type="text/css" href="{{asset('vendor/bootstrap-treeview/css/bootstrap-treeview.css')}}" />
{{--
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">--}}
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
<h1>Artists
    <a class="btn btn-primary float-right" href="{{route('artist-form')}}">Create</a>
</h1>
<x-adminlte-datatable id="table1" :heads="$heads" :config="$config">

    @foreach($artists as $artist)
    <tr>
        <td>
            {{$artist->id}}
        </td>
        <td>
            <img height="64" src="{{$artist->profile_image }} ">
        </td>
        <td>
            {{$artist->name}}
        </td>
        <td>
            <div class="btn-group btn-group-sm">
                <a class="btn btn-sm btn-default text-teal shadow" title="Details"
                    href="{{ route('artist-edit-form', ['id' => $artist['id']]) }}">
                    <i class="fa fa-lg fa-fw fa-eye"></i>Edit
                </a>

                <a href="{{ route('artist-delete', ['id' => $artist['id']]) }}" class="btn btn-danger btn-sm shadow"
                    onclick="return confirm('Are you sure?')">
                    <i class="fa fa-trash"></i> Remove
                </a>
            </div>
        </td>
    </tr>
    @endforeach

</x-adminlte-datatable>
@endsection
