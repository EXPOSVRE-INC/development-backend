@extends('adminlte::page')

@push('css')
    <style type="text/css">
        #image-preview,
        #thumbnail-preview,
        #video-thumbnail-preview,
        #video-preview {
            width: 100%;
            height: 400px;
            position: relative;
            overflow: hidden;
            background-color: #101010;
            color: #ecf0f1;
        }

        #image-preview input,
        #thumbnail-preview input,
        #video-thumbnail-preview input,
        #video-preview input {
            line-height: 200px;
            font-size: 200px;
            position: absolute;
            opacity: 0;
            z-index: 10;
        }

        #image-preview label,
        #thumbnail-preview label,
        #video-thumbnail-preview label,
        #video-preview label {
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

    <form action="{{ route('ads-edit-post', ['id' => $post->id]) }}" method="post" enctype="multipart/form-data">
        {{ csrf_field() }}
        <label for="thumbnail" class="text-lightblue">
            Header
        </label>
        <div id="thumbnail-preview"
            style="background-size: cover; background-position: center center; background-image: url('{{ $post->getFirstMediaUrl('thumb') }}')">
            <label for="thumbnail-upload" id="thumbnail-label">Choose File</label>
            <input type="file" name="thumbnail" id="thumbnail-upload" />
        </div>
        <label for="file" class="text-lightblue">
            Story
        </label>
        @php
            $files = $post->getMedia('files');
            $initialPreview = [];
            $initialPreviewConfig = [];
            foreach ($files as $key => $file) {
                $initialPreview[] =
                    "<img src='" . $file->getFullUrl() . "'  class='kv-preview-data file-preview-image'>";
                $initialPreviewConfig[] = [
                    'caption' => $file->file_name,
                    'description' => 'Test static',
                    'url' => '/admin/ads/remove-image',
                    'key' => $key,
                    'fileId' => $file->id,
                    'size' => $file->size,
                ];
            }
            //			dd($files);
            //        dump(json_encode($array));
            $config = [
                'browseOnZoneClick' => true,
                'initialPreview' => $initialPreview,
                'initialPreviewConfig' => $initialPreviewConfig,
                'initialPreviewShowDelete' => true,
            ];
        @endphp

        {{-- With placeholder, SM size multiple and data-* options --}}
        <x-adminlte-input-file-krajee id="image-file" name="file[]" igroup-size="sm"
            data-msg-placeholder="Choose multiple files..." data-show-cancel="true" :config="$config" data-show-close="true"
            multiple />


        <x-adminlte-input name="title" label="Title" placeholder="Title" label-class="text-lightblue"
            value="{!! $post->title !!}">
            <x-slot name="prependSlot">
                <div class="input-group-text">
                    <i class="fas fa-user text-lightblue"></i>
                </div>
            </x-slot>
        </x-adminlte-input>

        <x-adminlte-input name="subtitle" label="Subtitle" placeholder="Subtitle" label-class="text-lightblue"
            value="{!! $post->subtitle !!}">
            <x-slot name="prependSlot">
                <div class="input-group-text">
                    <i class="fas fa-user text-lightblue"></i>
                </div>
            </x-slot>
        </x-adminlte-input>

        <x-adminlte-input name="author" label="Author" placeholder="Author" label-class="text-lightblue"
            value="{{ $post->author }}">
            <x-slot name="prependSlot">
                <div class="input-group-text">
                    <i class="fas fa-user text-lightblue"></i>
                </div>
            </x-slot>
        </x-adminlte-input>

        <x-adminlte-input name="link" label="Link" placeholder="Link" label-class="text-lightblue"
            value="{{ $post->link }}">
            <x-slot name="prependSlot">
                <div class="input-group-text">
                    <i class="fa-solid fa-link"></i>
                </div>
            </x-slot>
        </x-adminlte-input>

        {{-- With append slot, number type and sm size --}}
        <x-adminlte-input name="order_priority" label="Priority" placeholder="priority" type="number"
            value="{{ $post->order_priority }}" igroup-size="sm" min=1 max=10000>
            <x-slot name="appendSlot">
                <div class="input-group-text bg-dark">
                    <i class="fas fa-hashtag"></i>
                </div>
            </x-slot>
        </x-adminlte-input>

        <x-adminlte-textarea name="description" label="Description" rows=5 label-class="text-lightblue" igroup-size="sm"
            placeholder="Insert description...">
            <x-slot name="prependSlot">
                <div class="input-group-text bg-dark">
                    <i class="fas fa-lg fa-file-alt text-warning"></i>
                </div>
            </x-slot>
            {!! $post->description !!}
        </x-adminlte-textarea>

        <label for="file" class="text-lightblue">
            Video
        </label>
        <input type='file' name="video" id='videoUpload' accept="video/*" />

        <video width="100%" height="300" controls src="{{ $post->getFirstMediaUrl('video') }}">
            Your browser does not support the video tag.
        </video>

        <label for="video_thumbnail" class="text-lightblue">
            Video Thumbnail
        </label>
        <div id="video-thumbnail-preview"
            style="background-size: cover; background-position: center center; background-image: url('{{ $post->getFirstMediaUrl('video_thumb') }}')">
            <label for="video_thumbnail_upload" id="video-thumbnail-label">Choose thumbnail</label>
            <input type="file" name="video_thumbnail" id="video_thumbnail_upload" />
        </div>
        @php
            $config = ['format' => 'DD/MM/YYYY HH:mm'];
            if ($post->publish_date) {
                $date = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $post->publish_date);
            }
        @endphp
        <x-adminlte-input-date name="publish_date" :config="$config" placeholder="Choose a date and time..."
            label="Publish time" label-class="text-primary"
            value="{{ $post->publish_date ? $date->format('d/m/y H:i') : '' }}">
            <x-slot name="appendSlot">
                <x-adminlte-button theme="outline-primary" icon="far fa-lg fa-calendar" title="Set Publish Date" />
            </x-slot>
        </x-adminlte-input-date>

        <x-adminlte-button class="btn-flat btn-sm" type="submit" label="Submit" theme="success"
            icon="fas fa-lg fa-save" />
        {{--        <x-adminlte-select2 name="owner"> --}}
        {{--            <option>-</option> --}}
        {{--            @foreach ($users as $user) --}}
        {{--                <option value="{{$user->id}}"><img height="25" src="{{$user->getFirstMediaUrl('preview')}}"> {{$user->profile ? $user->profile->firstName . ' ' . $user->profile->lastName : ''}} {{'<' . $user->email . '>'}}</option> --}}
        {{--            @endforeach --}}
        {{--        </x-adminlte-select2> --}}
        <a class="btn btn-sm btn-danger shadow float-right" title="Remove"
            href="{{ route('post-delete', ['id' => $post->id]) }}">
            <i class="fa fa-lg fa-fw fa-trash"></i>Remove
        </a>
    </form>
