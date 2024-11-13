@extends('adminlte::page')
@push('css')
<style type="text/css">

    #artist-image-edit-preview {
        width: 25%;
        height: 354px;
        position: relative;
        overflow: hidden;
        background-color: #101010;
        color: #ecf0f1;
        margin-bottom: 20px;
        display: flex;
        justify-content: center;
    }

    #artist-image-edit-preview input {
        line-height: 200px;
        font-size: 200px;
        position: absolute;
        opacity: 0;
        z-index: 10;
    }

    #artist-image-edit-preview label {
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
</style>

@section('content')
    <form action="{{route('artist-edit', $artist->id)}}" method="post" enctype="multipart/form-data">
        {{csrf_field()}}
        <x-adminlte-input type="hidden" name="id" value="{{ $id ?? '' }}"></x-adminlte-input>
        <input type="hidden" name="previous_url" value="{{ url()->previous() }}">
            <x-adminlte-input name="name" label="Name" placeholder="Name" label-class="text-lightblue" value="{{$artist->name ?? ''}}">
            <x-slot name="prependSlot">
                <div class="input-group-text">
                    <i class="fas fa-fw fa-user"></i>
                </div>
            </x-slot>
        </x-adminlte-input>

        <div id="artist-image-edit-preview">
            <label for="artist-image-upload" id="artist-image-label">Choose Image File</label>
            <input type="file" name="profile_image" id="artist-image-upload" />
            @if($artist->profile_image)
                <img id="artist-preview-image" src="{{ $artist->profile_image }}" alt="Artist Image">
            @else
                <img id="artist-preview-image" src="" alt="Artist Image" style="display: none;">
            @endif
        </div>
        <x-adminlte-button class="btn-flat btn-sm" type="submit" label="Submit" theme="success" icon="fas fa-lg fa-save"/>
    </form>

@endsection

@section('plugins.Select2', true)

@push('js')
{{-- <script type="text/javascript" src="//code.jquery.com/jquery-2.0.3.min.js"></script>--}}
<script type="text/javascript" src="{{asset('vendor/uploadPreview/jquery.uploadPreview.min.js')}}"></script>
<script type="text/javascript">
    $(document).ready(function() {
            $.uploadPreview({
                input_field: "#artist-image-upload",
                preview_box: "#artist-image-edit-preview",
                label_field: "#artist-image-label"
            });
        });

        document.getElementById('artist-image-upload').addEventListener('change', function(event) {
        const previewImage = document.getElementById('artist-preview-image');
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

@endpush
