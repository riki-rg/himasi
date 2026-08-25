<?php

namespace App\Http\Controllers;

use App\Models\Kas;
use App\Models\KasKategori;
use App\Services\MemberImportExporter;
use App\Support\Problem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KasController extends Controller
{
    public function __construct(private readonly MemberImportExporter $excel) {}

    /** GET /kas — bendahara & admin (ADR D2); filter periode/tipe/rentang tanggal. */
    public function index(Request $request)
    {
        $perPage = min(max($request->integer('per_page', 15), 1), 100);

        $paginator = $this->query($request)->orderBy('tanggal')->orderBy('id')->paginate($perPage);

        return response()->json([
            'data' => collect($paginator->items())->map(fn ($k) => $this->resource($k))->values(),
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

    /** POST /kas — catat transaksi + bukti foto ≤5MB. */
    public function store(Request $request)
    {
        $data = $this->validated($request);

        if ($request->hasFile('bukti_foto')) {
            $data['bukti_path'] = $request->file('bukti_foto')->store('kas/bukti', 'public');
        }
        unset($data['bukti_foto']);

        $kas = Kas::query()->create([
            ...$data,
            'kas_kategori_id' => $this->kategoriId($data['kategori'], $data['tipe']),
            'user_id' => $request->user()->id,
        ]);

        return response()->json($this->resource($kas), 201);
    }

    /** GET /kas/{id} */
    public function show(Kas $kas): JsonResponse
    {
        return response()->json($this->resource($kas));
    }

    /** PUT /kas/{id} */
    public function update(Request $request, Kas $kas)
    {
        $data = $this->validated($request);

        if ($request->hasFile('bukti_foto')) {
            if ($kas->bukti_path !== null) {
                Storage::disk('public')->delete($kas->bukti_path);
            }
            $data['bukti_path'] = $request->file('bukti_foto')->store('kas/bukti', 'public');
        }
        unset($data['bukti_foto']);

        $kas->update([
            ...collect($data)->except('kategori')->all(),
            'kas_kategori_id' => $this->kategoriId($data['kategori'] ?? $kas->kategori->nama, $data['tipe'] ?? $kas->tipe),
        ]);

        return response()->json($this->resource($kas));
    }

    /** DELETE /kas/{id} */
    public function destroy(Kas $kas): Response
    {
        if ($kas->bukti_path !== null) {
            Storage::disk('public')->delete($kas->bukti_path);
        }

        $kas->delete();

        return response()->noContent();
    }

    /**
     * GET /kas/rekap — agregat terbuka untuk pengurus lain (ADR D2).
     * kelompok=bulan|kategori.
     */
    public function rekap(Request $request): JsonResponse
    {
        $base = $this->query($request);

        $totals = (clone $base)
            ->selectRaw("SUM(CASE WHEN tipe = 'pemasukan' THEN nominal ELSE 0 END) as masuk")
            ->selectRaw("SUM(CASE WHEN tipe = 'pengeluaran' THEN nominal ELSE 0 END) as keluar")
            ->first();

        $kelompok = $request->string('kelompok', 'bulan')->toString();

        $breakdownQuery = (clone $base);

        $breakdown = $kelompok === 'kategori'
            ? $breakdownQuery
                ->join('kas_kategoris', 'kas_kategoris.id', '=', 'kas.kas_kategori_id')
                ->groupBy('kas_kategoris.nama')
                ->selectRaw('kas_kategoris.nama as label, SUM(nominal) as nilai')
                ->get()
            : $breakdownQuery
                ->groupByRaw("strftime('%Y-%m', tanggal)")
                ->selectRaw("strftime('%Y-%m', tanggal) as label, SUM(nominal) as nilai")
                ->get();

        return response()->json([
            'total_pemasukan' => (float) ($totals->masuk ?? 0),
            'total_pengeluaran' => (float) ($totals->keluar ?? 0),
            'saldo' => (float) ($totals->masuk ?? 0) - (float) ($totals->keluar ?? 0),
            'breakdown' => $breakdown->map(fn ($r) => ['label' => $r->label, 'nilai' => (float) $r->nilai])->values(),
        ]);
    }

    /**
     * GET /kas/export?format=xlsx|csv — laporan per periode/rentang (US-21).
     * Flag amendment: endpoint belum ada di openapi.yaml.
     */
    public function export(Request $request): StreamedResponse
    {
        $format = $request->string('format', 'xlsx')->toString();

        if (! in_array($format, ['xlsx', 'csv'], true)) {
            throw Problem::validation(['format' => ['Format harus xlsx atau csv.']]);
        }

        $rows = $this->query($request)->with(['kategori', 'periode'])
            ->orderBy('tanggal')->get();
        $totalMasuk = $rows->where('tipe', 'pemasukan')->sum('nominal');
        $totalKeluar = $rows->where('tipe', 'pengeluaran')->sum('nominal');

        return $this->excel->exportLaporanKeuangan($rows, [
            'total_pemasukan' => $totalMasuk,
            'total_pengeluaran' => $totalKeluar,
            'saldo' => $totalMasuk - $totalKeluar,
        ], $format);
    }

    private function query(Request $request): Builder
    {
        return Kas::query()
            ->when($request->filled('periode_id'), fn ($q) => $q->where('periode_id', $request->integer('periode_id')))
            ->when($request->filled('tipe'), fn ($q) => $q->where('tipe', $request->string('tipe')))
            ->when($request->filled('tanggal_dari'), fn ($q) => $q->whereDate('tanggal', '>=', $request->date('tanggal_dari')))
            ->when($request->filled('tanggal_sampai'), fn ($q) => $q->whereDate('tanggal', '<=', $request->date('tanggal_sampai')));
    }

    private function kategoriId(string $nama, string $tipe): int
    {
        return KasKategori::query()
            ->firstOrCreate(
                ['nama' => ucfirst(strtolower(trim($nama)))],
                ['tipe_default' => $tipe]
            )->id;
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'tanggal' => [$request->isMethod('POST') ? 'required' : 'sometimes', 'date'],
            'tipe' => [$request->isMethod('POST') ? 'required' : 'sometimes', Rule::in(['pemasukan', 'pengeluaran'])],
            'nominal' => [$request->isMethod('POST') ? 'required' : 'sometimes', 'numeric', 'min:0', 'max:9999999999.99'],
            'keterangan' => [$request->isMethod('POST') ? 'required' : 'sometimes', 'string', 'max:255'],
            'kategori' => [$request->isMethod('POST') ? 'required' : 'sometimes', 'string', 'max:100'],
            'periode_id' => [$request->isMethod('POST') ? 'required' : 'sometimes', Rule::exists('periodes', 'id')],
            'member_id' => ['nullable', Rule::exists('members', 'id')],
            'bukti_foto' => ['nullable', 'image', 'max:5120'],
        ]);
    }

    private function resource(Kas $k): array
    {
        return [
            'id' => $k->id,
            'tanggal' => $k->tanggal?->toDateString(),
            'tipe' => $k->tipe,
            'nominal' => $k->nominal,
            'keterangan' => $k->keterangan,
            'kategori' => $k->kategori?->nama,
            'bukti_path' => $k->bukti_path,
            'periode_id' => $k->periode_id,
            'member_id' => $k->member_id,
        ];
    }
}
