<?php

namespace App\Http\Controllers;

use App\Http\Resources\EventResource;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class EventController extends Controller
{
    /** GET /events — semua termasuk draft/batal; filter komunitas & status. */
    public function index(Request $request)
    {
        $perPage = min(max($request->integer('per_page', 15), 1), 100);

        return EventResource::collection(
            Event::query()
                ->when($request->filled('komunitas'), fn ($q) => $q->whereHas(
                    'komunitas',
                    fn ($k) => $k->where('kode', $request->string('komunitas')->upper())
                ))
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
                ->orderByDesc('mulai')
                ->paginate($perPage)
        );
    }

    /** POST /events — multipart poster opsional ≤5MB. */
    public function store(Request $request)
    {
        $data = $this->validated($request);

        if ($request->hasFile('poster')) {
            $data['poster_path'] = $request->file('poster')->store('events/posters', 'public');
        }

        return response()->json(EventResource::make(
            Event::query()->create([...$data, 'status' => $data['status'] ?? 'draft'])
        )->resolve(), 201);
    }

    /** GET /events/{id} */
    public function show(Event $event)
    {
        return response()->json(EventResource::make($event)->resolve());
    }

    /** PUT /events/{id} */
    public function update(Request $request, Event $event)
    {
        $event->update($this->validated($request));

        return response()->json(EventResource::make($event)->resolve());
    }

    /** DELETE /events/{id} */
    public function destroy(Event $event): Response
    {
        if ($event->poster_path !== null) {
            Storage::disk('public')->delete($event->poster_path);
        }

        $event->delete();

        return response()->noContent();
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'judul' => [$request->isMethod('POST') ? 'required' : 'sometimes', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'mulai' => [$request->isMethod('POST') ? 'required' : 'sometimes', 'date'],
            'selesai' => ['nullable', 'date', 'after_or_equal:mulai'],
            'komunitas_id' => ['nullable', Rule::exists('komunitas', 'id')],
            'status' => ['sometimes', Rule::in(['draft', 'published', 'batal'])],
        ]);
    }
}
