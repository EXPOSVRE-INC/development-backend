@extends('adminlte::page')
@push('css')
<style type="text/css">
    #artist-image-preview {
        width: 25%;
        height: 354px;
        position: relative;
        overflow: hidden;
        background-color: #101010;
        color: #ecf0f1;
        margin-bottom: 20px
    }

    #artist-image-preview input {
        line-height: 200px;
        font-size: 200px;
        position: absolute;
        opacity: 0;
        z-index: 10;
    }

    #artist-image-preview label {
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

<form action="{{route('artist-create')}}" method="post" enctype="multipart/form-data" id="artistForm">
    {{csrf_field()}}
    <x-adminlte-input type="hidden" name="id"></x-adminlte-input>
    <input type="hidden" name="previous_url" value="{{ url()->previous() }}">
    <x-adminlte-input name="name" label="Name" placeholder="Name" label-class="text-lightblue">
        <x-slot name="prependSlot">
            <div class="input-group-text">
                <i class="fas fa-fw fa-user"></i>
            </div>
        </x-slot>
    </x-adminlte-input>
    <div id="artist-image-preview">
        <label for="image-upload" id="image-label">Choose Image File</label>
        <input type="file" name="profile_image" id="artist-image-upload" accept="image/*" />
        <!-- Ensure correct name here -->
    </div>
    <x-adminlte-button class="btn-flat btn-sm" type="submit" label="Submit" theme="success" icon="fas fa-lg fa-save" />
</form>

@endsection

@section('plugins.Select2', true)

@push('js')
<script type="text/javascript" src="{{asset('vendor/uploadPreview/jquery.uploadPreview.min.js')}}"></script>
<script type="text/javascript">
    $(document).ready(function() {
            $.uploadPreview({
                input_field: "#artist-image-upload",
                preview_box: "#artist-image-preview",
                label_field: "#image-label"
            });
        });

        document.getElementById("artistForm").addEventListener("submit", function(event) {
        const profileImageInput = document.getElementById("artist-image-upload");

        const file = event.target.files[0];
        const fileType = file.type.split('/')[0];
        if (!profileImageInput.files || profileImageInput.files.length === 0) {
            event.preventDefault();
            alert("Profile image is required. Please upload an image.");
            profileImageInput.focus();
        }
        });

</script>

@endpush
