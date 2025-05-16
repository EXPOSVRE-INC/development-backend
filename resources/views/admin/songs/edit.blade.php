@extends('adminlte::page')

@push('css')
    <style type="text/css">
        .newArtistText {
            text-decoration: underline !important;
            color: #ffffff;
            font-size: 18px;
            padding-bottom: 12px;
            display: flex;
            font-weight: 600;
            width: fit-content;
        }

        #image-preview {
            width: 100%;
            height: 400px;
            position: relative;
            overflow: hidden;
            background-color: #101010;
            color: #ecf0f1;
        }

        #image-preview input {
            line-height: 200px;
            font-size: 200px;
            position: absolute;
            opacity: 0;
            z-index: 10;
        }

        #image-preview label {
            position: absolute;
            z-index: 5;
            opacity: 0.8;
            cursor: pointer;
            background-color: #e83e8c;
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

        .fileContainer {
            display: flex;
            text-align: center;
            align-items: center;
        }

        .clipText {
            padding-right: 20px;
        }

        .audioSong {
            width: 18%;
            height: 30px;
        }
    </style>
@endpush

@section('content')

    <form action="{{ route('song-edit', $song->id) }}" method="post" enctype="multipart/form-data" id="uploadForm"
        onsubmit="validateClipDuration(event)">
        {{ csrf_field() }}

        <!-- Image Upload Field with Preview -->
        <div id="image-preview">
            <label for="image-upload" id="image-label">Choose Image File</label>
            <input type="file" name="image_file" id="image-upload" />
            <!-- Show the existing image if available -->
            @if ($song->image_file)
                <img src="{{ $song->image_file }}" alt="Song Image" id="song-preview-image">
            @else
                <img id="song-preview-image" src="" alt="Song Image" style="display: none;">
            @endif
        </div>

        @php
            $config = [
                'placeholder' => 'Select option...',
                'allowClear' => true,
            ];
        @endphp

        <!-- Full Song File Upload -->
        <x-adminlte-input-file name="full_song_file" label="Upload Music File" placeholder="Upload Music File"
            label-class="text-lightblue">
            <x-slot name="prependSlot">
                <div class="input-group-text">
                    <i class="fas fa-music text-lightblue"></i>
                </div>
            </x-slot>
        </x-adminlte-input-file>

        <!-- Display currently uploaded full song file -->
        @if (isset($song->full_song_file))
            <div class="mt-3 fileContainer mb-3">
                <strong class="clipText">Uploaded Song:</strong>
                <audio controls class="audioSong">
                    <source src="{{ $song->full_song_file }}" type="audio/mpeg">
                    Your browser does not support the audio tag.
                </audio>
            </div>
        @endif

        <x-adminlte-input-file name="clip_30_sec" label="Upload 30 second clip of song"
            placeholder="Upload 30 second clip of song" label-class="text-lightblue">
            <x-slot name="prependSlot">
                <div class="input-group-text">
                    <i class="fas fa-play text-lightblue"></i>
                </div>
            </x-slot>
        </x-adminlte-input-file>

        <!-- Display currently uploaded 15-second clip -->
        @if (isset($song->clip_15_sec))
            <div class="mt-3 fileContainer mb-3">
                <strong class="clipText">Uploaded Clip:</strong>
                <audio controls class="audioSong">
                    <source src="{{ $song->clip_15_sec }}" type="audio/mpeg">
                    Your browser does not support the audio tag.
                </audio>
                <input type="hidden" id="existing_clip_duration" value="30">
            </div>
        @endif


        <!-- Artist Select Dropdown -->
        <x-adminlte-select2 id="artist_id" name="artist_id" label="Artist" label-class="text-lightblue" igroup-size="md"
            igroup-size="sm" :config="$config">
            <x-slot name="prependSlot">
                <div class="input-group-text bg-gradient-red">
                    <i class="fas fa-user"></i>
                </div>
            </x-slot>
            <x-slot name="appendSlot">
                <x-adminlte-button theme="outline-dark" label="Clear" icon="fas fa-lg fa-ban text-danger" />
            </x-slot>
            @foreach ($artists as $artist)
                <option value="{{ $artist->id }}" {{ $song->artist_id == $artist->id ? 'selected' : '' }}>
                    {{ $artist->name }}
                </option>
            @endforeach
        </x-adminlte-select2>


        <!-- Title Input Field -->
        <x-adminlte-input name="title" label="Title" placeholder="Title" label-class="text-lightblue"
            value="{{ old('title', $song->title) }}">
            <x-slot name="prependSlot">
                <div class="input-group-text">
                    <i class="fas fa-heading text-lightblue"></i>
                </div>
            </x-slot>
        </x-adminlte-input>

        <!-- Description Textarea -->
        <x-adminlte-textarea name="description" label="Description" rows=5 label-class="text-lightblue" igroup-size="sm"
            placeholder="Insert description...">
            {{ old('description', $song->description) }}
            <x-slot name="prependSlot">
                <div class="input-group-text bg-dark">
                    <i class="fas fa-lg fa-file-alt text-warning"></i>
                </div>
            </x-slot>
        </x-adminlte-textarea>

        <!-- Genre Dropdown -->
        <x-adminlte-select2 id="genre_id" name="genre_id" label="Genre" label-class="text-lightblue" igroup-size="md"
            igroup-size="sm" :config="$config">
            <x-slot name="prependSlot">
                <div class="input-group-text bg-gradient-red">
                    <i class="fas fa-list"></i>
                </div>
            </x-slot>
            <x-slot name="appendSlot">
                <x-adminlte-button theme="outline-dark" label="Clear" icon="fas fa-lg fa-ban text-danger" />
            </x-slot>
            @foreach ($genres as $genre)
                <option value="{{ $genre->id }}" {{ $song->genre_id == $genre->id ? 'selected' : '' }}>
                    {{ $genre->name }}
                </option>
            @endforeach
        </x-adminlte-select2>

        <!-- Mood Dropdown -->
        <x-adminlte-select2 id="mood_id" name="mood_id" label="Mood" label-class="text-lightblue" igroup-size="md"
            igroup-size="sm" :config="$config">
            <x-slot name="prependSlot">
                <div class="input-group-text bg-gradient-red">
                    <i class="fas fa-icons"></i>
                </div>
            </x-slot>
            <x-slot name="appendSlot">
                <x-adminlte-button theme="outline-dark" label="Clear" icon="fas fa-lg fa-ban text-danger" />
            </x-slot>
            @foreach ($moods as $mood)
                <option value="{{ $mood->id }}" {{ $song->mood_id == $mood->id ? 'selected' : '' }}>
                    {{ $mood->name }}
                </option>
            @endforeach
        </x-adminlte-select2>

        <!-- Submit Button -->
        <x-adminlte-button class="btn-flat" type="submit" label="Update Song" theme="success"
            icon="fas fa-lg fa-save" />

    </form>

