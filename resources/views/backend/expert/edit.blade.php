@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <h5 class="mb-0 h6">{{translate('Edit Expert Information')}}</h5>
</div>

<div class="col-lg-9 mx-auto">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">{{translate('Expert Information')}}</h5>
        </div>

        <div class="card-body">
            <form action="{{ route('experts.update', $expert->id) }}" method="POST">
                <input name="_method" type="hidden" value="PATCH">
                @csrf
                <div class="form-group row">
                    <label class="col-sm-4 col-from-label" for="name">{{translate('Name')}}</label>
                    <div class="col-sm-8">
                        <input type="text" placeholder="{{translate('Name')}}" id="name" name="name" class="form-control" value="{{$expert->name}}" required>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-4 col-from-label" for="email">{{translate('Email Address')}}</label>
                    <div class="col-sm-8">
                        <input type="text" placeholder="{{translate('Email Address')}}" id="email" name="email" class="form-control" value="{{$expert->email}}" required>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-4 col-from-label" for="phone">{{translate('Phone')}}</label>
                    <div class="col-sm-8">
                        <input type="text" placeholder="{{translate('Phone')}}" id="phone" name="phone" class="form-control" value="{{$expert->phone}}" required>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-4 col-from-label" for="phone">{{translate('Experience')}}</label>
                    <div class="col-sm-8">
                        <input type="text" placeholder="{{translate('experience')}}" id="experience" name="experience" class="form-control" value="{{$expert->experience}}" required>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-4 col-from-label" for="phone">{{translate('Price')}}</label>
                    <div class="col-sm-8">
                        <input type="text" placeholder="{{translate('price')}}" id="price" name="price" class="form-control" value="{{$expert->price}}" required>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-4 col-from-label" for="stastate_idte">{{translate('State')}}</label>
                    <div class="col-sm-8">
                        <select name="state_id" id="state_id" class="form-control" required>
                            <option value="">{{translate('Select State')}}</option>
                            @foreach($states as $state)
                            <option value="{{ $state->id }}" {{ $state->id == $expert->state_id ? 'selected' : '' }}>{{ $state->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- City Dropdown (Display cities based on selected state) -->
                <div class="form-group row">
                    <label class="col-sm-4 col-from-label" for="city">{{translate('City')}}</label>
                    <div class="col-sm-8">
                        <select name="city_id" id="city_id" class="form-control" required>
                            <option value="">{{translate('Select City')}}</option>
                            @foreach($states as $state)
                            @if($state->id == $expert->state_id)
                            @foreach($state->cities as $city)
                            <option value="{{ $city->id }}" {{ $city->id == $expert->city_id ? 'selected' : '' }}>{{ $city->name }}</option>
                            @endforeach
                            @endif
                            @endforeach
                        </select>
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
    document.addEventListener('DOMContentLoaded', function() {
        const stateSelect = document.getElementById('state_id');
        const citySelect = document.getElementById('city_id');
        const citiesByState = {!!json_encode($states -> mapWithKeys(function($state) {return [$state -> id => $state -> cities];})) !!};
        stateSelect.addEventListener('change', function() {
            const selectedStateId = this.value;
            citySelect.innerHTML = '<option value="">{{translate('Select City')}}</option>';
            if (selectedStateId && citiesByState[selectedStateId]) {
                citiesByState[selectedStateId].forEach(function(city) {
                    const option = document.createElement('option');
                    option.value = city.id;
                    option.text = city.name;
                    citySelect.appendChild(option);
                });
            }
        });
    });
</script>
@endsection