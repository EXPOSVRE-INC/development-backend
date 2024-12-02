@extends('adminlte::page')

@push('css')
<link href="//cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/css/bootstrap4-toggle.min.css" rel="stylesheet">
@endpush
@section('content')
<div class="container">
    <div>
        <nav aria-label="breadcrumb" class="main-breadcrumb" style="margin-top: 2pc">
            <ol class="breadcrumb" style="width:74.7%; margin:0px">
                <h3 style="color: black;margin:auto; font-weight:600;">User Details</h3>
            </ol>
        </nav>
        <div class="row gutters-sm">
            <div class="col-md-9">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-center mb-5">
                                <div class="d-flex flex-column align-items-center  text-align-center">
                                    <img src="{{ $user->getFirstMediaUrl('preview') ?: asset('profileImg.jpeg') }}"
                                        onerror="this.onerror=null; this.src='{{ asset('profileImg.jpeg') }}';"
                                        alt="Admin" class="rounded-circle object-cover" width="150" height="150" />
                                    <div class="mt-3">
                                        <h4>{{($user->profile && $user->profile != null ? $user->profile->firstName . '
                                            ' .$user->profile->lastName : '')}}
                                            <span class="ml-2" style="color:#52d65d"> {!! $user->verify ? '<i
                                                    id="verify_status" class="far fa-fw fa-check-circle"
                                                    aria-hidden="true"></i>' : '<i id="verify_status"
                                                    class="far fa-fw fa-circle" aria-hidden="true"></i>' !!}</span>
                                        </h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="row">
                                <div class="col-sm-2">
                                    <h6 class="mb-0">UserName :</h6>
                                </div>
                                <div class="col-sm-10">
                                    {{$user->username}}
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-2">
                                    <h6 class="mb-0">Full Name : </h6>
                                </div>
                                <div class="col-sm-10">
                                    {{($user->profile && $user->profile != null) ? $user->profile->firstName . ' '
                                    .$user->profile->lastName : ''}}
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-2">
                                    <h6 class="mb-0">Email :</h6>
                                </div>
                                <div class="col-sm-10">
                                    {{$user->email}}
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-2">
                                    <h6 class="mb-0">Phone :</h6>
                                </div>
                                <div class="col-sm-10">
                                    {{($user->profile && $user->profile != null) ? $user->profile->phone : ''}}
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-2">
                                    <h6 class="mb-0">Job :</h6>
                                </div>
                                <div class="col-sm-10">
                                    {{($user->profile && $user->profile != null) ? $user->profile->jobTitle : '--'}}
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-2">
                                    <h6 class="mb-0">Job Description :</h6>
                                </div>
                                <div class="col-sm-10">
                                    {{($user->profile && $user->profile != null) ? $user->profile->jobDescription : '--'}}
                                </div>
                            </div>
                            <hr>
                            <div class="row ml-1">
                                <form id="myForm" name="myForm" action="{{route('users-verify')}}" method="post">
                                    <span style="margin-right: 10px">Verify :</span>
                                    <input id="toggle" class="ms-3" type="checkbox" data-onstyle="outline-danger"
                                        data-toggle="toggle" {{$user->verify ? 'checked' : ''}} >
                            </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
@push('js')
<script src="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/js/bootstrap4-toggle.min.js"></script>
<script>
    $('#toggle').change(function() {
            var mode = $(this).prop('checked');
            var verify = (mode == true) ? 1 : 0;
            $.ajax({
                type:'POST',
                dataType:'JSON',
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                url:'{{route('users-verify')}}',
                data: {
                    verify: verify,
                    userId: {{$user->id}}
                },
                success:function(data)
                {
                    if (data.verify == 1) {
                        $('#verify_status').attr('class', 'far fa-fw fa-check-circle');
                    } else {
                        $('#verify_status').attr('class', 'far fa-fw fa-circle');
                    }
                    // var data=eval(data);
                    // message=data.message;
                    // success=data.success;
                    // $("#heading").html(success);
                    // $("#body").html(message);
                }
            });
        });
</script>
@endpush
