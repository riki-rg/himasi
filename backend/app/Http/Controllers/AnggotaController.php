<?php

namespace App\Http\Controllers;

use App\Http\Resources\MemberResource;
use App\Models\Komunitas;
use App\Models\Member;
use App\Services\MemberImportExporter;
use App\Support\Problem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class AnggotaController extends Controller
{
    public function __construct(
        private readonly MemberImportExporter $excel,
    ) {}

    /** GET /anggota — daftar dengan search, filter, pagination (cap 100). */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Member::query()
            ->when(
                $request->filled('q'),
                fn ($q) => $q->where(function ($w) use ($request) {
                    $term = '%'.$request->string('q').'%';
                    $w->where('nama', 'like', $term)->orWhere('nim', 'like', $term);
                })
            )
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('angkatan'), fn ($q) => $q->where('angkatan', (string) $request->integer('angkatan')))
            ->when(
                $request->filled('komunitas'),
                fn ($q) => $q->whereHas(
                    'keanggotaanKomunitas',
                    fn ($km) => $km->where('status', 'disetujui')
                        ->where('komunitas_id', Komunitas::idByKode($request->string('komunitas')->upper()))
                )
            )
            ->orderBy('nama');

        $perPage = min(max($request->integer('per_page', 15), 1), 100);

        return MemberResource::collection($query->paginate($perPage));
    }

    /** POST /anggota — tambah anggota manual. */
    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        $member = Member::query()->create([
            ...$data,
            'status' => $data['status'] ?? 'aktif',
        ]);

        return response()->json(MemberResource::make($member)->resolve(), Response::HTTP_CREATED);
    }

    /** GET /anggota/{id}. */
    public function show(Member $anggota): JsonResponse
    {
        return response()->json(MemberResource::make($anggota)->resolve());
    }

    /** PUT /anggota/{id} — update data anggota. */
    public function update(Request $request, Member $anggota): JsonResponse
    {
        $anggota->update($this->validated($request, $anggota));

        return response()->json(MemberResource::make($anggota)->resolve());
    }

    /** DELETE /anggota/{id}. */
    public function destroy(Member $anggota): Response
    {
        DB::transaction(fn () => $anggota->delete());

        return response()->noContent();
    }

    /**
     * POST /anggota/import — import massal xlsx/csv.
     * Header kolom: nim, nama, prodi, angkatan, email, no_hp, status.
     */
    public function import(Request $request): JsonResponse
    {
        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt,xlsx', 'max:10240']]);

        [$berhasil, $detailGagal] = $this->excel->importAnggota($request->file('file')->getRealPath());

        return response()->json([
            'berhasil' => $berhasil,
            'gagal' => count($detailGagal),
            'detail_gagal' => $detailGagal,
        ]);
    }

    /** GET /anggota/export?format=xlsx|csv — unduh seluruh kolom anggota. */
    public function export(Request $request): Response
    {
        $format = $request->string('format', 'xlsx')->toString();

        if (! in_array($format, ['xlsx', 'csv'], true)) {
            throw Problem::validation(['format' => ['Format harus xlsx atau csv.']]);
        }

        $query = Member::query()->orderBy('nama');

        return $this->excel->exportAnggota($query, $format);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Member $existing = null): array
    {
        return $request->validate([
            'nim' => ['sometimes', 'string', 'max:20', Rule::unique('members', 'nim')->ignore($existing?->id)],
            'user_id' => ['sometimes', 'nullable', Rule::exists('users', 'id')],
            'nama' => $existing === null
                ? ['required', 'string', 'max:255']
                : ['sometimes', 'string', 'max:255'],
            'prodi' => ['nullable', 'string', 'max:255'],
            'angkatan' => $existing === null
                ? ['required', 'digits:4', 'integer']
                : ['sometimes', 'digits:4', 'integer'],
            'email' => ['nullable', 'email'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'alamat' => ['nullable', 'string'],
            'foto_path' => ['nullable', 'string'],
            'link_portofolio' => ['nullable', 'url'],
            'link_instagram' => ['nullable', 'string', 'max:255'],
            'status' => ['sometimes', Rule::in(['aktif', 'nonaktif', 'alumni'])],
        ]);
    }
}
