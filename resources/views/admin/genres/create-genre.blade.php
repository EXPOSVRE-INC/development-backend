@extends('adminlte::page')


@section('content')

    <form action="{{route('song-genre-create')}}" method="post" enctype="multipart/form-data">
        {{csrf_field()}}
        <x-adminlte-input name="name" label="Name" placeholder="Name" label-class="text-lightblue" value="">
            <x-slot name="prependSlot">
                <div class="input-group-text">
                    <i class="fas fa-list"></i>
                </div>
            </x-slot>
        </x-adminlte-input>
        <x-adminlte-button class="btn-flat btn-sm" type="submit" label="Submit" theme="success" icon="fas fa-lg fa-save"/>
    </form>

@endsection

@section('plugins.Select2', true)
