@extends('layouts.frontend')

@section('content')
<div class="max-w-6xl mx-auto">
    <!-- Header & Navigation -->
    <div class="mb-8 flex items-center justify-between">
        <a href="{{ route('frontend.dashboard') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            <span class="text-sm font-medium">Zurück</span>
        </a>
        @if($job->status === 'pending' && !$isManager)
            <form action="{{ route('frontend.job.status', $job) }}" method="POST">
                @csrf
                <input type="hidden" name="status" value="in_progress">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition shadow-sm">
                    Job Starten
                </button>
            </form>
        @endif
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm flex items-start gap-3">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm flex items-start gap-3">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Main Content -->
    @php
        $customFields = collect($job->custom_fields ?? [])->filter(function ($value) {
            return !(is_null($value) || $value === '' || (is_array($value) && count($value) === 0));
        });
        $totalChecklistItems = $job->checklists->sum(function ($checklist) {
            return $checklist->items->count();
        });
    @endphp

    <!-- Job Header Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-8 mb-8">
        <div class="flex items-start justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-1">{{ $job->title }}</h1>
                <p class="text-gray-500 text-sm">Job #{{ $job->id }}</p>
            </div>
            <div class="text-right">
                <span class="inline-block px-4 py-2 rounded-full text-sm font-semibold
                    @if($job->status === 'pending') bg-yellow-100 text-yellow-800
                    @elseif($job->status === 'in_progress') bg-blue-100 text-blue-800
                    @elseif($job->status === 'completed') bg-green-100 text-green-800
                    @else bg-gray-100 text-gray-800 @endif">
                    {{ ucfirst($job->status) }}
                </span>
            </div>
        </div>

        <!-- Key Info Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 pb-6 border-b border-gray-200">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Typ</p>
                    @if(!$isManager && $job->status === 'in_progress')
                        <button type="button" onclick="openBepTypeModal()" class="text-gray-400 hover:text-gray-600 transition p-1" title="BEP Typ ändern">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </button>
                    @endif
                </div>
                <p class="text-lg font-semibold text-gray-900">{{ $job->bepType?->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1 font-semibold">Techniker</p>
                <p class="text-sm font-semibold text-gray-900">{{ $job->technician?->name ?? 'Nicht zugewiesen' }}</p>
                @if($job->technician?->email)
                    <p class="text-xs text-gray-500">{{ $job->technician->email }}</p>
                @endif
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1 font-semibold">Erstellt</p>
                <p class="text-sm font-semibold text-gray-900">{{ $job->created_at?->format('d.m.Y') }}</p>
                <p class="text-xs text-gray-500">{{ $job->created_at?->format('H:i') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1 font-semibold">Checklisten</p>
                <p class="text-lg font-semibold text-gray-900">{{ $job->checklists->count() }}</p>
                <p class="text-xs text-gray-500">{{ $totalChecklistItems }} Punkte</p>
            </div>
        </div>

        <!-- Description & Custom Fields -->
        @if($job->description || $customFields->isNotEmpty() || ($job->latitude && $job->longitude))
            <div class="space-y-4">
                @if($job->description)
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide mb-2 font-semibold">Beschreibung</p>
                        <p class="text-gray-700 text-sm leading-relaxed">{{ $job->description }}</p>
                    </div>
                @endif

                @if(isset($job->custom_fields['target_latitude']) && isset($job->custom_fields['target_longitude']) && $job->custom_fields['target_latitude'] && $job->custom_fields['target_longitude'])
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide mb-2 font-semibold">Standort</p>
                        <div id="job_map" class="w-full h-64 rounded-lg border border-gray-200 shadow-sm bg-gray-50"></div>
                        <p class="text-xs text-gray-500 mt-2">📍 {{ $job->custom_fields['target_latitude'] }}, {{ $job->custom_fields['target_longitude'] }}</p>
                    </div>
                @else
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide mb-2 font-semibold">Standort</p>
                        <div class="w-full h-64 rounded-lg border border-gray-200 bg-gray-100 flex items-center justify-center text-gray-500">
                            <span>Keine Koordinaten verfügbar</span>
                        </div>
                    </div>
                @endif

                @if($customFields->isNotEmpty())
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide mb-2 font-semibold">Zusätzliche Details</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach($customFields as $key => $value)
                                <div class="flex items-start gap-2">
                                    <span class="text-gray-500 text-sm font-medium min-w-fit">{{ $fieldLabels[$key] ?? ucwords(str_replace('_', ' ', $key)) }}:</span>
                                    <span class="text-gray-900 text-sm font-semibold">
                                        @if(is_array($value))
                                            {{ implode(', ', array_filter($value, function ($item) { return !is_null($item) && $item !== ''; })) }}
                                        @elseif(is_bool($value))
                                            {{ $value ? 'Ja' : 'Nein' }}
                                        @else
                                            {{ $value }}
                                        @endif
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>

    <!-- Checklists Section -->
    <div class="space-y-4">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Checklisten</h2>

        @forelse($job->checklists as $checklist)
            <div x-data="{ open: false }" class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition">
                
                <!-- Checklist Header -->
                <div class="p-6 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100 cursor-pointer hover:from-gray-100 transition" @click="open = !open">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="text-lg font-bold text-gray-900">{{ $checklist->name }}</h3>
                                <span class="px-3 py-1 rounded-full text-xs font-semibold
                                    @if($checklist->status === 'approved') bg-green-100 text-green-800
                                    @elseif($checklist->status === 'submitted') bg-yellow-100 text-yellow-800
                                    @elseif($checklist->status === 'rejected') bg-red-100 text-red-800
                                    @elseif($checklist->status === 'disabled') bg-gray-100 text-gray-600
                                    @else bg-blue-100 text-blue-800 @endif">
                                    {{ ucfirst($checklist->status) }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-600">{{ $checklist->items->count() }} Punkte</p>
                            @if(in_array($checklist->status, ['approved', 'rejected']) && $checklist->reviewer)
                                <p class="text-xs text-gray-500 mt-2">Überprüft von: <span class="font-semibold">{{ $checklist->reviewer->name }}</span></p>
                            @endif
                        </div>
                        <button type="button" class="text-gray-400 hover:text-gray-600 transition">
                            <svg class="w-6 h-6 transform transition" x-bind:class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Checklist Content -->
                <div x-show="open" x-transition class="p-6 space-y-4" style="display: none;">
                    
                    @if($checklist->status === 'submitted' && $isManager)
                        <!-- Manager Review Form -->
                        <form action="{{ route('frontend.checklist.review', $checklist) }}" method="POST" class="space-y-4">
                            @csrf
                            @foreach($checklist->items as $item)
                                <div class="p-4 rounded-lg border {{ $item->status === 'disabled' ? 'bg-gray-50 border-gray-200 opacity-60' : 'bg-white border-gray-200 hover:border-gray-300' }} transition">
                                    <div class="flex items-start justify-between mb-3">
                                        <p class="font-semibold text-gray-900">{{ $item->task }}</p>
                                        @if($item->status === 'disabled')
                                            <span class="text-xs bg-gray-200 text-gray-600 px-2 py-1 rounded">Vom Techniker deaktiviert</span>
                                        @endif
                                    </div>
                                    
                                    @if($item->status !== 'disabled')
                                        @php
                                            $itemPhotoUrls = $item->photos->pluck('photo_path')->map(fn ($path) => asset('storage/' . $path));
                                            if ($itemPhotoUrls->isEmpty() && $item->photo_path) {
                                                $itemPhotoUrls = collect([asset('storage/' . $item->photo_path)]);
                                            }
                                        @endphp
                                        
                                        @if($itemPhotoUrls->isNotEmpty())
                                            <div class="grid grid-cols-3 gap-2 mb-3">
                                                @foreach($itemPhotoUrls as $photoUrl)
                                                    <img src="{{ $photoUrl }}" onclick="openPhotoLightbox('{{ $photoUrl }}')" class="h-20 w-full object-cover rounded border cursor-zoom-in hover:opacity-80 transition">
                                                @endforeach
                                            </div>
                                        @endif

                                        @if($item->technician_comment)
                                            <p class="text-sm text-gray-700 bg-blue-50 border border-blue-100 rounded p-2 mb-3 italic">💬 {{ $item->technician_comment }}</p>
                                        @endif

                                        <div x-data="{ decision: 'approved' }" class="space-y-3">
                                            <div class="flex flex-wrap gap-4">
                                                <label class="flex items-center gap-2 cursor-pointer">
                                                    <input type="radio" name="items[{{ $item->id }}][status]" value="approved" x-model="decision" checked class="w-4 h-4">
                                                    <span class="text-sm font-medium text-green-700">✓ Akzeptieren</span>
                                                </label>
                                                <label class="flex items-center gap-2 cursor-pointer">
                                                    <input type="radio" name="items[{{ $item->id }}][status]" value="rejected" x-model="decision" class="w-4 h-4">
                                                    <span class="text-sm font-medium text-red-700">✗ Ablehnen</span>
                                                </label>
                                            </div>
                                            <input type="text" name="items[{{ $item->id }}][manager_comment]" :required="decision === 'rejected'" placeholder="Notiz hinzufügen..." class="w-full text-sm p-2 border rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent" :class="decision === 'rejected' ? 'border-red-300 bg-red-50' : 'border-gray-300'">
                                        </div>
                                    @else
                                        <input type="hidden" name="items[{{ $item->id }}][status]" value="approved">
                                    @endif
                                </div>
                            @endforeach
                            <button type="submit" class="w-full bg-red-600 text-white font-bold py-3 rounded-lg hover:bg-red-700 transition shadow-sm mt-6">
                                Überprüfung abschließen
                            </button>
                        </form>
                    @else
                        <!-- Technician/View Mode -->
                        <div class="space-y-3">
                            @if($checklist->status === 'pending' && $job->status === 'in_progress' && !$isManager)
                                <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg flex items-center justify-between">
                                    <p class="text-sm text-gray-700">Keine Arbeiten notwendig?</p>
                                    <form action="{{ route('frontend.checklist.disable', $checklist) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-4 py-2 bg-gray-200 text-gray-700 rounded font-semibold hover:bg-gray-300 transition">Deaktivieren</button>
                                    </form>
                                </div>
                            @endif

                            <div id="checklist_items_{{ $checklist->id }}" class="space-y-6">
                                @foreach($checklist->items as $item)
                                    @php
                                        $canEditItem = $checklist->status === 'pending' && in_array($item->status, ['pending', 'rejected']) && $job->status === 'in_progress' && !$isManager;
                                    @endphp
                                    <div id="item_card_{{ $item->id }}" class="p-5 rounded-lg border-2 {{ $item->status === 'rejected' ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-white' }} transition shadow-sm hover:shadow-md">
                                        <div class="flex items-start justify-between mb-3">
                                            <div class="flex-1">
                                                <p class="font-semibold text-gray-900 text-base">{{ $item->task }}</p>
                                                @if($item->status === 'submitted' && $item->last_edited_by_email)
                                                    <p class="text-xs text-gray-500 mt-1">✓ Eingereicht von: <span class="font-semibold">{{ $item->last_edited_by_email }}</span></p>
                                                @endif
                                            </div>
                                            <span id="badge_{{ $item->id }}" class="text-xs font-bold px-3 py-1 rounded-full whitespace-nowrap ml-2
                                                {{ match($item->status) {
                                                    'submitted' => 'bg-green-100 text-green-800',
                                                    'disabled' => 'bg-gray-200 text-gray-600',
                                                    'rejected' => 'bg-red-100 text-red-800',
                                                    'approved' => 'bg-blue-100 text-blue-800',
                                                    default => 'bg-yellow-100 text-yellow-800'
                                                } }}">
                                                {{ strtoupper($item->status) }}
                                            </span>
                                        </div>

                                        @if($item->manager_comment && $canEditItem)
                                            <div id="manager_note_box_{{ $item->id }}" class="bg-red-50 border border-red-200 rounded p-3 mb-3 text-sm text-red-800">
                                                <strong>⚠️ Überprüfer-Notiz:</strong> <span id="manager_comment_text_{{ $item->id }}">{{ $item->manager_comment }}</span>
                                            </div>
                                        @endif

                                        @php
                                            $techPhotoEntries = $item->photos->map(function ($photo) {
                                                return [
                                                    'id' => $photo->id,
                                                    'url' => asset('storage/' . $photo->photo_path),
                                                ];
                                            });
                                            if ($techPhotoEntries->isEmpty() && $item->photo_path) {
                                                $techPhotoEntries = collect([['id' => null, 'url' => asset('storage/' . $item->photo_path)]]);
                                            }
                                        @endphp
                                        
                                        <div id="preview_gallery_{{ $item->id }}" data-item-id="{{ $item->id }}" data-editable="{{ $canEditItem ? '1' : '0' }}" class="{{ $techPhotoEntries->isEmpty() ? 'hidden' : 'grid' }} grid-cols-3 gap-2 mb-3">
                                            @foreach($techPhotoEntries as $photo)
                                                <div class="relative group photo-tile" data-photo-id="{{ $photo['id'] ?? '' }}" draggable="{{ $canEditItem && $photo['id'] ? 'true' : 'false' }}" ondragstart="onPhotoDragStart(event)" ondragover="onPhotoDragOver(event)" ondrop="onPhotoDrop(event)">
                                                    <img src="{{ $photo['url'] }}" onclick="openPhotoLightbox('{{ $photo['url'] }}')" class="h-20 w-full object-cover rounded border cursor-zoom-in hover:opacity-80 transition">
                                                    @if($canEditItem && $photo['id'])
                                                        <button type="button" onclick="deleteItemPhoto({{ $item->id }}, {{ $photo['id'] }}, event)" class="absolute -top-2 -right-2 bg-red-600 text-white w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold shadow hover:bg-red-700 transition">&times;</button>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>

                                        @if($item->technician_comment || $canEditItem)
                                            <div class="mb-3">
                                                @if($canEditItem)
                                                    <input type="text" id="tech_comment_{{ $item->id }}" value="{{ $item->technician_comment }}" placeholder="Kommentar hinzufügen..." class="w-full text-sm p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                                @elseif($item->technician_comment)
                                                    <p class="text-sm text-gray-700 bg-blue-50 border border-blue-100 rounded p-2">💬 {{ auth()->user()?->email ?? 'System' }}: {{ $item->technician_comment }}</p>
                                                @endif
                                            </div>
                                        @endif

                                        @if($canEditItem)
                                            <div class="flex gap-2 items-center pt-3 border-t border-gray-200">
                                                <input type="file" id="file_input_{{ $item->id }}" accept="image/*" multiple onchange="uploadItemData({{ $item->id }}, false, true)" class="text-xs text-gray-500 file:mr-2 file:py-1 file:px-2 file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 file:cursor-pointer flex-1">
                                                <label class="relative inline-flex items-center cursor-pointer bg-gray-100 rounded-full p-0.5 transition" style="width: 50px; height: 28px;">
                                                    <input type="checkbox" id="disable_toggle_{{ $item->id }}" onchange="toggleDisableItem({{ $item->id }})" class="sr-only peer" />
                                                    <div class="absolute left-0.5 top-0.5 bg-white w-6 h-6 rounded-full transition-transform peer-checked:translate-x-6 shadow-sm"></div>
                                                    <span class="peer-checked:opacity-0 opacity-100 transition absolute left-1.5 text-xs font-semibold text-gray-600">Off</span>
                                                    <span class="peer-checked:opacity-100 opacity-0 transition absolute right-1.5 text-xs font-semibold text-blue-600">On</span>
                                                </label>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            @if($checklist->status === 'pending' && !$isManager)
                                <div class="mt-6 space-y-3 pt-6 border-t-2 border-gray-200">
                                    <div class="flex gap-3">
                                        <button type="button" id="save_comments_btn_{{ $checklist->id }}" onclick="saveAllComments({{ $checklist->id }})" class="flex-1 px-4 py-3 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition shadow-sm text-sm">
                                            💾 Kommentare speichern
                                        </button>
                                        <button type="button" onclick="submitChecklistWithConfirmation({{ $checklist->id }})" class="flex-1 px-4 py-3 bg-green-600 text-white font-bold rounded-lg hover:bg-green-700 transition shadow-sm text-sm">
                                            ✓ Checkliste einreichen
                                        </button>
                                    </div>
                                    <p class="text-xs text-gray-500 text-center">Speichern Sie zunächst alle Kommentare, bevor Sie die Checkliste einreichen.</p>
                                </div>
                                <form action="{{ route('frontend.checklist.submit', $checklist) }}" method="POST" class="checklist-submit-form hidden" data-checklist-id="{{ $checklist->id }}">
                                    @csrf
                                    <button type="submit" style="display: none;"></button>
                                </form>
                            @elseif(in_array($checklist->status, ['approved', 'rejected', 'disabled']) && !$isManager)
                                <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg text-center text-gray-600 font-medium">
                                    Checkliste ist geschlossen
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="p-8 text-center bg-white rounded-lg border border-gray-200">
                <p class="text-gray-600 font-medium">Keine Checklisten für diesen Job vorhanden</p>
            </div>
        @endforelse
    </div>



<!-- BEP Type Selection Modal -->
<div id="bep_type_modal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;" onclick="if(event.target.id === 'bep_type_modal') closeBepTypeModal()">
    <div class="bg-white rounded-lg shadow-2xl max-w-md w-full p-6" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold text-gray-900">BEP Typ wählen</h3>
            <button type="button" onclick="closeBepTypeModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div id="bep_type_list" class="space-y-2">
            <!-- Loaded via AJAX -->
        </div>
    </div>
</div>

<!-- Photo Lightbox -->
<div id="photo_lightbox" class="fixed inset-0 z-50 bg-black/90 backdrop-blur-sm p-4 flex items-center justify-center" style="display: none;" onclick="closePhotoLightbox()">
    <button type="button" onclick="closePhotoLightbox()" class="absolute top-4 right-4 text-white hover:text-gray-300 transition p-2">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
    </button>
    <img id="photo_lightbox_img" src="" alt="Vollansicht" class="max-w-full max-h-full object-contain rounded-lg shadow-2xl" onclick="event.stopPropagation()">
</div>

<!-- Leaflet CSS & JS for Map -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>

<script>
const MAX_PHOTOS_PER_CHECKPOINT = 5;
let draggedPhotoTile = null;

// BEP Type Modal
function openBepTypeModal() {
    const modal = document.getElementById('bep_type_modal');
    modal.style.display = 'flex';
    
    fetch('/bep-types', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        const typeList = document.getElementById('bep_type_list');
        typeList.innerHTML = data.types.map(type => 
            `<button type="button" onclick="selectBepType('${type}')" class="w-full text-left px-4 py-3 rounded-lg border border-gray-200 hover:bg-blue-50 hover:border-blue-300 transition font-medium text-gray-900">
                ${type}
            </button>`
        ).join('');
    })
    .catch(error => console.error('Fehler beim Laden der BEP Typen:', error));
}

function closeBepTypeModal() {
    const modal = document.getElementById('bep_type_modal');
    modal.style.display = 'none';
}

function selectBepType(type) {
    const formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('type', type);

    fetch('/job/{{ $job->id }}/update-type', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            alert('Fehler beim Ändern des BEP Typs');
            return;
        }
        closeBepTypeModal();
        location.reload();
    })
    .catch(error => console.error('Fehler:', error));
}

// Map initialization
function initJobMap() {
    const mapElement = document.getElementById('job_map');
    if (!mapElement) return;

    const customFields = @json($job->custom_fields ?? []);
    const lat = parseFloat(customFields['target_latitude']);
    const lng = parseFloat(customFields['target_longitude']);
    
    console.log('Map coords from custom_fields:', lat, lng); // Debug
    
    if (isNaN(lat) || isNaN(lng)) {
        console.warn('Keine validen Koordinaten vorhanden');
        return;
    }

    try {
        const map = L.map('job_map').setView([lat, lng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19,
        }).addTo(map);

        L.marker([lat, lng]).addTo(map).bindPopup('<b>Einsatzort</b><br>' + lat + ', ' + lng);
        console.log('Map erfolgreich initialisiert');
    } catch (error) {
        console.error('Fehler beim Initialisieren der Karte:', error);
    }
}

// Toggle disable item with iOS-style switcher
function toggleDisableItem(itemId) {
    const checkbox = document.getElementById('disable_toggle_' + itemId);
    const shouldDisable = checkbox.checked;
    uploadItemData(itemId, shouldDisable, false);
}

// Save all comments at once
function saveAllComments(checklistId) {
    const checklistItemsBox = document.getElementById('checklist_items_' + checklistId);
    if (!checklistItemsBox) return;

    const saveButton = document.getElementById('save_comments_btn_' + checklistId);
    const originalButtonText = saveButton ? saveButton.textContent : null;
    if (saveButton) {
        saveButton.disabled = true;
        saveButton.textContent = 'Speichert...';
    }

    try {
        const commentInputs = checklistItemsBox.querySelectorAll('input[id^="tech_comment_"]');
        const saveRequests = [];
        commentInputs.forEach(function (input) {
            const itemId = input.id.replace('tech_comment_', '');
            saveRequests.push(saveCommentOnly(itemId, input.value));
        });
        
        Promise.all(saveRequests).then(() => {
            if (saveButton) {
                saveButton.disabled = false;
                saveButton.textContent = originalButtonText || '💾 Kommentare speichern';
            }
            alert('✓ Alle Kommentare wurden gespeichert!');
        }).catch(() => {
            if (saveButton) {
                saveButton.disabled = false;
                saveButton.textContent = originalButtonText || '💾 Kommentare speichern';
            }
            alert('Fehler beim Speichern der Kommentare');
        });
    } catch (error) {
        console.error(error);
        if (saveButton) {
            saveButton.disabled = false;
            saveButton.textContent = originalButtonText || '💾 Kommentare speichern';
        }
    }
}

// Submit checklist with confirmation
function submitChecklistWithConfirmation(checklistId) {
    const confirmed = confirm('⚠️ Wichtig!\n\nSie reichen die Checkliste jetzt ein.\n\n✓ Alle Angaben sind korrekt und vollständig\n✓ Diese Aktion kann nicht rückgängig gemacht werden\n\nMöchten Sie fortfahren?');
    
    if (confirmed) {
        const form = document.querySelector('[data-checklist-id="' + checklistId + '"]');
        if (form) form.submit();
    }
}

function openPhotoLightbox(src) {
    if (!src) return;
    const lightbox = document.getElementById('photo_lightbox');
    const lightboxImg = document.getElementById('photo_lightbox_img');
    if (!lightbox || !lightboxImg) return;
    lightboxImg.src = src;
    lightbox.style.display = 'flex';
    document.body.classList.add('overflow-hidden');
}

function closePhotoLightbox() {
    const lightbox = document.getElementById('photo_lightbox');
    const lightboxImg = document.getElementById('photo_lightbox_img');
    if (!lightbox || !lightboxImg) return;
    lightbox.style.display = 'none';
    lightboxImg.src = '';
    document.body.classList.remove('overflow-hidden');
}

function applyStatusBadge(badge, status) {
    if (!badge) return;
    const statusClasses = {
        submitted: ['bg-green-100', 'text-green-800'],
        disabled: ['bg-gray-200', 'text-gray-600'],
        rejected: ['bg-red-100', 'text-red-800'],
        approved: ['bg-blue-100', 'text-blue-800'],
        pending: ['bg-yellow-100', 'text-yellow-800']
    };
    badge.innerText = status.toUpperCase();
    badge.className = 'text-xs font-bold px-3 py-1 rounded-full whitespace-nowrap ml-2';
    (statusClasses[status] || statusClasses.pending).forEach(className => badge.classList.add(className));
}

function uploadItemData(itemId, shouldDisable, isAutoPhotoUpload = false) {
    const fileInput = document.getElementById('file_input_' + itemId);
    const commentInput = document.getElementById('tech_comment_' + itemId);
    const badge = document.getElementById('badge_' + itemId);
    const previewGallery = document.getElementById('preview_gallery_' + itemId);
    const card = document.getElementById('item_card_' + itemId);
    const managerBox = document.getElementById('manager_note_box_' + itemId);
    const hasPhoto = fileInput && fileInput.files && fileInput.files.length > 0;
    const hasComment = commentInput && commentInput.value.trim().length > 0;
    const existingPhotoCount = previewGallery ? previewGallery.querySelectorAll('.photo-tile img').length : 0;

    let formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('technician_comment', commentInput ? commentInput.value : '');

    if (shouldDisable) {
        formData.append('disable', '1');
    } else if (hasPhoto) {
        if ((existingPhotoCount + fileInput.files.length) > MAX_PHOTOS_PER_CHECKPOINT) {
            alert('Maximum ' + MAX_PHOTOS_PER_CHECKPOINT + ' Fotos pro Punkt erlaubt.');
            return;
        }
        for (let i = 0; i < fileInput.files.length; i++) {
            formData.append('photos[]', fileInput.files[i]);
        }
    } else if (!hasComment && !shouldDisable) {
        if (isAutoPhotoUpload) return;
    }

    fetch('/checklist-item/' + itemId + '/save', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            if (data.message) alert(data.message);
            return;
        }

        applyStatusBadge(badge, data.status);

        if (data.status === 'submitted') {
            if (managerBox) managerBox.classList.add('hidden');
            card.className = "p-5 rounded-lg border-2 border-gray-200 bg-white transition shadow-sm hover:shadow-md";
        } else if (data.status === 'disabled') {
            if (managerBox) managerBox.classList.add('hidden');
            card.className = "p-5 rounded-lg border-2 border-gray-200 bg-white transition shadow-sm hover:shadow-md opacity-60";
            // Löschen von Kommentaren und Fotos beim Deaktivieren
            if (commentInput) commentInput.value = '';
            renderPreviewGallery(previewGallery, []);
        }

        if (Array.isArray(data.photos)) {
            renderPreviewGallery(previewGallery, data.photos);
        } else if (data.photo_url) {
            renderPreviewGallery(previewGallery, [{ id: null, url: data.photo_url }]);
        }

        if (fileInput) fileInput.value = '';
        if (shouldDisable) {
            renderPreviewGallery(previewGallery, []);
            document.getElementById('disable_toggle_' + itemId).checked = true;
        } else {
            document.getElementById('disable_toggle_' + itemId).checked = false;
        }
    })
    .catch(error => console.error('Fehler:', error));
}

