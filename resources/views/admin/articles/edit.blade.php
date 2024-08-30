@extends('adminlte::page')

@push('css')
    <style type="text/css">
        #image-preview, #thumbnail-preview, #video-preview {
            width: 100%;
            height: 400px;
            position: relative;
            overflow: hidden;
            background-color: #101010;
            color: #ecf0f1;
        }
        #image-preview input, #thumbnail-preview input, #video-preview input {
            line-height: 200px;
            font-size: 200px;
            position: absolute;
            opacity: 0;
            z-index: 10;
        }
        #image-preview label, #thumbnail-preview label, #video-preview label {
            position: absolute;
            z-index: 5;
            opacity: 0.8;
            cursor: pointer;
            background-color: #bdc3c7;
            width: 200px;
            height: 50px;
            font-size: 20px;
            line-height: 50px;
            text-transform: uppercase;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            margin: auto;
            text-align: center;
        }
    </style>
@endpush
@section('content')

    <form action="{{route('articles-edit-post', ['id' => $post->id])}}" method="post" enctype="multipart/form-data">
        {{csrf_field()}}
        <label for="file" class="text-lightblue">
            Header image
        </label>
        <div id="image-preview" style="background-size: cover; background-position: center center; background-image: url('{{$post->getFirstMediaUrl('files')}}')">
            <label for="image-upload" id="image-label">Choose File</label>
            <input type="file" name="file" id="image-upload" />
        </div>


        <x-adminlte-input name="title" label="Title" placeholder="Title" label-class="text-lightblue" value="{{$post->title}}">
            <x-slot name="prependSlot">
                <div class="input-group-text">
                    <i class="fas fa-user text-lightblue"></i>
                </div>
            </x-slot>
        </x-adminlte-input>

        <x-adminlte-input name="link" label="Link" placeholder="Link" label-class="text-lightblue" value="{{$post->link}}">
            <x-slot name="prependSlot">
                <div class="input-group-text">
                    <i class="fa-solid fa-link"></i>
                </div>
            </x-slot>
        </x-adminlte-input>

        <x-adminlte-textarea name="description" label="Description" rows=5 label-class="text-lightblue"
                             igroup-size="sm" placeholder="Insert description...">
            <x-slot name="prependSlot">
                <div class="input-group-text bg-dark">
                    <i class="fas fa-lg fa-file-alt text-warning"></i>
                </div>
            </x-slot>
            {!! $post->description !!}
        </x-adminlte-textarea>



{{--        <x-adminlte-select2 name="interest" label="Category" label-class="text-lightblue"--}}
{{--                            igroup-size="md" data-placeholder="Select a category...">--}}
{{--            <x-slot name="prependSlot">--}}
{{--                <div class="input-group-text bg-gradient-info">--}}
{{--                    <i class="fas fa-car-side"></i>--}}
{{--                </div>--}}
{{--            </x-slot>--}}
{{--            <option/>--}}
{{--            @foreach($categories as $category)--}}
{{--                <option {{($category->id == $post->interests->first()->id)}} value="{{$category->id}}"> {{$category->name}}</option>--}}
{{--            @endforeach--}}
{{--        </x-adminlte-select2>--}}

{{--        <x-adminlte-select2 name="owner_id" label="Creator" label-class="text-lightblue"--}}
{{--                            igroup-size="md" data-placeholder="Select an option...">--}}
{{--            <x-slot name="prependSlot">--}}
{{--                <div class="input-group-text bg-gradient-info">--}}
{{--                    <i class="fas fa-user"></i>--}}
{{--                </div>--}}
{{--            </x-slot>--}}
{{--            <option/>--}}
{{--            @foreach($users as $user)--}}
{{--                <option value="{{$user->id}}"><img height="25" src="{{$user->getFirstMediaUrl('preview')}}"> {{$user->profile ? $user->profile->firstName . ' ' . $user->profile->lastName : ''}} {{'<' . $user->email . '>'}}</option>--}}
{{--            @endforeach--}}
{{--        </x-adminlte-select2>--}}

        @php
            $config = ['format' => 'DD/MM/YYYY HH:mm'];
			$date = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s',  $post->publish_date);
        @endphp
        <x-adminlte-input-date name="publish_date" :config="$config" placeholder="Choose a date and time..."
                               label="Publish time" label-class="text-primary" value="{{$date->format('d/m/y H:i')}}">
            <x-slot name="appendSlot">
                <x-adminlte-button theme="outline-primary" icon="far fa-lg fa-calendar"
                                   title="Set Publish Date"/>
            </x-slot>
        </x-adminlte-input-date>

        <x-adminlte-button class="btn-flat" type="submit" label="Submit" theme="success" icon="fas fa-lg fa-save"/>
{{--        <x-adminlte-select2 name="owner">--}}
{{--            <option>-</option>--}}
{{--            @foreach($users as $user)--}}
{{--                <option value="{{$user->id}}"><img height="25" src="{{$user->getFirstMediaUrl('preview')}}"> {{$user->profile ? $user->profile->firstName . ' ' . $user->profile->lastName : ''}} {{'<' . $user->email . '>'}}</option>--}}
{{--            @endforeach--}}
{{--        </x-adminlte-select2>--}}
    </form>
@endsection

@push('js')
{{--    <script type="text/javascript" src="//code.jquery.com/jquery-2.0.3.min.js"></script>--}}
    <script type="text/javascript" src="{{asset('vendor/uploadPreview/jquery.uploadPreview.min.js')}}"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $.uploadPreview({
                input_field: "#image-upload",
                preview_box: "#image-preview",
                label_field: "#image-label"
            });
        });
        $(document).ready(function() {
            $.uploadPreview({
                input_field: "#thumbnail-upload",
                preview_box: "#thumbnail-preview",
                label_field: "#thumbnail-label"
            });
        });
        document.getElementById("videoUpload")
            .onchange = function(event) {
            let file = event.target.files[0];
            let blobURL = URL.createObjectURL(file);
            document.querySelector("video").src = blobURL;
        };
        // $(document).ready(function() {
        //     $.uploadPreview({
        //         input_field: "#video-upload",
        //         preview_box: "#video-preview",
        //         label_field: "#video-label"
        //     });
        // });
    </script>
@endpush

@section('plugins.Select2', true)
@section('plugins.BsCustomFileInput', true)
@section('plugins.TempusDominusBs4', true)
