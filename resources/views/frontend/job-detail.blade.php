@extends('frontend.layout')

@content
<div class="space-y-4">
    <div class="mb-2">
        <a href="{{ route('dashboard') }}" class="text-sm text-blue-600 font-medium">&larr; Zurück zur Übersicht</a>
    </div>

    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 space-y-3">
        <h2 class="text-xl font-bold text-gray-900">{{ $job->title }}</h2>
        <p class="text-sm text-gray-600 whitespace-pre-wrap">{{ $job->description }}</p>
        
        <div class="pt-2 border-t border-gray-100 flex items-center justify-between">
            <span class="text-xs text-gray-400">Status: {{ $job->status }}</span>
            <form action="{{ route('jobs.update-status', $job) }}" method="POST">
                @csrf
                @if($job->status === 'pending')
                    <button type="submit" name="status" value="in_progress" class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm w-full">Arbeit starten</button>
                @elseif($job->status === 'in_progress')
                    <button type="submit" name="status" value="completed" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-sm w-full">Auftrag abschließen</button>
                @endif
            </form>
        </div>
    </div>

    @if($job->status === 'in_progress')
        <div class="space-y-4">
            @foreach($job->checklists as $checklist)
                <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 space-y-3">
                    <h3 class="font-bold text-gray-800 border-b border-gray-100 pb-2">{{ $checklist->name }}</h3>
                    <ul class="space-y-3">
                        @foreach($checklist->items as $item)
                            <li class="flex items-center gap-3">
                                <input type="checkbox" 
                                       id="item-{{ $item->id }}" 
                                       data-id="{{ $item->id }}"
                                       class="item-checkbox h-5 w-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                       {{ $item->is_checked ? 'checked' : '' }}>
                                <label自动 for="item-{{ $item->id }}" class="text-sm text-gray-700 {{ $item->is_checked ? 'line-through text-gray-400' : '' }}">
                                    {{ $item->task }}
                                </label>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    @endif
</div>

<script>
document.querySelectorAll('.item-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const itemId = this.dataset.id;
        const label = this.nextElementSibling;
        
        fetch(`/items/${itemId}/toggle`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (data.is_checked) {
                    label.classList.add('line-through', 'text-gray-400');
                } else {
                    label.classList.remove('line-through', 'text-gray-400');
                }
            }
        });
    });
});
</script>
@endsection
