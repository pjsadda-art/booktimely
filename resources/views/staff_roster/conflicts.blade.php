@extends('layouts.main')

@section('page-title')
    {{ __('Roster Conflicts') }}
@endsection

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5>{{ __('Appointments Not Matching the New Roster') }}</h5>
            <p class="text-muted mb-0">{{ __('Appointments on or after :date whose staff has no covering roster shift, or whose time falls outside it. Nothing here has been changed automatically — review and reassign as needed.', ['date' => \App\Services\RosterService::ACTIVATION_DATE]) }}</p>
        </div>
        <div class="card-body">
            @if ($conflicts->isEmpty())
                <p class="text-muted mb-0">{{ __('No conflicts found.') }}</p>
            @else
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Time') }}</th>
                                <th>{{ __('Staff') }}</th>
                                <th>{{ __('Service') }}</th>
                                <th>{{ __('Customer') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($conflicts as $conflict)
                                <tr>
                                    <td>{{ $conflict['date'] }}</td>
                                    <td>{{ $conflict['time'] }}</td>
                                    <td>{{ $conflict['staff'] }}</td>
                                    <td>{{ $conflict['service'] }}</td>
                                    <td>{{ $conflict['customer'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
