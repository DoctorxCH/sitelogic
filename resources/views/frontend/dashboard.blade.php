@extends('frontend.layout')

@content
<div class="space-y-4">
    <h2 class="text-xl font-bold text-gray-800">Meine aktuellen Aufträge</h2>
    
    @if($jobs->isEmpty())
        <div class="bg-white p-6 rounded-xl shadow-sm text-center text-gray-500">
            Aktuell keine zugewiesenen Aufträge vorhanden.
        </div>
    @else
        <div class="space-y-3">
            @foreach($jobs as $job)
                <a href="{{ route('jobs.show', $job) }}" class="block bg-white p-4 rounded-xl shadow-sm border border-gray-200 hover:border-blue-500 transition-all">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="font-semibold text-gray-900">{{ $job->title }}</h3>
                        <span class="px-2 py-1 text-xs font-semibold rounded {{ $job->status === 'in_progress' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $job->status === 'in_progress' ? 'In Arbeit' : 'Ausstehend' }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-500 line-clamp-2 mb-2">{{ $job->description }}</p>
                    <div class="text-xs text-gray-400">Erstellt: {{ $job->created_at->format('d.m.Y H:i') }} Uhr</div>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
