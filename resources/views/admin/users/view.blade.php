@extends('adminlte::page')

@push('css')
    <link href="//cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/css/bootstrap4-toggle.min.css" rel="stylesheet">
@endpush
@section('content')

<div class="container emp-profile">
        <div class="row">
            <div class="col-md-4">
                <div class="profile-img">
                    <img src="{{$user->getFirstMediaUrl('preview')}}" alt=""/>
                </div>
            </div>
            <div class="col-md-6">
                <div class="profile-head">
                    <h5>
                        {{($user->profile && $user->profile != null ? $user->profile->firstName . ' ' .$user->profile->lastName : '')}}
                        {!! $user->verify ? '<i id="verify_status" class="far fa-fw fa-check-circle" aria-hidden="true"></i>' : '<i id="verify_status" class="far fa-fw fa-circle" aria-hidden="true"></i>' !!}
                    </h5>
                    <h6>
                        {{($user->profile && $user->profile != null) ? $user->profile->jobTitle : ''}}
                    </h6>
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">About</a>
                        </li>
{{--                        <li class="nav-item">--}}
{{--                            <a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="false">Timeline</a>--}}
{{--                        </li>--}}
                    </ul>
                </div>
            </div>
            <div class="col-md-2">
                <form id="myForm" name="myForm" action="{{route('users-verify')}}" method="post">
                    Verify:  <input id="toggle" type="checkbox" data-onstyle="outline-danger" data-toggle="toggle" {{$user->verify ? 'checked' : ''}}>
                </form>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">

            </div>
            <div class="col-md-8">
                <div class="tab-content profile-tab" id="myTabContent">
                    <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                        <div class="row">
                            <div class="col-md-6">
                                <label>Username</label>
                            </div>
                            <div class="col-md-6">
                                <p>{{$user->username}}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label>Full Name</label>
                            </div>
                            <div class="col-md-6">
                                <p>{{($user->profile && $user->profile != null) ? $user->profile->firstName . ' ' .$user->profile->lastName : ''}}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label>Email</label>
                            </div>
                            <div class="col-md-6">
                                <p>{{$user->email}}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label>Phone</label>
                            </div>
                            <div class="col-md-6">
                                <p>{{($user->profile && $user->profile != null) ? $user->profile->phone : ''}}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label>Job</label>
                            </div>
                            <div class="col-md-6">
                                <p>{{($user->profile && $user->profile != null) ? $user->profile->jobTitle : ''}}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label>Job Description</label>
                            </div>
                            <div class="col-md-6">
                                <p>{{($user->profile && $user->profile != null) ? $user->profile->jobDescription : ''}}</p>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                        <div class="row">
                            <div class="col-md-6">
                                <label>Experience</label>
                            </div>
                            <div class="col-md-6">
                                <p>Expert</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label>Hourly Rate</label>
                            </div>
                            <div class="col-md-6">
                                <p>10$/hr</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label>Total Projects</label>
                            </div>
                            <div class="col-md-6">
                                <p>230</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label>English Level</label>
                            </div>
                            <div class="col-md-6">
                                <p>Expert</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label>Availability</label>
                            </div>
                            <div class="col-md-6">
                                <p>6 months</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <label>Your Bio</label><br/>
                                <p>Your detail description</p>
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
