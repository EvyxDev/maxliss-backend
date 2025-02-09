@extends('backend.layouts.app')

@section('content')
@if (env('MAIL_USERNAME') == null && env('MAIL_PASSWORD') == null)
<div class="alert alert-info d-flex align-items-center">
    {{ translate('You need to configure SMTP correctly to to add Expert.') }}
    <a class="alert-link ml-2" href="{{ route('smtp_settings.index') }}">{{ translate('Configure Now') }}</a>
</div>
@endif

<div class="aiz-titlebar text-left mt-2 mb-3">
    <h5 class="mb-0 h6">{{translate('Add New Expert')}}</h5>
</div>

<div class="col-lg-9 mx-auto">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">{{translate('Expert Information')}}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('experts.store') }}" method="POST">
                @csrf
                <div class="form-group row">
                    <label class="col-sm-4 col-from-label" for="name">
                        {{translate('Name')}} <span class="text-danger">*</span>
                    </label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control @if ($errors->has('name')) is-invalid @endif" name="name" value="{{ old('name') }}" placeholder="{{ translate('Name') }}" required>
                        @if ($errors->has('name'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('name') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-4 col-from-label" for="email">
                        {{ translate('Email') }} <span class="text-danger">*</span>
                    </label>
                    <div class="col-sm-8">
                        <input type="email" class="form-control rounded-0 @if($errors->has('email')) is-invalid @endif" value="{{ old('email') }}" placeholder="{{  translate('Email') }}" name="email">
                        @if ($errors->has('email'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('email') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-4 col-from-label" for="phone">
                        {{ translate('Phone') }} <span class="text-danger">*</span>
                    </label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control rounded-0 @if($errors->has('phone')) is-invalid @endif" value="{{ old('phone') }}" placeholder="{{  translate('Phone') }}" name="phone">
                        @if ($errors->has('phone'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('phone') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>


                <div class="form-group row">
                    <label class="col-sm-4 col-from-label" for="state">{{ translate('State') }}</label>
                    <div class="col-sm-8">
                        <select class="form-control rounded-0" name="state_id" id="state">
                            <option value="">{{ translate('Select State') }}</option>
                            @foreach ($states as $state)
                            <option value="{{ $state->id }}" data-cities="{{ json_encode($state->cities) }}">
                                {{ $state->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-4 col-from-label" for="city">{{ translate('City') }}</label>
                    <div class="col-sm-8">
                        <select class="form-control rounded-0" name="city_id" id="city">
                            <option value="">{{ translate('Select City') }}</option>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-4 col-from-label" for="experience">{{ translate('Experience') }}</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control rounded-0 @if($errors->has('experience')) is-invalid @endif" value="{{ old('experience') }}" placeholder="{{  translate('Experience') }}" name="experience">
                        @if ($errors->has('experience'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('experience') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-4 col-from-label" for="price">{{ translate('Price') }}</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control rounded-0 @if($errors->has('price')) is-invalid @endif" value="{{ old('price') }}" placeholder="{{  translate('Price') }}" name="price">
                        @if ($errors->has('price'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('price') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>

                
                <div class="form-group row">
                    <label class="col-sm-4 col-from-label" for="user">{{ translate('User') }}</label>
                    <div class="col-sm-8">
                        <select class="form-control rounded-0" name="user_id" id="user">
                            <option value="">{{ translate('Select User') }}</option>
                            @foreach ($users as $user)
                            <option value="{{ $user->id }}" >
                                {{ $user->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-4 col-from-label" for="password">{{ translate('Password') }}</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control rounded-0 @if($errors->has('password')) is-invalid @endif" value="{{ old('password') }}" placeholder="{{  translate('Password') }}" name="password">
                        @if ($errors->has('password'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('password') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-4 col-from-label" for="password_confirmation">{{ translate('Confirm Password') }}</label>
                    <div class="col-sm-8">
                        <input type="password" class="form-control rounded-0 @if($errors->has('password_confirmation')) is-invalid @endif" value="{{ old('password_confirmation') }}" placeholder="{{ translate('Confirm Password') }}" name="password_confirmation">
                        @if ($errors->has('password_confirmation'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('password_confirmation') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>

                <div class="form-group mb-0 text-right">
                    <button type="submit" class="btn btn-primary">{{translate('Save')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')

<script>
    $(document).ready(function() {
        $('#state').on('change', function() {
            var cities = $(this).find(':selected').data('cities');
            $('#city').html('<option value="">{{ translate("Select City") }}</option>');

            if (cities) {
                $.each(cities, function(key, city) {
                    $('#city').append('<option value="' + city.id + '">' + city.name + '</option>');
                });
            }
        });
    });
</script>

@endsection