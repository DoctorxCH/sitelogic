@extends('layouts.frontend')

@section('content')

<!-- Jobs Map -->
<div class="mb-10">
    <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('main.jobs_overview') }}</h2>
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 mb-6">
        <div id="jobs_map" class="w-full h-96 rounded-lg border border-gray-200 shadow-sm" style="min-height: 400px;"></div>
        <div class="mt-4 grid grid-cols-4 gap-4 text-sm">
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded-full" style="background-color: #3B82F6;"></div>
                <span>{{ __('main.status_pending') }}</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded-full" style="background-color: #EAB308;"></div>
                <span>{{ __('main.status_in_progress') }}</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded-full" style="background-color: #EF4444;"></div>
                <span>{{ __('main.status_rejected') }}</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded-full" style="background-color: #22C55E;"></div>
                <span>{{ __('main.status_completed') }} (24h)</span>
            </div>
        </div>
    </div>
</div>

@if($isManager && count($pendingReviews) > 0)
    <div class="mb-10">
        <h2 class="text-xl font-bold text-red-600 mb-4 flex items-center">
            <span class="flex h-3 w-3 relative mr-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
            </span>
            {{ __('main.pending_quality_reviews') }} ({{ count($pendingReviews) }})
        </h2>
        <div class="bg-white shadow overflow-hidden sm:rounded-md border border-red-200">
            <ul class="divide-y divide-gray-200">
                @foreach($pendingReviews as $review)
                    <li>
                        <a href="{{ route('frontend.job.show', $review->job) }}" class="block hover:bg-gray-50">
                            <div class="px-4 py-4 sm:px-6 flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-blue-600 truncate">{{ $review->job->title }}</p>
                                    <p class="text-xs text-gray-500 mt-1">{{ __('main.checklist') }}: <span class="font-semibold">{{ $review->name }}</span> | {{ __('main.submitted') }}: {{ $review->submitted_at->diffForHumans() }}</p>
                                </div>
                                <div class="bg-yellow-100 text-yellow-800 text-xs font-bold px-3 py-1 rounded uppercase">
                                    {{ __('main.review_now') }}
                                </div>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
    <hr class="mb-10 border-gray-300">
@endif

@php
    $myTabFilters = [
        'all'        => __('main.filter_all'),
        'pending'    => __('main.status_pending'),
        'in_progress'=> __('main.status_in_progress'),
        'rejected'   => __('main.status_rejected'),
        'completed'  => __('main.status_completed'),
    ];
    $myTabColors = [
        'all'        => 'bg-gray-800 text-white',
        'pending'    => 'bg-blue-600 text-white',
        'in_progress'=> 'bg-yellow-500 text-white',
        'rejected'   => 'bg-red-600 text-white',
        'completed'  => 'bg-green-600 text-white',
    ];
    $myTabInactive = 'bg-white text-gray-600 border border-gray-300 hover:bg-gray-50';
@endphp

<div class="px-4 sm:px-0 mb-4 mt-8 flex items-center justify-between flex-wrap gap-3">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">{{ __('main.my_jobs') }}</h1>
        <p class="mt-1 text-sm text-gray-600">{{ __('main.jobs_assigned_to_you') }}</p>
    </div>
    <div class="flex flex-wrap gap-2">
        @foreach($myTabFilters as $value => $label)
            @php
                $isActive = $myStatusFilter === $value;
                $params = array_merge(request()->query(), ['my_status' => $value === 'all' ? null : $value]);
                $params = array_filter($params, fn($v) => !is_null($v) && $v !== '');
            @endphp
            <a href="{{ route('frontend.dashboard') }}?{{ http_build_query($params) }}"
               class="px-3 py-1.5 rounded-full text-xs font-semibold transition {{ $isActive ? $myTabColors[$value] : $myTabInactive }}">
                {{ $label }}
            </a>
        @endforeach
    </div>
</div>

@if($myJobs->isEmpty())
    <div class="bg-white rounded-lg shadow px-6 py-12 text-center mb-10">
        <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('main.no_assigned_jobs_found') }}</h3>
    </div>
