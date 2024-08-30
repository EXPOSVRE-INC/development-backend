@extends('adminlte::page')

@php
    $heads = [
        'ID',
        'Nickname',
        'Full Name',
        'Email',
        'Account status',
        'Verified',
        ['label' => 'Actions', 'no-export' => true, 'width' => 5],
    ];

    $config = [
        'order' => [[1, 'asc']],
        'columns' => [null, null, null, null, null, null, null],
    ];

@endphp

@section('content')
{{--    <a id="clear" class="btn btn-danger m-2" href="">Clear account</a>--}}
{{--    <a id="warning" class="btn btn-danger m-2" href="">Issue a warning</a>--}}
{{--    <a id="ban" class="btn btn-danger m-2" href="">Move to ban</a>--}}

    <x-adminlte-datatable id="table1" :heads="$heads" :config="$config">


        @foreach($users as $user)
            <tr data-id="{{$user->id}}">
                <td>
                    {{$user->id}}
                </td>
                <td>
                    {{$user->username}}
                </td>
                <td>
                    {{($user->profile && $user->profile != null) ? $user->profile->firstName . ' ' . $user->profile->lastName : ''}}
                </td>
                <td>
                    {{$user->email}}
                </td>
                <td>
                    {{$user->status}}
                </td>
                <td>
                    {!! $user->verify ? '<i class="far fa-fw fa-check-circle" aria-hidden="true"></i>' : '<i class="far fa-fw fa-circle" aria-hidden="true"></i>' !!}
                </td>
                <td>
                    <button class="btn btn-xs btn-default text-primary mx-1 shadow" onclick="window.location.href='{{route('users-show', ['id' => $user->id])}}';" title="Show">
                        <i class="fa fa-lg fa-fw fa-pen"></i></button>
                </td>
            </tr>
        @endforeach

    </x-adminlte-datatable>

@stop
