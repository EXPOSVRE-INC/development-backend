@extends('adminlte::page')

@push('css')
    <link type="text/css" href="{{asset('vendor/bootstrap-treeview/css/bootstrap-treeview.css')}}"/>
{{--    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">--}}
    <style>
        .node-default-tree:not(.node-disabled):hover {
            background-color: #e83e8c;
        }
        li .list-group-item.node-selected {
            background-color: #000!important;
        }
    </style>
@endpush

@section('content')
    <h1>Moods
        <a class="btn btn-primary float-right" href="{{route('mood-form')}}">Create</a>
    </h1>
            <table class="table table-bordered table-striped">
                <tbody>
                    @foreach ($moods as $mood)
                        <tr>
                            <td>{{ $mood['name'] }}</td>
                            <td class="text-right">
                                <a href="{{ route('mood-edit', ['id' => $mood['id']]) }}" class="btn btn-primary btn-sm">
                                    <i class="fa fa-edit"></i> Edit
                                </a>
                                <a href="{{ route('mood-delete', ['id' => $mood['id']]) }}" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">
                                    <i class="fa fa-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
@endsection


