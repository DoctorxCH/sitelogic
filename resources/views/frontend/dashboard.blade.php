@extends('layouts.frontend')

@section('content')

<!-- Jobs Map -->
<div class="mb-10">
    <h2 class="text-2xl font-bold text-gray-900 mb-4">Aufträge Übersicht</h2>
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 mb-6">
        <div id="jobs_map" class="w-full h-96 rounded-lg border border-gray-200 shadow-sm" style="min-height: 400px;"></div>
        <div class="mt-4 grid grid-cols-3 gap-4 text-sm">
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded-full" style="background-color: #EAB308;"></div>
                <span>Ausstehend (Pending)</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded-full" style="background-color: #3B82F6;"></div>
                <span>In Bearbeitung (In Progress)</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded-full" style="background-color: #EF4444;"></div>
                <span>Abgelehnt (Rejected)</span>
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
            Pending Quality Reviews ({{ count($pendingReviews) }})
        </h2>
        <div class="bg-white shadow overflow-hidden sm:rounded-md border border-red-200">
            <ul class="divide-y divide-gray-200">
                @foreach($pendingReviews as $review)
                    <li>
                        <a href="{{ route('frontend.job.show', $review->job) }}" class="block hover:bg-gray-50">
                            <div class="px-4 py-4 sm:px-6 flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-blue-600 truncate">{{ $review->job->title }}</p>
                                    <p class="text-xs text-gray-500 mt-1">Checklist: <span class="font-semibold">{{ $review->name }}</span> | Submitted: {{ $review->submitted_at->diffForHumans() }}</p>
                                </div>
                                <div class="bg-yellow-100 text-yellow-800 text-xs font-bold px-3 py-1 rounded uppercase">
                                    Review Now
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

<div class="px-4 sm:px-0 mb-6">
    <h1 class="text-2xl font-semibold text-gray-900">Available Jobs</h1>
    <p class="mt-1 text-sm text-gray-600">Select a job to view details or manage operations.</p>
</div>

@if($jobs->isEmpty())
    <div class="bg-white rounded-lg shadow px-6 py-12 text-center">
        <h3 class="mt-2 text-sm font-medium text-gray-900">No jobs found</h3>
    </div>
@else
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($jobs as $job)
            <div class="bg-white overflow-hidden shadow rounded-lg flex flex-col border border-gray-200">
                <div class="px-4 py-5 sm:p-6 flex-grow">
                    <div class="flex items-center justify-between mb-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                            @if($job->status === 'pending') bg-gray-100 text-gray-800 
                            @elseif($job->status === 'in_progress') bg-yellow-100 text-yellow-800 
                            @else bg-green-100 text-green-800 @endif">
                            {{ ucfirst($job->status) }}
                        </span>
                        <span class="text-sm text-gray-500 font-medium uppercase">{{ $job->type }}</span>
                    </div>
                    <h3 class="text-md font-bold text-gray-900 mb-2 truncate">{{ $job->title }}</h3>
                    <div class="text-xs text-gray-500 space-y-1">
                        @if(isset($job->custom_fields['pid'])) <p><span class="font-medium">PID:</span> {{ $job->custom_fields['pid'] }}</p> @endif
                        @if(isset($job->custom_fields['address'])) <p class="truncate"><span class="font-medium">Location:</span> {{ $job->custom_fields['address'] }}</p> @endif
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 border-t">
                    <a href="{{ route('frontend.job.show', $job) }}" class="w-full text-center block text-sm font-medium text-blue-600 hover:text-blue-800">
                        Open Job &rarr;
                    </a>
                </div>
            </div>
        @endforeach
    </div>
    <div class="mt-6">{{ $jobs->links() }}</div>
@endif

<!-- Leaflet CSS & JS for Map -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>

<script>
@php
    $jobsForMap = $jobs->map(function($job) {
        return [
            'id' => $job->id,
            'title' => $job->title,
            'status' => $job->status,
            'custom_fields' => $job->custom_fields,
            'show_url' => route('frontend.job.show', $job)
        ];
    });
