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

    <form action="{{ route('ads-post') }}" method="post" enctype="multipart/form-data" id="ad-form">
        {{ csrf_field() }}
        <label class="text-lightblue">Header Type</label><br />
        <input type="radio" name="header_type" value="image" checked> Image
        <input type="radio" name="header_type" value="video"> Video

        <!-- Image upload -->
        <div id="header-image-input">
            <label for="thumbnail" class="text-lightblue">Header Image</label>
            <div id="thumbnail-preview">
                <label for="thumbnail-upload" id="thumbnail-label">Choose File</label>
                <input type="file" name="thumbnail" id="thumbnail-upload" accept="image/*" />
            </div>
        </div>

        <!-- Video upload -->
        <div id="header-video-input" style="display: none;">
            <label for="header_video" class="text-lightblue">Header Video</label>
            <div id="header-video-preview">
                <label for="header_video_upload" class="text-white" id="header-video-label">Choose Video</label>
                <input type="file" name="header_video" id="header_video_upload" accept="video/*" />
                <video id="preview-header-video" width="100%" height="300" controls
                    style="margin-top: 10px; display: none;">
                    <source id="header-video-source" src="" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            </div>
        </div>

        <label for="file" class="text-lightblue">
            Story
        </label>
        @php
            $initialPreview = [];
            $initialPreviewConfig = [];
            $initialPreviewConfig[] = [
                'description' => 'Test static',
                'url' => '/admin/ads/remove-image',
            ];
            // dd($files);
            // dump(json_encode($array));
            $config = [
                'browseOnZoneClick' => true,
                'initialPreview' => $initialPreview,
                'initialPreviewConfig' => $initialPreviewConfig,
                'initialPreviewShowDelete' => true,
            ];
        @endphp
        <x-adminlte-input-file-krajee id="image-file" name="file[]" igroup-size="sm"
            data-msg-placeholder="Choose multiple files..." data-show-cancel="true" :config="$config" data-show-close="true"
            multiple />


        <x-adminlte-input name="title" label="Title" placeholder="Title" label-class="text-lightblue">
            <x-slot name="prependSlot">
                <div class="input-group-text">
                    <i class="fas fa-user text-lightblue"></i>
                </div>
            </x-slot>
        </x-adminlte-input>

        <x-adminlte-input name="subtitle" label="Subtitle" placeholder="Subtitle" label-class="text-lightblue">
            <x-slot name="prependSlot">
                <div class="input-group-text">
                    <i class="fas fa-user text-lightblue"></i>
                </div>
            </x-slot>
        </x-adminlte-input>

        <x-adminlte-input name="author" label="Author" placeholder="author" label-class="text-lightblue">
            <x-slot name="prependSlot">
                <div class="input-group-text">
                    <i class="fas fa-user text-lightblue"></i>
                </div>
            </x-slot>
        </x-adminlte-input>

        <x-adminlte-input name="link" label="Link" placeholder="Link" label-class="text-lightblue">
            <x-slot name="prependSlot">
                <div class="input-group-text">
                    <i class="fas fa-link" style="color: #86bad8"></i>
                </div>
            </x-slot>
        </x-adminlte-input>

        {{-- With append slot, number type and sm size --}}
        <x-adminlte-input name="order_priority" label="Priority" placeholder="priority" type="number" igroup-size="sm"
            min=1 max=10000>
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
                    <i class="fas fa-lg fa-file-alt" style="color: #86bad8"></i>
                </div>
            </x-slot>
        </x-adminlte-textarea>

        <label for="file" class="text-lightblue">
            Video
        </label>
        <input type='file' name="video" id='videoUpload' accept="video/*" />

        <video width="100%" id="videoPreview" height="300" controls>
            Your browser does not support the video tag.
        </video>
        <label for="video_thumbnail" class="text-lightblue">
            Video Thumbnail
        </label>
        <div id="video-thumbnail-preview">
            <label for="video_thumbnail_upload" class="text-white" id="video-thumbnail-label">
                Choose thumbnail
            </label>
            <input type="file" name="video_thumbnail" id="video_thumbnail_upload">
        </div>
        @php
            $config = [
                'placeholder' => 'Select multiple options...',
                'allowClear' => true,
            ];
        @endphp
        <x-adminlte-select2 id="interests" name="interests[]" label="Category" label-class="text-lightblue"
            igroup-size="md" igroup-size="sm" :config="$config" multiple>
            <x-slot name="prependSlot">
                <div class="input-group-text bg-gradient-red">
                    <i class="fas fa-tag"></i>
                </div>
            </x-slot>
            <x-slot name="appendSlot">
                <x-adminlte-button theme="outline-dark" label="Clear" icon="fas fa-lg fa-ban text-danger" />
            </x-slot>
            <option />
            @foreach ($categories as $category)
                <option value="{{ $category->id }}"> {{ $category->name }}</option>
                @foreach ($category->children as $child)
                    <option value="{{ $child->id }}">{{ '(' . $category->name . ') ' }}{{ $child->name }}</option>
                @endforeach
            @endforeach
        </x-adminlte-select2>

        @php
            $config = ['format' => 'DD/MM/YYYY HH:mm'];

            $today = \Carbon\Carbon::now('US/Eastern');
        @endphp

        <x-adminlte-input-date name="publish_date" :config="$config" placeholder="Choose a date and time..."
            label="Publish time" label-class="text-primary" value="{{ $today->format('d/m/Y H:i') }}">
            <x-slot name="appendSlot">
                <x-adminlte-button theme="outline-primary" icon="far fa-lg fa-calendar" title="Set Publish Date" />
            </x-slot>
        </x-adminlte-input-date>

        <x-adminlte-button class="btn-flat" type="submit" label="Submit" theme="success" icon="fas fa-lg fa-save" />
    </form>