@else
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 mb-6">
        @foreach($myJobs as $job)
            <div class="bg-white overflow-hidden shadow rounded-lg flex flex-col border border-gray-200">
                <div class="px-4 py-5 sm:p-6 flex-grow">
                    <div class="flex items-center justify-between mb-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                            @if($job->status === 'pending') bg-blue-100 text-blue-800 
                            @elseif($job->status === 'in_progress') bg-yellow-100 text-yellow-800
                            @elseif($job->status === 'completed') bg-green-100 text-green-800
                            @elseif($job->status === 'rejected') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ ucfirst($job->status) }}
                        </span>
                        <span class="text-sm text-gray-500 font-medium uppercase">{{ $job->type }}</span>
                    </div>
                    <h3 class="text-md font-bold text-gray-900 mb-2 truncate">{{ $job->title }}</h3>
                    <div class="text-xs text-gray-500 space-y-1">
                        @if(isset($job->custom_fields['pid'])) <p><span class="font-medium">PID:</span> {{ $job->custom_fields['pid'] }}</p> @endif
                        @if(isset($job->custom_fields['address'])) <p class="truncate"><span class="font-medium">{{ __('main.location') }}:</span> {{ $job->custom_fields['address'] }}</p> @endif
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 border-t">
                    <a href="{{ route('frontend.job.show', $job) }}" class="w-full text-center block text-sm font-medium text-blue-600 hover:text-blue-800">
                        {{ __('main.open_job') }} &rarr;
                    </a>
                </div>
            </div>
        @endforeach
    </div>
    <div class="mt-6 mb-10">{{ $myJobs->links() }}</div>
@endif

@php
    $generalTabFilters = [
        'all'        => __('main.filter_all'),
        'pending'    => __('main.status_pending'),
        'in_progress'=> __('main.status_in_progress'),
        'rejected'   => __('main.status_rejected'),
        'completed'  => __('main.status_completed'),
    ];
    $generalTabColors = [
        'all'        => 'bg-gray-800 text-white',
        'pending'    => 'bg-blue-600 text-white',
        'in_progress'=> 'bg-yellow-500 text-white',
        'rejected'   => 'bg-red-600 text-white',
        'completed'  => 'bg-green-600 text-white',
    ];
    $generalTabInactive = 'bg-white text-gray-600 border border-gray-300 hover:bg-gray-50';
@endphp

<div class="px-4 sm:px-0 mb-4 mt-10 flex items-center justify-between flex-wrap gap-3">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">{{ __('main.general_jobs') }}</h1>
        <p class="mt-1 text-sm text-gray-600">{{ __('main.available_unassigned_jobs') }}</p>
    </div>
    <div class="flex flex-wrap gap-2">
        @foreach($generalTabFilters as $value => $label)
            @php
                $isActive = $generalStatusFilter === $value;
                $params = array_merge(request()->query(), ['general_status' => $value === 'all' ? null : $value]);
                $params = array_filter($params, fn($v) => !is_null($v) && $v !== '');
            @endphp
            <a href="{{ route('frontend.dashboard') }}?{{ http_build_query($params) }}"
               class="px-3 py-1.5 rounded-full text-xs font-semibold transition {{ $isActive ? $generalTabColors[$value] : $generalTabInactive }}">
                {{ $label }}
            </a>
        @endforeach
    </div>
</div>

@if($generalJobs->isEmpty())
    <div class="bg-white rounded-lg shadow px-6 py-12 text-center">
        <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('main.no_general_jobs_found') }}</h3>
    </div>
@else
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($generalJobs as $job)
            <div class="bg-white overflow-hidden shadow rounded-lg flex flex-col border border-gray-200">
                <div class="px-4 py-5 sm:p-6 flex-grow">
                    <div class="flex items-center justify-between mb-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                            @if($job->status === 'pending') bg-blue-100 text-blue-800
                            @elseif($job->status === 'in_progress') bg-yellow-100 text-yellow-800
                            @elseif($job->status === 'completed') bg-green-100 text-green-800
                            @elseif($job->status === 'rejected') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ ucfirst($job->status) }}
                        </span>
                        <span class="text-sm text-gray-500 font-medium uppercase">{{ $job->type }}</span>
                    </div>
                    <h3 class="text-md font-bold text-gray-900 mb-2 truncate">{{ $job->title }}</h3>
                    <div class="text-xs text-gray-500 space-y-1">
                        @if(isset($job->custom_fields['pid'])) <p><span class="font-medium">PID:</span> {{ $job->custom_fields['pid'] }}</p> @endif
                        @if(isset($job->custom_fields['address'])) <p class="truncate"><span class="font-medium">{{ __('main.location') }}:</span> {{ $job->custom_fields['address'] }}</p> @endif
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 border-t">
                    <a href="{{ route('frontend.job.show', $job) }}" class="w-full text-center block text-sm font-medium text-blue-600 hover:text-blue-800">
                        {{ __('main.open_job') }} &rarr;
                    </a>
                </div>
            </div>
        @endforeach
    </div>
    <div class="mt-6">{{ $generalJobs->links() }}</div>
@endif

<!-- Leaflet CSS & JS for Map -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>

<script>
// Use the $allMapJobs collection for the map
const jobsData = {!! json_encode($allMapJobs->map(function($job) {
    return [
        'id' => $job->id,
        'title' => $job->title,
        'status' => $job->status,
        'completed_at' => $job->completed_at ? $job->completed_at->toISOString() : null,
        'custom_fields' => $job->custom_fields ?? [],
        'show_url' => route('frontend.job.show', $job)
    ];
})) !!};

