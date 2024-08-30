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
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4 class="card-title">Export Buttons</h4>

            <a class="btn btn-rounded btn-info" href="{{route('add.best.deal','new')}}">
              <span class="btn-icon-start text-info">
                <i class="fa fa-plus color-info"></i>
              </span>
              Add
            </a>
          </div>

          <div class="card-body">
            <div class="table-responsive">
              <table id="example-2" class="display" style="min-width: 850px">
                <thead>
                  <tr>
                    <th>Sr.No.</th>
                    <th>State</th>
                    <th>Bulk Exam Name</th>
                    <th>Pakage Name</th>
                    <th>Image</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Universal</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($bestdeal as $key => $package)
                  <tr id="remove{{$package->id}}">
                    <td>{{$key + 1}}</td>
                    <td>{{$package->state != null ? $package->state->state_title : 'All'}}</td>
                    <td>{{$package->bulk != null ? $package->bulk->name : 'N/A'}}</td>
                    <td>{{$package->package_name}}</td>

                   
                    <td><img src="{{$package->image}}" height="50" width="50"></td>
                    <td>{{$package->price}}</td>
                    <td>
                      <div class="form-check form-switch">
                        <input class="form-check-input switch" data-id="{{$package->id}}" type="checkbox" role="switch"
                          {{$package->status == 1 ? 'checked' :
                        ''}}>
                        <label class="form-check-label switch-label{{$package->id}}" for="status">{{$package->status == 1
                          ? 'Active' :
                          'InActive'}}</label>
                      </div>
                    </td>
                    <td>
                      <div class="form-check form-switch">
                        <input class="form-check-input universalSwitch" data-id="{{$package->id}}" type="checkbox" role="switch"
                          {{$package->universal_status == 1 ? 'checked' :
                        ''}}>
                        <label class="form-check-label universalSwitch-label{{$package->id}}" for="status">{{$package->universal_status == 1
                          ? 'Active' :
                          'InActive'}}</label>
                      </div>
                    </td>
                    <td>
                      <div class="d-flex">
                        <a href="{{route('add.best.deal',base64_encode($package->id))}}"
                          class="btn btn-primary shadow btn-xs sharp me-1 openModel"><i
                            class="fas fa-pencil-alt"></i></a>

                        <a href="javascript:void(0);" class="btn btn-danger shadow btn-xs sharp delete"
                          data-id="{{$package->id}}"><i class="fa fa-trash"></i></a>
                      </div>
                    </td>

                  </tr>
                  @endforeach
                </tbody>

              </table>
            </div>
          </div>
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
  $(document).ready(function () {
    $('.switch').change(function () {
      var id = $(this).attr('data-id');
      if ($(this).is(':checked')) {
        $('.switch-label' + id).text('Active');
        status = 1;
      } else {
        $('.switch-label' + id).text('InActive');
        status = 0;
      }
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': "{{ csrf_token() }}"
        }
      });

      $.ajax({

        url: "{{ route('best.deal.status') }}",
        type: "PUT",
        data: { id: id, status: status },
        success: function (data) {

        }
      });

    });
  });

  $(document).ready(function () {
    $('.universalSwitch').change(function () {
      var id = $(this).attr('data-id');
      if ($(this).is(':checked')) {
        $('.universalSwitch-label' + id).text('Active');
        status = 1;
      } else {
        $('.universalSwitch-label' + id).text('InActive');
        status = 0;
      }
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': "{{ csrf_token() }}"
        }
      });

      $.ajax({

        url: "{{ route('best.deal.universalSwitch') }}",
        type: "PUT",
        data: { id: id, status: status },
        success: function (data) {

        }
      });

    });
  });

 
  $(document).on('click', '.delete', function () {
    var id = $(this).attr('data-id');

    Swal.fire({
      title: "Are you sure?",
      text: "You won't be able to revert this!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Yes, delete it!"
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
          }
        });
        $.ajax({
          url: "{{ route('delete.best.deal') }}",
          type: "DELETE",
          data: {
            id: id
          },
          success: function (data) {
            if (data.status == 200) {
              $('#remove' + data.id).hide();
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
              });
              Toast.fire({
                icon: 'success',
                title: 'Record deleted successfully!',
                background: 'green',
                color: '#fff'
              });
            }
          },
          error: function () {
          }
        });
      }
    });
  });

</script>
@endsection