function renderPreviewGallery(container, photos) {
    if (!container) return;
    const entries = Array.isArray(photos) ? photos.filter(photo => photo && photo.url) : [];
    if (entries.length === 0) {
        container.classList.add('hidden');
        container.innerHTML = '';
        return;
    }

    const editable = container.dataset.editable === '1';
    const itemId = container.dataset.itemId;
    container.classList.remove('hidden');
    container.innerHTML = entries
        .map(photo => {
            const photoId = photo.id ?? '';
            const url = String(photo.url);
            const escapedUrl = url.replace(/'/g, "\\'");
            const draggable = editable && photoId ? 'true' : 'false';
            const deleteButton = editable && photoId
                ? '<button type="button" onclick="deleteItemPhoto(' + itemId + ', ' + photoId + ', event)" class="absolute -top-2 -right-2 bg-red-600 text-white w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold shadow hover:bg-red-700 transition">&times;</button>'
                : '';

            return '<div class="relative group photo-tile" data-photo-id="' + photoId + '" draggable="' + draggable + '" ondragstart="onPhotoDragStart(event)" ondragover="onPhotoDragOver(event)" ondrop="onPhotoDrop(event)">' +
                '<img src="' + url + '" onclick="openPhotoLightbox(\'' + escapedUrl + '\')" class="h-20 w-full object-cover rounded border cursor-zoom-in hover:opacity-80 transition">' +
                deleteButton +
                '</div>';
        })
        .join('');
}

function deleteItemPhoto(itemId, photoId, event) {
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }

    const formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');

    fetch('/checklist-item/' + itemId + '/photo/' + photoId + '/delete', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            alert(data.message || 'Foto konnte nicht gelöscht werden.');
            return;
        }
        const previewGallery = document.getElementById('preview_gallery_' + itemId);
        renderPreviewGallery(previewGallery, data.photos || []);
    })
    .catch(() => alert('Foto konnte nicht gelöscht werden.'));
}

