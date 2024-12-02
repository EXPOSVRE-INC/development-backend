@extends('adminlte::page')


@section('content')

    <form action="{{route('category-create-post')}}" method="post" enctype="multipart/form-data">
        {{csrf_field()}}
        <x-adminlte-input name="name" label="Name" placeholder="Name" label-class="text-lightblue" value="">
            <x-slot name="prependSlot">
                <div class="input-group-text">
                    <i class="fas fa-link"></i>
                </div>
            </x-slot>
        </x-adminlte-input>
        <x-adminlte-select name="parent_id">
            <option value="{{null}}">-</option>
            @foreach($parents as $parent)
                <option value="{{$parent->id}}">{{$parent->name}}</option>
            @endforeach
        </x-adminlte-select>
        <x-adminlte-button class="btn-flat btn-sm" type="submit" label="Submit" theme="success" icon="fas fa-lg fa-save"/>
    </form>

@endsection

@section('plugins.Select2', true)
