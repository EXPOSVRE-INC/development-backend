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
    <h1>Interests
        <a class="btn btn-primary float-right" href="{{route('category-create')}}">Create</a>
    </h1>
    <div id="default-tree"></div>
@endsection

@push('js')
    <script type="text/javascript" src="{{asset('vendor/bootstrap-treeview/js/bootstrap-treeview.js')}}"></script>
    <script type="text/javascript">
        var myTree = @json($interests);
        var treeView = $('#default-tree').treeview({
            data: myTree,
            expandIcon: 'fas fa-plus',
            collapseIcon: 'fas fa-minus',
            enableLinks: true,
            onhoverColor:'#e83e8c',
            // selectedColor:'#000',
            selectedBackColor:'#000',
        }).treeview('collapseAll', { silent:true });
    </script>
@endpush