function onPhotoDragStart(event) {
    const tile = event.target.closest('.photo-tile');
    if (!tile || tile.getAttribute('draggable') !== 'true') return;
    draggedPhotoTile = tile;
    event.dataTransfer.effectAllowed = 'move';
}

function onPhotoDragOver(event) {
    const tile = event.target.closest('.photo-tile');
    if (!tile || !draggedPhotoTile) return;
    event.preventDefault();
    event.dataTransfer.dropEffect = 'move';
}

function onPhotoDrop(event) {
    event.preventDefault();
    const targetTile = event.target.closest('.photo-tile');
    if (!targetTile || !draggedPhotoTile || targetTile === draggedPhotoTile) {
        draggedPhotoTile = null;
        return;
    }

    const container = targetTile.parentElement;
    const targetRect = targetTile.getBoundingClientRect();
    const shouldInsertAfter = event.clientX > targetRect.left + (targetRect.width / 2);
    if (shouldInsertAfter) {
        targetTile.after(draggedPhotoTile);
    } else {
        targetTile.before(draggedPhotoTile);
    }

    draggedPhotoTile = null;
    persistPhotoOrder(container);
}

function persistPhotoOrder(container) {
    if (!container || container.dataset.editable !== '1') return;
    const itemId = container.dataset.itemId;
    const orderedPhotoIds = Array.from(container.querySelectorAll('.photo-tile[data-photo-id]'))
        .map(tile => tile.dataset.photoId)
        .filter(id => id && id !== 'null');

    if (orderedPhotoIds.length < 2) return;

    const formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');
    orderedPhotoIds.forEach(photoId => formData.append('photo_ids[]', photoId));

    fetch('/checklist-item/' + itemId + '/photos/reorder', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            alert(data.message || 'Fotos konnten nicht neu geordnet werden.');
            return;
        }
        renderPreviewGallery(container, data.photos || []);
    })
    .catch(() => alert('Fotos konnten nicht neu geordnet werden.'));
}

function saveCommentOnly(itemId, comment) {
    let formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('technician_comment', comment);

    return fetch('/checklist-item/' + itemId + '/save', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => {
        if (!response.ok) throw new Error('Kommentar konnte nicht gespeichert werden für Element ' + itemId);
        return response.json();
    })
    .then(data => {
        if (!data.success) throw new Error('Kommentar speichern war nicht erfolgreich für Element ' + itemId);
        const badge = document.getElementById('badge_' + itemId);
        applyStatusBadge(badge, data.status);
        return data;
    });
}

document.addEventListener('DOMContentLoaded', function () {
    initJobMap();

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closePhotoLightbox();
            closeBepTypeModal();
        }
    });
});
</script>
@endsection
