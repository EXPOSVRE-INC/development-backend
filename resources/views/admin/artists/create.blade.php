@extends('adminlte::page')


@section('content')

    <form action="{{route('artist-create')}}" method="post" enctype="multipart/form-data">
        {{csrf_field()}}
        <x-adminlte-input type="hidden" name="id" value="{{ $id ?? '' }}"></x-adminlte-input>
        <input type="hidden" name="previous_url" value="{{ url()->previous() }}">
            <x-adminlte-input name="name" label="Name" placeholder="Name" label-class="text-lightblue" value="{{$artist != null && $artist->name != null ? $artist->name :''}}">
            <x-slot name="prependSlot">
                <div class="input-group-text">
                    <i class="fas fa-fw fa-user"></i>
                </div>
            </x-slot>
        </x-adminlte-input>
        <x-adminlte-button class="btn-flat btn-sm" type="submit" label="Submit" theme="success" icon="fas fa-lg fa-save"/>
    </form>

@endsection

@section('plugins.Select2', true)
