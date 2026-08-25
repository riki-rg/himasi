<?php

namespace App\Http\Controllers;

use App\Models\Iuran;
use App\Models\IuranMember;
use App\Models\Kas;
use App\Models\KasKategori;
use App\Models\Member;
use App\Support\Problem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IuranController extends Controller
{
    /** GET /iuran?periode_id= — daftar + ringkasan lunas/belum. */
    public function index(Request $request)
    {
        return response()->json(Iuran::query()
            ->when($request->filled('periode_id'), fn ($q) => $q->where('periode_id', $request->integer('periode_id')))
            ->withCount(['tagihans as total_tagihan'])
            ->withCount(['tagihans as lunas' => fn ($q) => $q->where('status', 'lunas')])
            ->withCount(['tagihans as belum' => fn ($q) => $q->where('status', 'belum')])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($i) => [
                'id' => $i->id,
                'nama' => $i->nama,
                'jumlah' => $i->jumlah,
                'periode_id' => $i->periode_id,
                'komunitas_id' => $i->komunitas_id,
                'tenggat' => $i->tenggat?->toDateString(),
                'ringkasan' => [
                    'total_tagihan' => $i->total_tagihan,
                    'lunas' => $i->lunas,
                    'belum' => $i->belum,
                ],
            ])->values());
    }

    /**
     * POST /iuran — buat iuran + generate tagihan otomatis untuk semua
     * anggota aktif terkait (komunitas tertentu, atau seluruh HIMSI).
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'jumlah' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'periode_id' => ['required', 'exists:periodes,id'],
            'komunitas_id' => ['nullable', 'exists:komunitas,id'],
            'tenggat' => ['required', 'date'],
        ]);

        [$iuran, $jumlahTagihan] = DB::transaction(function () use ($data) {
            $iuran = Iuran::query()->create($data);

            $anggota = Member::query()
                ->where('status', 'aktif')
                ->when(
                    $data['komunitas_id'] !== null,
                    fn ($q) => $q->whereHas('keanggotaanKomunitas', fn ($km) => $km
                        ->where('komunitas_id', $data['komunitas_id'])
                        ->where('status', 'disetujui'))
                )
                ->pluck('id');

            foreach ($anggota as $memberId) {
                IuranMember::query()->create([
                    'iuran_id' => $iuran->id,
                    'member_id' => $memberId,
                    'status' => 'belum',
                ]);
            }

            return [$iuran, $anggota->count()];
        });

        return response()->json([
            'id' => $iuran->id,
            'nama' => $iuran->nama,
            'jumlah' => $iuran->jumlah,
            'periode_id' => $iuran->periode_id,
            'komunitas_id' => $iuran->komunitas_id,
            'tenggat' => $iuran->tenggat?->toDateString(),
            'tagihan_dibuat' => $jumlahTagihan,
        ], 201);
    }

    /** GET /iuran/{id}/tagihan — daftar tagihan per anggota. */
    public function tagihans(Iuran $iuran): JsonResponse
    {
        return response()->json($iuran->tagihans()->with('member:id,nim,nama')->get()
            ->map(fn ($t) => $this->tagihanResource($t))->values());
    }

    /**
     * POST /iuran/tagihan/{id}/lunasi — tandai lunas + transaksi kas
     * pemasukan otomatis dalam SATU transaksi atomik (US-22).
     */
    public function lunasi(Request $request, IuranMember $tagihan): JsonResponse
    {
        if ($tagihan->status === 'lunas') {
            throw Problem::conflict('Tagihan ini sudah lunas.');
        }

        $hasil = DB::transaction(function () use ($request, $tagihan) {
            $iuran = $tagihan->iuran;

            $kas = Kas::query()->create([
                'tanggal' => today(),
                'tipe' => 'pemasukan',
                'nominal' => $iuran->jumlah,
                'kas_kategori_id' => KasKategori::query()->firstOrCreate(
                    ['nama' => 'Iuran'],
                    ['tipe_default' => 'pemasukan']
                )->id,
                'periode_id' => $iuran->periode_id,
                'keterangan' => "Pelunasan iuran: {$iuran->nama} ({$tagihan->member->nama})",
                'member_id' => $tagihan->member_id,
                'user_id' => $request->user()->id,
            ]);

            $tagihan->update([
                'status' => 'lunas',
                'kas_id' => $kas->id,
                'lunas_pada' => now(),
            ]);

            return $tagihan;
        });

        return response()->json($this->tagihanResource($hasil));
    }

    private function tagihanResource(IuranMember $t): array
    {
        return [
            'id' => $t->id,
            'iuran_id' => $t->iuran_id,
            'member' => $t->member ? [
                'nim' => $t->member->nim,
                'nama' => $t->member->nama,
            ] : null,
            'status' => $t->status,
            'kas_id' => $t->kas_id,
            'lunas_pada' => $t->lunas_pada?->toISOString(),
        ];
    }
}