@endsection

@push('js')
    <script type="text/javascript" src="{{ asset('vendor/uploadPreview/jquery.uploadPreview.min.js') }}"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/getID3/0.8.0/getID3.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $.uploadPreview({
                input_field: "#image-upload",
                preview_box: "#image-preview",
                label_field: "#image-label"
            });
        });

        document.getElementById('image-upload').addEventListener('change', function(event) {
            const previewImage = document.getElementById('song-preview-image');
            const file = event.target.files[0];

            if (file) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    previewImage.style.display = 'block';
                };

                reader.readAsDataURL(file);
            } else {
                previewImage.src = '';
                previewImage.style.display = 'none';
            }
        });
    </script>
    <script>
        function validateClipDuration(event) {
            event.preventDefault();

            const clipFile = document.getElementById('clip_30_sec').files[0];
            const existingDuration = parseInt(document.getElementById('existing_clip_duration').value);

            if (!clipFile) {
                if (existingDuration >= 30 && existingDuration < 32) {
                    document.getElementById('uploadForm').submit();
                } else {
                    alert('The existing audio file must be 30 seconds long.');
                }
            }

            const audio = new Audio();
            audio.src = URL.createObjectURL(clipFile);
            audio.onloadedmetadata = function() {
                const duration = Math.round(audio.duration);

                if (duration >= 30 && duration < 32) {
                    document.getElementById('uploadForm').submit();
                } else {
                    alert('The audio file must be 30 seconds long.');
                }
            };
        }
    </script>
@endpush

@section('plugins.Select2', true)
@section('plugins.BsCustomFileInput', true)
@section('plugins.TempusDominusBs4', true)