@endsection

@push('js')
    <script type="text/javascript" src="{{ asset('vendor/uploadPreview/jquery.uploadPreview.min.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $.uploadPreview({
                input_field: "#image-upload",
                preview_box: "#image-preview",
                label_field: "#image-label"
            });
        });

        document.getElementById("header_video_upload").onchange = function(event) {
            let file = event.target.files[0];
            if (file) {
                const maxSize = 10 * 1024 * 1024;

                if (file.size > maxSize) {
                    alert("Header video must be less than 10MB.");
                    event.target.value = "";
                    return;
                }

                if (!file.type.startsWith("video/")) {
                    alert("Please upload a valid video file.");
                    event.target.value = "";
                    return;
                }
                if (file && file.type.startsWith("video/")) {
                    let blobURL = URL.createObjectURL(file);
                    let videoElement = document.getElementById("preview-header-video");
                    let videoSource = document.getElementById("header-video-source");

                    videoSource.src = blobURL;
                    videoElement.style.display = "block";
                    videoElement.load();
                }
            }
        };

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
                document.getElementById("videoPreview").src = blobURL;
            };
        $(document).ready(function() {
            $.uploadPreview({
                input_field: "#thumbnail-upload",
                preview_box: "#thumbnail-preview",
                label_field: "#thumbnail-label"
            });
        });

        $('input[name="header_type"]').on('change', function() {
            if ($(this).val() === 'image') {
                $('#header-image-input').show();
                $('#header-video-input').hide();
            } else {
                $('#header-image-input').hide();
                $('#header-video-input').show();
            }
        });
        document.getElementById("ad-form").addEventListener("submit", function(e) {
            const imageInput = document.getElementById("thumbnail-upload");
            const videoInput = document.getElementById("header_video_upload");

            if (imageInput.files.length > 0 && videoInput.files.length > 0) {
                e.preventDefault(); // Stop form submission
                alert("Only one of header image or video can be uploaded.");
                imageInput.value = "";
                videoInput.value = "";

                // Optionally hide preview if visible
                document.getElementById("preview-header-video").style.display = "none";
                $('#thumbnail-preview').css('background-image', 'none');

                return false;
            }
        });
    </script>
@endpush

@section('plugins.Select2', true)
@section('plugins.BsCustomFileInput', true)
@section('plugins.TempusDominusBs4', true)
