@extends('layouts.master.base')
@section('customecss')
@include('layouts.admin-datatable-css')
@endsection
@section('content')
<div class="content-body">
    <div class="container-fluid">
        <div class="row page-titles mx-0">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="javascript:void(0)">Table</a>
                </li>
                <li class="breadcrumb-item active">
                    <a href="javascript:void(0)">Datatable</a>
                </li>
            </ol>
        </div>

        <!-- row -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card  card-bx m-b30">
                    <div class="card-header bg-primary">
                        <h6 class="title text-white">Create bestDeal</h6>

                    </div>
                    <form class="profile" data-action="{{ route('save.best.deal') }}" method="POST"
                        enctype="multipart/form-data" id="saveCourse">
                        <div class="card-body">
                            <div class="row">
                                <input type="hidden" name="id"
                                    value="{{$bestDeal != null ? base64_encode($bestDeal->id) : $id}}">
                                <div class="col-sm-6 mb-3">
                                    <label class=" form-label required">State</label>
                                    <div class="dropdown bootstrap-select default-select wide form-control dropdown">
                                        <select class="form-control select2" id="stateAll">
                                            <option>Select State</option>
                                            <option value="0" {{$bestDeal !=null && $bestDeal->state_id == 0 ?
                                                'selected' : ''}}>All</option>
                                            <option value="1" {{$bestDeal !=null && $bestDeal->state_id == 1 ?
                                                'selected' : ''}}>State</option>

                                        </select>
                                    </div>
                                    <input type="hidden" name="type" id="petRefCode"
                                        value="{{$bestDeal != null && $bestDeal->state_id ? $bestDeal->state_id : '0'}}">
                                </div>
                                <div class="col-sm-6 mb-3" id="stateSelected" style="display:none">
                                    <label class=" form-label required">State</label>
                                    <select class="form-control" name="state_id" multiples id="setstate">
                                        <option value="0">Select</option>
                                        @foreach($states as $key => $state)
                                        <option value="{{$state->id}}" {{$bestDeal !=null && $bestDeal->state &&
                                            $bestDeal->state->id == $state->id ?
                                            'selected' : ''}}>{{$state->state_title}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-6 mb-3" id="bulk" style="display:none">
                                    <label class=" form-label required">Bulk Exam</label>
                                    <select class="form-control" name="bulk_id" multiples id="setbulk">
                                        <option value="0">Select</option>
                                        @foreach($bulks as $key => $bul)
                                        <option value="{{$bul->id}}">{{$bul->name}}</option>

                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-sm-6 mb-3">
                                    <label class=" form-label">Package Name</label>
                                    <input type="text" class="form-control" name="package_name"
                                        placeholder="Enter Package Name"
                                        value="{{$bestDeal != null ? $bestDeal->package_name : ''}}">
                                </div>

                                <div class="col-sm-6 mb-3">
                                    <label class=" form-label">Image</label>
                                    <input type="file" class="form-control" name="image"
                                        placeholder="Enter Email Address"
                                        value="{{$bestDeal != null ? $bestDeal->image : ''}}">
                                </div>

                                <div class="col-sm-6 mb-3">
                                    <label class=" form-label">Price</label>
                                    <input type="number" class="form-control" name="price" placeholder="Enter price"
                                        value="{{$bestDeal != null ? $bestDeal->price : ''}}">
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <label class=" form-label required">Status</label>
                                    <div class="dropdown bootstrap-select default-select wide form-control dropdown">
                                        <select class="default-select wide form-control" required="" name="status">

                                            <option value="1" {{$bestDeal !=null && $bestDeal->status == 1 ?
                                                'selected' : ''}}>Active</option>
                                            <option value="0" {{$bestDeal !=null && $bestDeal->status == 0 ?
                                                'selected' : ''}}>InActive</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-12 mb-3">
                                    <div class="form-group">
                                        <label class=" form-label required">Description </label>
                                        <div class="position-relative">
                                            <input type="text" name="description" class="form-control"
                                                value="{{$bestDeal != null ? $bestDeal->description : ''}}"
                                                placeholder="Enter description" required="">

                                        </div>
                                    </div>
                                </div>



                                @foreach($bestdealfile as $key => $value)
                                <table>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <div class="col-sm-11 mb-3">
                                                    <label class=" form-label">Name</label>
                                                    <input type="text" class="form-control" name="name[]"
                                                        placeholder="Enter Name" value="{{$value->name}}">
                                                </div>
                                            </td>
                                            <td>
                                                <div class="col-sm-11 mb-3">
                                                    <label class=" form-label">Upload File</label>
                                                    <input type="file" class="form-control" name="files[]"
                                                        placeholder="Enter Name" value="{{$value->file}}">
                                                    <input type="hidden" name="existing_files[]"
                                                        value="{{ $value->file }}">
                                                </div>
                                            </td>
                                            <td>
                                                <div class="col-sm-11 mb-3">
                                                    <label class=" form-label">Upload Image</label>
                                                    <input type="file" class="form-control" name="images[]"
                                                        placeholder="Enter Name" value="{{$value->images}}">
                                                    <input type="hidden" name="existing_images[]"
                                                        value="{{ $value->images }}">
                                                </div>
                                            </td>
                                            <td>
                                                <a href="javascript:void(0);"
                                                    class="btn btn-danger shadow btn-xs sharp delete deleteRow" data-id="{{$value->id}}"><i
                                                        class="fa fa-trash"></i></a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                @endforeach
                                <table id="demoTable">
                                    @if($id == 'new')
                                    <div class="col-sm-4 mb-4">
                                        <label class=" form-label">Name</label>
                                        <input type="text" class="form-control" name="name[]" placeholder="Enter Name"
                                            value="">
                                    </div>
                                    <div class="col-sm-4 mb-4">
                                        <label class=" form-label">Upload File</label>
                                        <input type="file" class="form-control" name="files[]" placeholder="Enter Name"
                                            value="">
                                    </div>
                                    <div class="col-sm-4 mb-4">
                                        <label class=" form-label">Upload Image</label>
                                        <input type="file" class="form-control" name="images[]" placeholder="Enter Name"
                                            value="">
                                    </div>
                                    @endif

                                </table>
                                <div class="col-sm-3 mb-3">
                                    <div class="form-group">
                                        <label class=" form-label">Add More</label>

                                        <input type="button" class="form-control btn btn-warning"
                                            onclick="doTheInsert()" value="Add More" style="background:#ff7a01">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer justify-content-end">
                            <button type="submit" class="btn btn-primary">Create bestDeal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('customejs')
@include('layouts.admin-datatable-js')
@endsection
@section('script')

<script>
    $(document).on("click", '.delete', function () {

        $(this).closest('tr').remove();
    });
    function doTheInsert() {
        var table = document.getElementById('demoTable');

        var newRow = table.insertRow(-1);
        newRow.innerHTML = `
<td>
    <div class="col-sm-11 mb-3">
        <label class="form-label">Name</label>
        <input type="text" class="form-control" name="name[]" placeholder="Enter Name" value="">
    </div>
</td>
<td>
    <div class="col-sm-11 mb-3">
        <label class="form-label">Upload File</label>
        <input type="file" class="form-control" name="files[]" placeholder="Enter File">
    </div>
</td>
<td>
    <div class="col-sm-11 mb-3">
        <label class="form-label">Upload Image</label>
        <input type="file" class="form-control" name="images[]" placeholder="Enter Image">
    </div>
</td>
<td>
    <a href="javascript:void(0);" class="btn btn-danger shadow btn-xs sharp delete">
        <i class="fa fa-trash"></i>
    </a>
</td>`;
        newRow.querySelector('.delete').addEventListener('click', function () {
            table.deleteRow(newRow.rowIndex);
        });
    }
    $(document).ready(function () {
        $('#saveCourse').on('submit', function (event) {
            event.preventDefault();
            $('.textdanger').html('');
            var isValid = true;
            $('input[name="name[]"]').each(function () {
                if ($(this).val().trim() === '') {
                    $(this).after('<span class="text-strong textdanger">Name is required.</span>');
                    isValid = false;
                }
            });
            $('input[name="files[]"]').each(function (index) {
                var hasExistingFile = $('input[name="existing_files[]"]').eq(index).val();
                if ($(this).val() === '' && !hasExistingFile) {
                    $(this).after('<span class="text-strong textdanger">File upload is required.</span>');
                    isValid = false;
                }
            });
            $('input[name="images[]"]').each(function (index) {
                var hasExistingImage = $('input[name="existing_images[]"]').eq(index).val();
                if ($(this).val() === '' && !hasExistingImage) {
                    $(this).after('<span class="text-strong textdanger">Image upload is required.</span>');
                    isValid = false;
                }
            });
            if (isValid) {
                var url = $('#saveCourse').attr('data-action');
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    }
                });
                $.ajax({
                    url: url,
                    method: 'POST',
                    data: new FormData(this),
                    dataType: 'JSON',
                    contentType: false,
                    cache: false,
                    processData: false,
                    success: function (response) {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                            didOpen: (toast) => {
                                toast.addEventListener('mouseenter', Swal.stopTimer)
                                toast.addEventListener('mouseleave', Swal.resumeTimer)
                            }
                        })
                        Toast.fire({
                            icon: 'success',
                            title: 'Record saved successfully!!',
                            background: 'green',
                            color: '#fff'
                        }).then((value) => {
                            var redirectUrl = "{{ route('best.deal') }}";
                            window.location.href = redirectUrl;
                        })
                    },
                    error: function (response) {
                        $('.textdanger').html('');
                        $.each(response.responseJSON.errors, function (field_name, error) {
                            $(document).find('[name=' + field_name + ']').after(
                                '<span class="text-strong textdanger">' + error + '</span>')
                        })

                    },
                });
            }
        });

    });

    $("#stateAll").select2({
        templateResult: function (option, container) {
            if ($(option.element).attr("data-select2-id") == 0) {
                // $(container).css("display", "none");
            }

            return option.text;
        }
    });
    $(document).ready(function () {
        $('#stateAll').on('change', function () {
            var id = this.value;
            $('input[name="type"]').val(id);
            if (id == 1) {
                $('#stateSelected').css('display', 'block');
                $('#bulk').css('display', 'none');
                $('#setbulk').val('');
            }
            else {
                $('#stateSelected').css('display', 'none');
                $('#bulk').css('display', 'block');
                $('#setstate').val('');


            }
            $("#cources").html('');
            $.ajax({
                url: "{{ route('fetch.cources') }}",
                type: "POST",
                data: {
                    id: id,
                    _token: '{{csrf_token()}}'
                },
                dataType: 'json',
                success: function (result) {
                    $('#cources').html('<option value="">-- Select cources --</option>');
                    $.each(result.cources, function (key, value) {
                        $("#cources").append('<option value="' + value
                            .id + '">' + value.course_name + '</option>');
                    });
                }
            });
        });
    })
    $(document).ready(function () {
        $('#cources').on('change', function () {
            var id = this.value;
            $("#category").html('');
            $.ajax({
                url: "{{ route('fetch.category') }}",
                type: "POST",
                data: {
                    id: id,
                    _token: '{{csrf_token()}}'
                },
                dataType: 'json',
                success: function (result) {
                    $('#category').html('<option value="">-- Select category --</option>');
                    $.each(result.category, function (key, value) {
                        $("#category").append('<option value="' + value
                            .id + '">' + value.category_name + '</option>');
                    });
                }
            });
        });
    })

    $(document).on('click', '.deleteRow', function () {
    var id = $(this).attr('data-id');
        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
          }
        });
        $.ajax({
          url: "{{ route('delete.deal.file') }}",
          type: "DELETE",
          data: {
            id: id
          },
          success: function (data) {
            if (data.status == 200) {
            }
          },
          error: function () {
          }
        });
  });

</script>
@endsection