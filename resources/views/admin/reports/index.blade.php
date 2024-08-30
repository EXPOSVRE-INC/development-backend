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
    ['label' => 'Actions', 'no-export' => true, 'width' => 5],
];

$config = [
    'order' => [[1, 'asc']],
    'columns' => [[
			'orderable' => false,
            'className' => 'select-checkbox',
            'targets' =>  0
        ], null, null, null, null, null, null, ['orderable' => false]],
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


        @foreach($users as $user)
            <tr data-id="{{$user->id}}">
                <td></td>
                <td>
                    {{$user->id}}
                </td>
                <td>
                    {{$user->username}}
                </td>
                <td>
                    {{$user->profile->firstName . ' ' . $user->profile->lastName}}
                </td>
                <td>
                    Flagged
                </td>
                <td>
                    {{$user->reports->count()}}
                </td>
                <td>
                    {{$user->reports->first()->reason}}
                </td>
                <td>
                    <button class="btn btn-xs btn-default text-primary mx-1 shadow" title="Edit">
                        <i class="fa fa-lg fa-fw fa-pen"></i></button>
                    <button class="btn btn-xs btn-default text-danger mx-1 shadow" title="Delete">
                        <i class="fa fa-lg fa-fw fa-trash"></i>
                    </button>
                    <button class="btn btn-xs btn-default text-teal mx-1 shadow" title="Details">
                        <i class="fa fa-lg fa-fw fa-eye"></i>
                    </button>
                </td>
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
        })
    </script>
@endpush

@section('plugins.Datatables', true)