function getMarkerColor(status) {
    switch(status) {
        case 'pending':
            return '#3B82F6'; // Blue
        case 'in_progress':
            return '#EAB308'; // Yellow
        case 'rejected':
            return '#EF4444'; // Red
        case 'completed':
            return '#22C55E'; // Green
        default:
            return '#6B7280'; // Gray
    }
}

function initJobsMap() {
    const mapElement = document.getElementById('jobs_map');
    if (!mapElement || !Array.isArray(jobsData)) {
        return;
    }

    // Filter jobs with coordinates and a valid/recent status
    const now = Date.now();
    const msIn24h = 24 * 60 * 60 * 1000;
    const jobsWithCoords = jobsData.filter(job => {
        const hasCoords = job.custom_fields && job.custom_fields.target_latitude && job.custom_fields.target_longitude;
        if (!hasCoords) return false;
        if (job.status === 'completed') {
            // Only show completed jobs from the last 24h
            if (!job.completed_at) return false;
            return (now - new Date(job.completed_at).getTime()) <= msIn24h;
        }
        return ['pending', 'in_progress', 'rejected'].includes(job.status);
    });

    if (jobsWithCoords.length === 0) {
        mapElement.innerHTML = '<div class="w-full h-full flex items-center justify-center text-gray-500">{{ __('main.no_jobs_with_coordinates') }}</div>';
        return;
    }

    // Calculate map bounds
    let minLat = Infinity, maxLat = -Infinity, minLng = Infinity, maxLng = -Infinity;
    jobsWithCoords.forEach(job => {
        const lat = parseFloat(job.custom_fields.target_latitude);
        const lng = parseFloat(job.custom_fields.target_longitude);
        minLat = Math.min(minLat, lat);
        maxLat = Math.max(maxLat, lat);
        minLng = Math.min(minLng, lng);
        maxLng = Math.max(maxLng, lng);
    });

    try {
        const map = L.map('jobs_map', {
            center: [47.5, 8.5],
            zoom: 8
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19,
        }).addTo(map);

        // Add markers
        jobsWithCoords.forEach((job) => {
            const lat = parseFloat(job.custom_fields.target_latitude);
            const lng = parseFloat(job.custom_fields.target_longitude);
            const color = getMarkerColor(job.status);

            // Create SVG icon
            const svgIcon = `
                <svg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="20" cy="20" r="16" fill="${color}" stroke="white" stroke-width="3"/>
                    <circle cx="20" cy="20" r="5" fill="white"/>
                </svg>
            `;

            const icon = L.icon({
                iconUrl: 'data:image/svg+xml;base64,' + btoa(svgIcon),
                iconSize: [40, 40],
                iconAnchor: [20, 20],
                popupAnchor: [0, -20]
            });

            const marker = L.marker([lat, lng], { icon: icon }).addTo(map);
            
            const popupContent = `
                <div style="min-width: 220px; font-family: sans-serif;">
                    <p style="font-weight: bold; margin: 0 0 10px 0; font-size: 14px;">${job.title}</p>
                    <p style="margin: 6px 0; font-size: 12px;"><span style="font-weight: bold;">{{ __('main.status') }}:</span> <span style="text-transform: uppercase; color: ${color};">●</span> ${job.status}</p>
                    ${job.custom_fields.pid ? `<p style="margin: 6px 0; font-size: 12px;"><span style="font-weight: bold;">PID:</span> ${job.custom_fields.pid}</p>` : ''}
                    ${job.custom_fields.address ? `<p style="margin: 6px 0; font-size: 12px;"><span style="font-weight: bold;">{{ __('main.address') }}:</span> ${job.custom_fields.address}</p>` : ''}
                    <p style="margin: 10px 0 0 0;"><a href="${job.show_url}" style="color: #3B82F6; text-decoration: none; font-size: 12px; font-weight: bold;">→ {{ __('main.open') }}</a></p>
                </div>
            `;
            
            marker.bindPopup(popupContent);
        });

        // Fit bounds
        if (jobsWithCoords.length === 1) {
            const lat = parseFloat(jobsWithCoords[0].custom_fields.target_latitude);
            const lng = parseFloat(jobsWithCoords[0].custom_fields.target_longitude);
            map.setView([lat, lng], 13);
        } else {
            try {
                map.fitBounds([[minLat, minLng], [maxLat, maxLng]], { padding: [50, 50] });
            } catch(e) {
                map.setView([(minLat + maxLat) / 2, (minLng + maxLng) / 2], 8);
            }
        }
    } catch (error) {
    }
}

document.addEventListener('DOMContentLoaded', function () {
    if (typeof L !== 'undefined') {
        initJobsMap();
    }
});
</script>

@endsection
