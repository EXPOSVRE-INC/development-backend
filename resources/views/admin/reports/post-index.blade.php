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
//    ['label' => 'Actions', 'no-export' => true, 'width' => 5],
];

$config = [
    'order' => [[1, 'asc']],
    'columns' => [[
			'orderable' => false,
            'className' => 'select-checkbox',
            'targets' =>  0
        ], null, null, null, null, null, null, null],
    'select' => [
	    'style' => 'os',
	    'selector' => 'td:first-child'
    ]
];

@endphp

@section('content')
    <a id="clear" class="btn btn-danger m-2" href="">Clear account</a>
    <a id="warning" class="btn btn-danger m-2" href="">Issue a warning</a>
    <a id="ban" class="btn btn-danger m-2" href="">Move to ban</a>

    <x-adminlte-datatable id="table1" :heads="$heads" :config="$config">

        @foreach($posts as $post)
            <tr data-id="{{$post->owner->id}}">
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
                    Flagged
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
                    <img height="64" src="{{$image ?? ''}}">
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

@push('js')
    <script type="text/javascript">


        $('#clear').on('click', function(e) {
            e.preventDefault();
            if(confirm('Do you really want to clear accounts?')) {
                let ids = [];

                let elementsWithId = $('#table1').find('.selected');
                $(elementsWithId).each(function() {
                    ids.push(this.dataset.id);
                });

                console.log(ids);

                $.ajax({
                    type: "POST",
                    url: "{{route('clear-accounts')}}",
                    // contentType: 'application/json',
                    // processData: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {'ids': ids},
                    success: function(data){
                        console.log(data)
                    },
                });
            } else {

            }
        });

        $('#warning').on('click', function(e) {
            e.preventDefault();
            if(confirm('Issue a warning?')) {
                let ids = [];

                let elementsWithId = $('#table1').find('.selected');
                $(elementsWithId).each(function() {
                    ids.push(this.dataset.id);
                });

                console.log(ids);

                $.ajax({
                    type: "POST",
                    url: "{{route('issue-accounts')}}",
                    // contentType: 'application/json',
                    // processData: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {'ids': ids},
                    success: function(data){
                        console.log(data)
                    },
                });
            } else {

            }
        })

        $('#ban').on('click', function(e) {
            e.preventDefault();
            if(confirm('Ban accounts?')) {
                let ids = [];

                let elementsWithId = $('#table1').find('.selected');
                $(elementsWithId).each(function() {
                    ids.push(this.dataset.id);
                });

                console.log(ids);

                $.ajax({
                    type: "POST",
                    url: "{{route('ban-accounts')}}",
                    // contentType: 'application/json',
                    // processData: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {'ids': ids},
                    success: function(data){
                        console.log(data)
                        window.location.reload();
                    },
                });
            } else {

            }
        })
    </script>
@endpush

@section('plugins.Datatables', true)