@endsection

@push('js')
    {{--    <script type="text/javascript" src="//code.jquery.com/jquery-2.0.3.min.js"></script> --}}
    <script type="text/javascript" src="{{ asset('vendor/uploadPreview/jquery.uploadPreview.min.js') }}"></script>
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
        $(document).ready(function() {
            $.uploadPreview({
                input_field: "#video_thumbnail_upload",
                preview_box: "#video-thumbnail-preview",
                label_field: "#video-thumbnail-label"
            });
        });
        document.getElementById("videoUpload")
            .onchange = function(event) {
                let file = event.target.files[0];
                let blobURL = URL.createObjectURL(file);
                document.querySelector("video").src = blobURL;
            };

        // document.getElementById("headerVideoUpload").onchange = function(event) {
        //     let file = event.target.files[0];
        //     let blobURL = URL.createObjectURL(file);
        //     document.querySelector("video").src = blobURL;
        // };
        $(document).ready(function() {
            $.uploadPreview({
                input_field: "#video-upload",
                preview_box: "#video-preview",
                label_field: "#video-label"
            });
        });
    </script>
@endpush

@section('plugins.Select2', true)
@section('plugins.BsCustomFileInput', true)
@section('plugins.TempusDominusBs4', true)
@section('plugins.KrajeeFileinput', true)