@endphp

const jobsData = @json($jobsForMap);

function getMarkerColor(status) {
    switch(status) {
        case 'pending':
            return '#EAB308'; // Yellow
        case 'in_progress':
            return '#3B82F6'; // Blue
        case 'rejected':
            return '#EF4444'; // Red
        default:
            return '#6B7280'; // Gray
    }
}

function initJobsMap() {
    const mapElement = document.getElementById('jobs_map');
    if (!mapElement) {
        console.error('Map element not found');
        return;
    }

    console.log('Total jobs:', jobsData.length);

    // Filter jobs with coordinates and status in ['pending', 'in_progress', 'rejected']
    const jobsWithCoords = jobsData.filter(job => {
        const hasCoords = job.custom_fields && job.custom_fields.target_latitude && job.custom_fields.target_longitude;
        const validStatus = ['pending', 'in_progress', 'rejected'].includes(job.status);
        console.log(`Job ${job.id} (${job.title}):`, { hasCoords, validStatus, lat: job.custom_fields?.target_latitude, lng: job.custom_fields?.target_longitude });
        return hasCoords && validStatus;
    });

    console.log('Jobs with coords:', jobsWithCoords.length);

    if (jobsWithCoords.length === 0) {
        mapElement.innerHTML = '<div class="w-full h-full flex items-center justify-center text-gray-500">Keine Aufträge mit Koordinaten vorhanden</div>';
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

    console.log('Bounds:', { minLat, maxLat, minLng, maxLng });

    try {
        const map = L.map('jobs_map', {
            center: [47.5, 8.5],
            zoom: 8
        });
        
        console.log('Map created');

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19,
        }).addTo(map);

        console.log('TileLayer added');

        // Add markers
        jobsWithCoords.forEach((job, index) => {
            const lat = parseFloat(job.custom_fields.target_latitude);
            const lng = parseFloat(job.custom_fields.target_longitude);
            const color = getMarkerColor(job.status);

            console.log(`Adding marker ${index} at`, lat, lng, 'color:', color);

            // Create SVG icon instead of divIcon
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
                    <p style="margin: 6px 0; font-size: 12px;"><span style="font-weight: bold;">Status:</span> <span style="text-transform: uppercase; color: ${color};">●</span> ${job.status}</p>
                    ${job.custom_fields.pid ? `<p style="margin: 6px 0; font-size: 12px;"><span style="font-weight: bold;">PID:</span> ${job.custom_fields.pid}</p>` : ''}
                    ${job.custom_fields.address ? `<p style="margin: 6px 0; font-size: 12px;"><span style="font-weight: bold;">Adresse:</span> ${job.custom_fields.address}</p>` : ''}
                    <p style="margin: 10px 0 0 0;"><a href="${job.show_url}" style="color: #3B82F6; text-decoration: none; font-size: 12px; font-weight: bold;">→ Öffnen</a></p>
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
                console.warn('FitBounds failed, using default view');
                map.setView([(minLat + maxLat) / 2, (minLng + maxLng) / 2], 8);
            }
        }

        console.log(`✅ Karte mit ${jobsWithCoords.length} Aufträgen initialisiert`);
    } catch (error) {
        console.error('❌ Fehler beim Initialisieren der Auftrags-Karte:', error);
        mapElement.innerHTML = `<div class="w-full h-full flex flex-col items-center justify-center text-red-500">
            <p style="font-weight: bold;">Fehler beim Laden der Karte</p>
            <p style="font-size: 12px;">${error.message}</p>
        </div>`;
    }
}

document.addEventListener('DOMContentLoaded', function () {
    console.log('DOM loaded, initializing map...');
    if (typeof L === 'undefined') {
        console.error('❌ Leaflet.js not loaded!');
        return;
    }
    initJobsMap();
});
</script>

@endsection
