import { ApiError, Badge, Button, Card, EmptyState, SkeletonBlock } from '@himsi/ui'
import { ScannerOverlay } from '@himsi/ui'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { useState } from 'react'
import { Link, useParams } from 'react-router'
import { api } from '../lib/api'

interface Peserta {
  member?: { nim: string; nama: string } | null
  kehadiran: string | null
  waktu_absen: string | null
  catatan: string | null
}
interface Rapat {
  id: number
  judul: string
  tanggal: string
  jam_mulai: string
  jam_selesai: string | null
  tempat: string | null
  agenda: string | null
  notulen: string | null
  lampiran_path: string | null
  status: string
  window_aktif: boolean
  peserta: Peserta[]
}

type Fase = 'belum' | 'aktif' | 'selesai'

export default function RapatDetailPage() {
  const { id } = useParams()
  const queryClient = useQueryClient()
  const [scannerBuka, setScannerBuka] = useState(false)
  const [hasilAbsen, setHasilAbsen] = useState<{ waktu: string } | null>(null)
  const [pesanError, setPesanError] = useState<string | null>(null)
  const [manualError, setManualError] = useState<string | null>(null)

  const rapat = useQuery({
    queryKey: ['rapat', id],
    queryFn: () => api.get<Rapat>(`/rapat/${id}`),
    refetchInterval: (q) => (q.state.data?.window_aktif ? 30_000 : false),
  })

  function prosesToken(token: string) {
    setPesanError(null)
    setManualError(null)

    mutation.mutate(token)
  }

  const mutation = useMutation({
    mutationFn: (token: string) =>
      api.post<{ kehadiran: string; waktu: string }>(`/rapat/${id}/absen`, { token }),
    onSuccess: async (data) => {
      if (navigator.vibrate) navigator.vibrate(100)
      setHasilAbsen(data)
      setScannerBuka(false)
      await queryClient.invalidateQueries({ queryKey: ['rapat', id] })
    },
    onError: (err) => {
      if (!(err instanceof ApiError)) return

      switch (err.problemSlug) {
        case 'qr-expired':
          setPesanError('QR sudah ganti — coba scan lagi')
          break
        case 'sudah-absen':
          setHasilAbsen({ waktu: 'tadi' })
          setScannerBuka(false)
          break
        case 'jendela-tutup':
          setScannerBuka(false)
          setPesanError('Jendela absensi belum dibuka / sudah ditutup.')
          break
        default:
          if (err.status === 422) setManualError('Kode tidak dikenali.')
          else setPesanError(err.message)
      }
    },
  })

  if (rapat.isPending) {
    return (
      <div className="space-y-3">
        <SkeletonBlock className="h-8 w-2/3" />
        <SkeletonBlock className="h-40 w-full" />
        <SkeletonBlock className="h-24 w-full" />
      </div>
    )
  }

  const r = rapat.data
  if (!r) return <EmptyState title="Rapat tidak ditemukan" />

  const fase: Fase =
    r.status !== 'dijadwalkan' || hasilAbsen ? 'selesai' : r.window_aktif ? 'aktif' : 'belum'
  const kehadiranSaya = r.peserta.find((p) => p.kehadiran === 'hadir')

  return (
    <div className="space-y-6">
      <Link to="/app/rapat" className="font-mono text-xs text-gray-400 hover:text-cobalt-600">
        ← RAPAT
      </Link>

      {/* Status banner */}
      <div className="flex flex-wrap items-center gap-2">
        {r.status === 'dibatalkan' ? (
          <Badge tone="red">dibatalkan</Badge>
        ) : fase === 'aktif' ? (
          <Badge tone="green">
            <span aria-hidden>🔴</span> BERLANGSUNG
          </Badge>
        ) : fase === 'selesai' ? (
          <Badge tone="neutral">✅ selesai</Badge>
        ) : (
          <Badge tone="cobalt">terjadwal</Badge>
        )}
      </div>

      <header>
        <h1 className="text-xl font-bold">{r.judul}</h1>
        <p className="mt-1 font-mono text-sm text-gray-500">
          {new Date(r.tanggal).toLocaleDateString('id-ID', {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            year: 'numeric',
          })}
          {' · '}
          {r.jam_mulai}
          {r.jam_selesai ? `–${r.jam_selesai}` : ''}
          {r.tempat ? ` · ${r.tempat}` : ''}
        </p>
      </header>

      {r.status === 'dibatalkan' ? (
        <p className="rounded-lg bg-gray-100 p-4 text-sm text-gray-600">Rapat ini dibatalkan.</p>
      ) : null}

      {/* ABSENSI — konten berganti per fase */}
      <section aria-label="Absensi">
        {kehadiranSaya || hasilAbsen ? (
          <Card className="border-l-4 border-l-green-500 bg-green-50/50">
            <p className="text-sm font-semibold text-green-800">
              ✅ HADIR
              {hasilAbsen?.waktu && hasilAbsen.waktu !== 'tadi'
                ? ` · tercatat ${new Date(hasilAbsen.waktu).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })}`
                : ''}
            </p>
          </Card>
        ) : fase === 'aktif' ? (
          <Card className="border-2 border-cobalt-500 bg-cobalt-50/50 text-center">
            <p className="text-sm font-semibold text-cobalt-800">Belum absen? Sekarang waktunya!</p>
            <Button className="mt-4 !px-10 !py-3.5 text-base" onClick={() => setScannerBuka(true)}>
              📷 SCAN ABSENSI
            </Button>
            <p className="mt-3 text-xs text-gray-500">atau ketik kode manual dari pengurus</p>
          </Card>
        ) : (
          <Card>
            <p className="text-sm text-gray-600">⏰ Absensi dibuka saat rapat berlangsung.</p>
          </Card>
        )}
        {pesanError ? (
          <p role="alert" className="mt-2 rounded-lg bg-red-50 p-3 text-sm text-red-700">
            {pesanError}
          </p>
        ) : null}
      </section>

      {/* AGENDA */}
      {r.agenda ? (
        <section>
          <h2 className="font-mono text-xs font-bold tracking-widest text-gray-500">AGENDA</h2>
          <Card className="mt-2 whitespace-pre-wrap text-sm text-gray-700">{r.agenda}</Card>
        </section>
      ) : null}

      {/* NOTULEN — hanya setelah selesai */}
      {fase === 'selesai' ? (
        <section>
          <h2 className="font-mono text-xs font-bold tracking-widest text-gray-500">NOTULEN</h2>
          <Card className="mt-2">
            {r.notulen ? (
              <>
                <p className="whitespace-pre-wrap text-sm text-gray-700">{r.notulen}</p>
                {r.lampiran_path ? (
                  <a
                    href={`${import.meta.env.VITE_API_URL.replace(/\/api\/v1$/, '')}/storage/${r.lampiran_path}`}
                    target="_blank"
                    rel="noreferrer"
                    className="mt-3 inline-block font-mono text-xs font-bold tracking-wide text-cobalt-600"
                  >
                    📎 unduh lampiran
                  </a>
                ) : null}
              </>
            ) : (
              <p className="text-sm text-gray-400">Notulen menyusul setelah rapat.</p>
            )}
          </Card>
        </section>
      ) : null}

      {/* PESERTA */}
      <details className="rounded-xl border border-gray-200 bg-white">
        <summary className="cursor-pointer px-5 py-3 text-sm font-semibold">
          PESERTA ({r.peserta.length})
        </summary>
        <ul className="divide-y divide-gray-50 border-t border-gray-100">
          {r.peserta.map((p, i) => (
            <li
              key={`${p.member?.nim ?? i}`}
              className="flex items-center justify-between px-5 py-2.5 text-sm"
            >
              <span>{p.member?.nama ?? '—'}</span>
              {p.kehadiran === 'hadir' ? (
                <Badge tone="green">hadir</Badge>
              ) : p.kehadiran === 'izin' ? (
                <Badge tone="amber">izin</Badge>
              ) : p.kehadiran === 'tidak' ? (
                <Badge tone="red">tidak</Badge>
              ) : (
                <span className="text-xs text-gray-300">belum absen</span>
              )}
            </li>
          ))}
        </ul>
      </details>

      {scannerBuka ? (
        <ScannerOverlay
          onDecode={prosesToken}
          manualError={manualError}
          onClose={() => {
            setScannerBuka(false)
            setManualError(null)
          }}
        />
      ) : null}
    </div>
  )
}
