<?php

namespace App\Http\Controllers;

use App\Http\Resources\PengumumanResource;
use App\Models\Pengumuman;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class PengumumanController extends Controller
{
    /** GET /pengumumans — semua termasuk kadaluarsa; filter komunitas. */
    public function index(Request $request)
    {
        $perPage = min(max($request->integer('per_page', 20), 1), 100);

        return PengumumanResource::collection(
            Pengumuman::query()
                ->when($request->filled('komunitas'), fn ($q) => $q->whereHas(
                    'komunitas',
                    fn ($k) => $k->where('kode', $request->string('komunitas')->upper())
                ))
                ->orderByDesc('created_at')
                ->paginate($perPage)
        );
    }

    /** POST /pengumumans */
    public function store(Request $request)
    {
        $data = $this->validated($request);

        return response()->json(PengumumanResource::make(Pengumuman::query()->create($data))->resolve(), 201);
    }

    /** PUT /pengumumans/{id} */
    public function update(Request $request, Pengumuman $pengumuman)
    {
        $pengumuman->update($this->validated($request));

        return response()->json(PengumumanResource::make($pengumuman)->resolve());
    }

    /** DELETE /pengumumans/{id} */
    public function destroy(Pengumuman $pengumuman): Response
    {
        $pengumuman->delete();

        return response()->noContent();
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'judul' => [$request->isMethod('POST') ? 'required' : 'sometimes', 'string', 'max:255'],
            'isi' => [$request->isMethod('POST') ? 'required' : 'sometimes', 'string'],
            'prioritas' => ['sometimes', Rule::in(['normal', 'penting'])],
            'tayang_mulai' => ['nullable', 'date'],
            'tayang_selesai' => ['nullable', 'date', 'after_or_equal:tayang_mulai'],
            'komunitas_id' => ['nullable', Rule::exists('komunitas', 'id')],
        ]);
    }
}
