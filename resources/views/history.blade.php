@extends('feature-flags::layouts.app')

@section('title', 'Features - History')

@section('description', 'Displays a chronological log of all changes made to feature flags')



@push('styles')
@endpush

@section('content')
    @include('feature-flags::partials.header')
    <div class="controls">
        <form method="GET" action="{{ route('feature-flags.history') }}">
            <input type="text" name="filter" value="{{ request('filter') }}" placeholder="Filter history..."
                class="search-box">
        </form>
    </div>

    <div class="table-container">
        @if ($histories->count() !== 0)
            <table id="featuresTable">
                <thead>
                    <tr>
                        <th>Feature Key</th>
                        <th>Enabled</th>
                        <th>Event</th>
                        <th>Metadata</th>
                        <th>Environments</th>
                        <th>Changed_by</th>
                        <th>Changed_at</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($histories as $history)
                        @php
                            $eventColors = [
                                'created' => 'info',
                                'updated' => 'warning',
                                'deleted' => 'danger',
                            ];

                            $color = $eventColors[$history->event] ?? 'secondary';
                        @endphp
                        <tr>
                            <td>{{ $history->key }}</td>
                            <td>
                                <div class="toggle {{ $history->enabled ? 'active' : '' }}"></div>
                            </td>
                            <td> <span class="badge bg-{{ $color }}">{{ $history->event }}</span>
                            </td>
                            <td>{{ json_encode($history->metadata) }}</td>
                            <td>{{ json_encode($history->environments) }}</td>
                            <td>{{ $history->changed_by }}</td>
                            <td class="timestamp">{{ \Carbon\Carbon::parse($history->updated_at)->diffForHumans() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Pagination links -->
            <div class="pagination pt-5 justify-content-center">
                {{ $histories->links('pagination::bootstrap-5') }}
            </div>
        @else
            <h2 class="text-center p-3">Empty history</h2>
        @endif
    </div>
@endsection
