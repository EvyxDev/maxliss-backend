@extends('backend.layouts.app')

@section('content')

@php
$route = Route::currentRouteName() == 'experts.index' ? 'all_expert_route' : 'expert_rating_followers';
@endphp

<div class="aiz-titlebar text-left mt-2 mb-3">
    <div class="row align-items-center">
        <div class="col-auto">
            <h1 class="h3">{{ $route == 'all_expert_route' ? translate('All Expert') : translate('Expert Review & Followers ')}}</h1>
        </div>
        @if(auth()->user()->can('add_seller'))
        <div class="col text-right">
            <a href="{{ route('experts.create') }}" class="btn btn-circle btn-info">
                <span>{{ translate('Add New Expert')}}</span>
            </a>
        </div>
        @endif
    </div>
</div>

<div class="card">
    {{-- Show Validation Errors --}}
    @if ($errors->any())
        @foreach ($errors->all() as $error)
            <div class="alert alert-danger">
                {{ $error }}
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endforeach
    @endif

    <form class="" id="sort_sellers" action="" method="GET">
        <div class="card-header row gutters-5">
            <div class="col">
                <h5 class="mb-md-0 h6">{{ $route == 'all_expert_route' ? translate('Expert') : translate('Expert Review & Followers ') }}</h5>
            </div>
            @if($route == 'all_expert_route')
            <div class="col-lg-2 ml-auto">
                <select class="form-control aiz-selectpicker" name="verification_status" onchange="sort_sellers()" data-selected="{{ $verification_status }}">
                    <option value="">{{ translate('Filter by Verification Status') }}</option>
                    <option value="verified">{{ translate('Verified') }}</option>
                    <option value="un_verified">{{ translate('Unverified') }}</option>
                </select>
            </div>
            <div class="col-md-2 ml-auto">
                <select class="form-control aiz-selectpicker" name="approved_status" id="approved_status" onchange="sort_sellers()">
                    <option value="">{{translate('Filter by Approval')}}</option>
                    <option value="1" @isset($approved) @if($approved=='1' ) selected @endif @endisset>{{translate('Approved')}}</option>
                    <option value="0" @isset($approved) @if($approved=='0' ) selected @endif @endisset>{{translate('Non-Approved')}}</option>
                </select>
            </div>
            @endif
            <div class="col-md-3">
                <div class="form-group mb-0">
                    <input type="text" class="form-control" id="search" name="search" @isset($sort_search) value="{{ $sort_search }}" @endisset placeholder="{{ translate('Type name or email & Enter') }}">
                </div>
            </div>
        </div>

        <div class="card-body">
            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        <th>{{translate('Name')}}</th>
                        <th data-breakpoints="lg">{{translate('Phone')}}</th>
                        <th data-breakpoints="lg">{{translate('Email Address')}}</th>
                        <th data-breakpoints="lg">{{translate('Experience')}}</th>
                        <th data-breakpoints="lg">{{translate('State')}}</th>
                        <th data-breakpoints="lg">{{translate('City')}}</th>
                        <th data-breakpoints="lg">{{translate('Amount')}}</th>
                        <th data-breakpoints="lg">{{translate('Total Transactions')}}</th>
                        <th data-breakpoints="lg">{{translate('User')}}</th>
                        <th data-breakpoints="lg">{{translate('Rating')}}</th>
                        <th width="10%">{{translate('Options')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($experts as $key => $expert)
                    <tr>
                        <td>
                            <div class="row gutters-5  mw-100 align-items-center">
                                <div class="col-auto">
                                    <img src="{{ uploaded_asset($expert->logo) }}" class="size-40px img-fit" alt="Image" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/placeholder.jpg') }}';">
                                </div>
                                <div class="col">
                                    <span class="text-truncate-2">{{ $expert->name }}</span>
                                </div>
                            </div>
                        </td>
                        <td>{{$expert->phone}}</td>
                        <td>{{$expert->email}}</td>
                        <td>{{$expert->experience}}</td>
                        <td>{{$expert->state->name}}</td>
                        <td>{{$expert->city->name}}</td>
                        <td>{{$expert->amount}}</td>
                        <td>{{$expert->transactions->sum('amount')}}</td> 
                        <td>{{$expert->user->name}}</td>
                        <td>
                            {{ $expert->rating }}
                            <span class="rating rating-sm m-0 ml-1">
                                @for ($i=0; $i < $expert->rating; $i++)
                                    <i class="las la-star active"></i>
                                    @endfor
                                    @for ($i=0; $i < 5-$expert->rating; $i++)
                                        <i class="las la-star"></i>
                                        @endfor
                            </span>
                        </td>
                        <td>
                            <div class="dropdown d-flex">
                                <button type="button" class="btn btn-sm btn-circle btn-soft-primary btn-icon dropdown-toggle no-arrow" data-toggle="dropdown" href="javascript:void(0);" role="button" aria-haspopup="true" aria-expanded="false">
                                    <i class="las la-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right dropdown-menu-xs">
                                    <a href="{{ route('experts.edit', encrypt($expert->id)) }}" class="dropdown-item">
                                        {{ translate('Edit') }}
                                    </a>
                                    <button type="button" class="dropdown-item" data-toggle="modal" data-target="#add-expert-transaction-{{ $expert->id }}" href="javascript:void(0);" role="button" aria-haspopup="true" aria-expanded="false">
                                        {{ translate('Add Transaction') }}
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="aiz-pagination">
                {{ $experts->appends(request()->input())->links() }}
            </div>
        </div>
    </form>
    
    {{-- Add Modal Bootstrap Add New Transaction for expert --}}
    <div class="modal fade" id="add-expert-transaction-{{ $expert->id }}" tabindex="-1" role="dialog" aria-labelledby="add-expert-transaction-{{ $expert->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form action="{{ route('expert.transaction') }}" method="POST">
                    @csrf
                    <input type="hidden" name="expert_id" value="{{ $expert->id }}">
                    <div class="modal-header">
                        <h5 class="modal-title h6">{{ translate('Add New Transaction') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true" class="la-2x">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="amount">{{ translate('Amount') }}</label>
                            <input type="number" class="form-control" name="amount" id="amount" max="{{ $expert->amount }}" required>                            {{-- Body --}}
                            <label for="body">{{ translate('Body') }}</label>
                            <textarea class="form-control" name="body" id="body" required></textarea>
                            {{-- Date --}}
                            <label for="date">{{ translate('date') }}</label>
                            <input type="date" class="form-control" name="date" id="date" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('Close') }}</button>
                        <button type="submit" class="btn btn-primary">{{ translate('Save changes') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
@section('script')
<script type="text/javascript">
    $(document).on("change", ".check-all", function() {
        if (this.checked) {
            // Iterate each checkbox
            $('.check-one:checkbox').each(function() {
                this.checked = true;
            });
        } else {
            $('.check-one:checkbox').each(function() {
                this.checked = false;
            });
        }

    });






    function sort_sellers(el) {
        $('#sort_sellers').submit();
    }
</script>
@endsection
