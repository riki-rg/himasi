<?php

namespace App\Http\Controllers;

use App\Models\Periode;
use App\Models\Surat;
use App\Models\SuratStatusLog;
use App\Models\SuratTemplate;
use App\Services\PenomoranSurat;
use App\Support\Problem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SuratController extends Controller
{
    private const URUTAN_STATUS = ['draft', 'review', 'disetujui', 'terkirim'];

    public function __construct(private readonly PenomoranSurat $penomoran) {}

    /** GET /surat/templates — kosong periode_id = periode aktif. */
    public function templates(Request $request): JsonResponse
    {
        $periodeId = $request->filled('periode_id')
            ? $request->integer('periode_id')
            : Periode::aktif()?->id;

        return response()->json(
            SuratTemplate::query()
                ->when($periodeId !== null, fn ($q) => $q->where('periode_id', $periodeId))
                ->get()
                ->map(fn ($t) => $this->templateResource($t))
                ->values()
        );
    }

    /** POST /surat/templates */
    public function storeTemplate(Request $request): JsonResponse
    {
        $data = $this->validatedTemplate($request);

        return response()->json(
            $this->templateResource(SuratTemplate::query()->create([...$data, 'counter' => 0])),
            201
        );
    }

    /** PUT /surat/templates/{id} — edit format; surat lama nomornya tetap. */
    public function updateTemplate(Request $request, SuratTemplate $template): JsonResponse
    {
        $data = $this->validatedTemplate($request, $template);

        $template->update(collect($data)->except('counter')->all());

        return response()->json($this->templateResource($template));
    }

    /** GET /surat — arsip searchable filter jenis & periode (US-18). */
    public function index(Request $request)
    {
        $perPage = min(max($request->integer('per_page', 15), 1), 100);

        $paginator = Surat::query()
            ->when($request->filled('jenis'), fn ($q) => $q->where('jenis', $request->string('jenis')))
            ->when($request->filled('periode_id'), fn ($q) => $q->where('periode_id', $request->integer('periode_id')))
            ->when($request->filled('q'), fn ($q) => $q->where(function ($w) use ($request) {
                $term = '%'.$request->string('q').'%';
                $w->where('nomor_surat', 'like', $term)
                    ->orWhere('perihal', 'like', $term)
                    ->orWhere('pihak', 'like', $term);
            }))
            ->orderByDesc('tanggal_surat')
            ->paginate($perPage);

        return response()->json([
            'data' => collect($paginator->items())->map(fn ($s) => $this->resource($s))->values(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
        ]);
    }

    /**
     * POST /surat — catat surat masuk atau keluar.
     * Keluar wajib template → penomoran otomatis atomik (US-17).
     */
    public function store(Request $request)
    {
        $data = $this->validated($request);

        if ($request->hasFile('file_scan')) {
            $data['file_path'] = $request->file('file_scan')->store('surat/scan', 'public');
        }
        unset($data['file_scan']);

        if ($data['jenis'] === 'keluar') {
            if (empty($data['surat_template_id'])) {
                throw Problem::validation(
                    ['surat_template_id' => ['Wajib memilih template untuk surat keluar.']],
                    'Surat keluar wajib punya template penomoran.'
                );
            }

            [$data['nomor_surat'], $data['status']] = DB::transaction(function () use ($data) {
                $template = SuratTemplate::query()
                    ->where('id', $data['surat_template_id'])
                    ->where('periode_id', $data['periode_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                return [
                    $this->penomoran->nomorBerikutnya($template, $data['tanggal_surat']),
                    'draft',
                ];
            });
        } else {
            $data['status'] = null;
        }

        $surat = Surat::query()->create([
            ...$data,
            'created_by' => $request->user()->id,
        ]);

        if ($surat->jenis === 'keluar') {
            SuratStatusLog::query()->create([
                'surat_id' => $surat->id,
                'status' => 'draft',
                'user_id' => $request->user()->id,
            ]);
        }

        return response()->json($this->resource($surat), 201);
    }

    /** GET /surat/{id} */
    public function show(Surat $surat): JsonResponse
    {
        return response()->json($this->resource($surat));
    }

    /** PUT /surat/{id} */
    public function update(Request $request, Surat $surat): JsonResponse
    {
        $data = $this->validated($request, $surat);
        unset($data['nomor_surat']);

        $surat->update($data);

        return response()->json($this->resource($surat));
    }

    /** DELETE /surat/{id} */
    public function destroy(Surat $surat): Response
    {
        if ($surat->file_path !== null) {
            Storage::disk('public')->delete($surat->file_path);
        }

        $surat->delete();

        return response()->noContent();
    }

    /**
     * POST /surat/{id}/status — alur maju draft→review→disetujui→terkirim.
     * Mundur/melompat → 409; setiap transisi tercatat siapa & kapan (US-19).
     */
    public function ubahStatus(Request $request, Surat $surat): JsonResponse
    {
        if ($surat->jenis === 'masuk') {
            throw Problem::conflict('Alur status hanya berlaku untuk surat keluar.');
        }

        $data = $request->validate([
            'status' => ['required', Rule::in(['review', 'disetujui', 'terkirim'])],
            'catatan' => ['nullable', 'string', 'max:255'],
        ]);

        $posisiSekarang = array_search($surat->status ?? 'draft', self::URUTAN_STATUS, true);
        $posisiTarget = array_search($data['status'], self::URUTAN_STATUS, true);

        if ($posisiTarget !== $posisiSekarang + 1) {
            throw Problem::conflict(sprintf(
                'Transisi tidak valid: %s hanya boleh maju ke %s.',
                $surat->status ?? 'draft',
                self::URUTAN_STATUS[$posisiSekarang + 1] ?? '(akhir)'
            ));
        }

        DB::transaction(function () use ($request, $surat, $data) {
            $surat->update(['status' => $data['status']]);

            SuratStatusLog::query()->create([
                'surat_id' => $surat->id,
                'status' => $data['status'],
                'catatan' => $data['catatan'] ?? null,
                'user_id' => $request->user()->id,
            ]);
        });

        return response()->json($this->resource($surat->refresh()));
    }

    /** GET /surat/{id}/logs — riwayat transisi (pendukung US-19). */
    public function logs(Surat $surat): JsonResponse
    {
        return response()->json(SuratStatusLog::query()
            ->where('surat_id', $surat->id)
            ->with('pengubah:id,name')
            ->orderBy('created_at')
            ->get()
            ->map(fn ($l) => [
                'status' => $l->status,
                'catatan' => $l->catatan,
                'oleh' => $l->pengubah?->name,
                'pada' => $l->created_at?->toISOString(),
            ])->values());
    }

    private function validated(Request $request, ?Surat $existing = null): array
    {
        return $request->validate([
            'jenis' => [$request->isMethod('POST') ? 'required' : 'sometimes', Rule::in(['masuk', 'keluar'])],
            'tanggal_surat' => [$request->isMethod('POST') ? 'required' : 'sometimes', 'date'],
            'pihak' => [$request->isMethod('POST') ? 'required' : 'sometimes', 'string', 'max:255'],
            'perihal' => [$request->isMethod('POST') ? 'required' : 'sometimes', 'string', 'max:255'],
            'disposisi' => ['nullable', 'string'],
            'periode_id' => [$request->isMethod('POST') ? 'required' : 'sometimes', Rule::exists('periodes', 'id')],
            'surat_template_id' => ['nullable', Rule::exists('surat_templates', 'id')],
            'file_scan' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);
    }

    private function validatedTemplate(Request $request, ?SuratTemplate $existing = null): array
    {
        return $request->validate([
            'periode_id' => [$existing === null ? 'required' : 'sometimes', Rule::exists('periodes', 'id')],
            'nama_jenis' => [$existing === null ? 'required' : 'sometimes', 'string', 'max:100',
                Rule::unique('surat_templates')->where(
                    fn ($q) => $q->where('periode_id', $existing?->periode_id ?? $request->integer('periode_id'))
                )->ignore($existing?->id),
            ],
            'format' => [$existing === null ? 'required' : 'sometimes', 'string', 'max:255'],
        ]);
    }

    private function templateResource(SuratTemplate $t): array
    {
        return [
            'id' => $t->id,
            'periode_id' => $t->periode_id,
            'nama_jenis' => $t->nama_jenis,
            'format' => $t->format,
            'counter' => $t->counter,
        ];
    }

    private function resource(Surat $s): array
    {
        return [
            'id' => $s->id,
            'jenis' => $s->jenis,
            'nomor_surat' => $s->nomor_surat,
            'tanggal_surat' => $s->tanggal_surat?->toDateString(),
            'pihak' => $s->pihak,
            'perihal' => $s->perihal,
            'file_path' => $s->file_path,
            'disposisi' => $s->disposisi,
            'status' => $s->status,
            'periode_id' => $s->periode_id,
            'surat_template_id' => $s->surat_template_id,
            'created_by' => $s->created_by,
        ];
    }
}
