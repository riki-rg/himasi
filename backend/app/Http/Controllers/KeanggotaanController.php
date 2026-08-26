<?php

namespace App\Http\Controllers;

use App\Http\Resources\MemberResource;
use App\Models\Komunitas;
use App\Models\KomunitasMember;
use App\Support\Problem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class KeanggotaanController extends Controller
{
    /**
     * GET /keanggotaan?komunitas=BITSI&status=pending — antrean pendaftar
     * untuk layar approve pengurus (wireframe bitsi-pengurus-pendaftar.md).
     */
    public function index(Request $request): JsonResponse
    {
        $kode = strtoupper($request->string('komunitas')->toString());
        Gate::authorize('pengurus-komunitas', $kode);

        $rows = KomunitasMember::query()
            ->whereHas('komunitas', fn ($k) => $k->where('kode', $kode))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->with(['member:id,nim,nama,prodi,angkatan,email,no_hp,foto_path,link_portofolio,link_instagram'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json($rows->map(fn ($km) => [
            'id' => $km->id,
            'member' => $km->member,
            'komunitas' => ['id' => $km->komunitas->id, 'kode' => $km->komunitas->kode, 'nama' => $km->komunitas->nama],
            'status' => $km->status,
            'disetujui_pada' => $km->disetujui_pada?->toISOString(),
            'daftar_pada' => $km->created_at?->toISOString(),
        ])->values());
    }

    /**
     * POST /keanggotaan — apply mandiri (pending) atau input manual
     * oleh admin/ketua komunitas (langsung disetujui). ADR D6.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'komunitas' => ['required', Rule::in(['HIMSI', 'BITSI', 'SIBINER'])],
            'member_id' => ['sometimes', 'integer', Rule::exists('members', 'id')],
        ]);

        $komunitasId = Komunitas::idByKode($data['komunitas']);
        $memberId = $data['member_id'] ?? Auth::user()->member?->id;

        if ($memberId === null) {
            throw Problem::validation(
                ['member_id' => ['Profil anggota belum tersedia untuk akun ini.']]
            );
        }

        if (isset($data['member_id'])) {
            Gate::authorize('pengurus-komunitas', $data['komunitas']);
        }

        $sudahAda = KomunitasMember::query()
            ->where('member_id', $memberId)
            ->where('komunitas_id', $komunitasId)
            ->exists();

        if ($sudahAda) {
            throw Problem::conflict(
                'Anggota sudah terdaftar pada komunitas '.$data['komunitas'].'.'
            );
        }

        $inputManual = isset($data['member_id']);

        $km = KomunitasMember::query()->create([
            'member_id' => $memberId,
            'komunitas_id' => $komunitasId,
            'status' => $inputManual ? 'disetujui' : 'pending',
            'approved_by' => $inputManual ? Auth::id() : null,
            'disetujui_pada' => $inputManual ? now() : null,
        ]);

        return response()->json($this->resource($km), Response::HTTP_CREATED);
    }

    /** PATCH /keanggotaan/{id} — setujui/tolak pendaftaran. */
    public function update(Request $request, KomunitasMember $keanggotaan): JsonResponse
    {
        Gate::authorize('pengurus-komunitas', $keanggotaan->komunitas->kode);

        $data = $request->validate([
            'status' => ['required', Rule::in(['disetujui', 'ditolak'])],
        ]);

        $keanggotaan->update([
            'status' => $data['status'],
            'approved_by' => Auth::id(),
            'disetujui_pada' => $data['status'] === 'disetujui' ? now() : null,
        ]);

        return response()->json($this->resource($keanggotaan));
    }

    /** DELETE /keanggotaan/{id} — hapus keanggotaan komunitas. */
    public function destroy(KomunitasMember $keanggotaan): Response
    {
        Gate::authorize('pengurus-komunitas', $keanggotaan->komunitas->kode);

        $keanggotaan->delete();

        return response()->noContent();
    }

    /**
     * @return array<string, mixed>
     */
    private function resource(KomunitasMember $km): array
    {
        return [
            'id' => $km->id,
            'member' => MemberResource::make($km->member),
            'komunitas' => [
                'id' => $km->komunitas->id,
                'kode' => $km->komunitas->kode,
                'nama' => $km->komunitas->nama,
            ],
            'status' => $km->status,
            'disetujui_pada' => $km->disetujui_pada?->toISOString(),
        ];
    }
}
