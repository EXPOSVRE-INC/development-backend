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
    <h1>Genres
        <a class="btn btn-primary float-right" href="{{route('song-genre-form')}}">Create</a>
    </h1>
            <table class="table table-bordered table-striped">
                <tbody>
                @if ($genres->isEmpty())
                    <tr>
                        <td colspan="2" class="text-center">No data found</td>
                    </tr>
                 @else
                    @foreach ($genres as $genre)
                        <tr>
                            <td>{{ $genre['name'] }}</td>
                            <td class="text-right">
                                <a href="{{ route('genre-edit', ['id' => $genre['id']]) }}" class="btn btn-primary btn-sm">
                                    <i class="fa fa-edit"></i> Edit
                                </a>
                                <a href="{{ route('genre-delete', ['id' => $genre['id']]) }}" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">
                                    <i class="fa fa-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                    @endforeach
                @endif
                </tbody>
            </table>
@endsection


