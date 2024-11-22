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
<h1>Moods
    <a class="btn btn-primary float-right" href="{{route('mood-form')}}">Create</a>
</h1>
<x-adminlte-datatable id="table1" :heads="$heads" :config="$config">
    @if ($moods->isEmpty())
    <tr>
        <td colspan="2" class="text-center">No data found</td>
    </tr>
    @else
    @foreach ($moods as $mood)
    <tr>
        <td>
            {{$mood->id}}
        </td>
        <td>{{ $mood['name'] }}</td>
        <td>
            <div class="btn-group btn-group-sm">
                <a class="btn btn-sm btn-default text-teal shadow" title="Details"
                    href="{{ route('mood-edit', ['id' => $mood['id']]) }}">
                    <i class="fa fa-lg fa-fw fa-eye"></i>Edit
                </a>

                <a href="{{ route('mood-delete', ['id' => $mood['id']]) }}" class="btn btn-danger btn-sm shadow"
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
