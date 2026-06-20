<div class="mb-2">
    @if($path)
        <a href="{{ asset('storage/' . $path) }}" target="_blank">
            <img src="{{ asset('storage/' . $path) }}" alt="Checklist Photo" class="h-64 object-cover rounded border shadow-sm hover:opacity-75 transition">
        </a>
    @else
        <span class="text-sm text-gray-500">No photo uploaded</span>
    @endif
</div>
