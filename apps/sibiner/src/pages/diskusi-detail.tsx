import { ApiError, Badge, Button, Card, EmptyState, ScannerOverlay, SkeletonBlock } from '@himsi/ui'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { useState } from 'react'
import { Link, useParams } from 'react-router'
import { api } from '../lib/api'

interface Peserta {
  member?: { nim: string; nama: string } | null
  kehadiran: string | null
}
interface Diskusi {
  id: number
  judul: string
  tanggal: string
  jam_mulai: string
  tempat: string | null
  agenda: string | null
  notulen: string | null
  status: string
  window_aktif: boolean
  peserta: Peserta[]
}

/** Mirror bitsi-rapat-detail.md — label literasi (sibiner-member.md). */
export default function DiskusiDetailPage() {
  const { id } = useParams()
  const queryClient = useQueryClient()
  const [scannerBuka, setScannerBuka] = useState(false)
  const [hasil, setHasil] = useState<string | null>(null)
  const [manualError, setManualError] = useState<string | null>(null)

  const diskusi = useQuery({
    queryKey: ['diskusi', id],
    queryFn: () => api.get<Diskusi>(`/rapat/${id}`),
    refetchInterval: (q) => (q.state.data?.window_aktif ? 30_000 : false),
  })

  const absen = useMutation({
    mutationFn: (token: string) => api.post<{ waktu: string }>(`/rapat/${id}/absen`, { token }),
    onSuccess: async (data) => {
      if (navigator.vibrate) navigator.vibrate(100)
      setHasil(
        new Date(data.waktu).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }),
      )
      setScannerBuka(false)
      await queryClient.invalidateQueries({ queryKey: ['diskusi', id] })
    },
    onError: (err) => {
      if (!(err instanceof ApiError)) return
      if (err.problemSlug === 'qr-expired') setManualError('QR sudah ganti — coba scan lagi')
      else if (err.problemSlug === 'sudah-absen') {
        setHasil('tadi')
        setScannerBuka(false)
      } else if (err.status === 422) setManualError('Kode tidak dikenali.')
      else {
        setScannerBuka(false)
        setManualError(err.message)
      }
    },
  })

  if (diskusi.isPending) {
    return (
      <div className="space-y-3">
        <SkeletonBlock className="h-8 w-2/3" />
        <SkeletonBlock className="h-40" />
      </div>
    )
  }

  const d = diskusi.data
  if (!d) return <EmptyState title="Diskusi tidak ditemukan" />

  const hadirSaya = d.peserta.find((p) => p.kehadiran === 'hadir')

  return (
    <div className="space-y-6">
      <Link to="/app" className="font-mono text-xs text-forest-600">
        ← DISKUSI
      </Link>

      <div>
        {d.status === 'dijadwalkan' && d.window_aktif ? (
          <Badge tone="green">
            <span aria-hidden>🔴</span> BERLANGSUNG
          </Badge>
        ) : null}
        {d.status === 'selesai' ? <Badge tone="neutral">✅ selesai</Badge> : null}
      </div>

      <header>
        <h1 className="font-serif text-2xl font-bold text-forest-700">{d.judul}</h1>
        <p className="mt-1 text-sm text-gray-500">
          📖{' '}
          {new Date(d.tanggal).toLocaleDateString('id-ID', {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
          })}{' '}
          · {d.jam_mulai}
          {d.tempat ? ` · ${d.tempat}` : ''}
        </p>
      </header>

      {/* ABSENSI */}
      <section aria-label="Absensi">
        {hadirSaya || hasil ? (
          <Card className="border-l-4 border-l-green-600 bg-forest-50/60">
            <p className="text-sm font-semibold text-forest-700">
              ✅ HADIR{hasil && hasil !== 'tadi' ? ` · tercatat ${hasil}` : ''}
            </p>
          </Card>
        ) : d.window_aktif ? (
          <Card className="border-2 border-forest-600 bg-forest-50/40 text-center">
            <p className="text-sm font-semibold text-forest-800">Belum absen? Sekarang waktunya!</p>
            <Button
              className="mt-4 !bg-forest-700 !px-10 !py-3.5 hover:!bg-forest-600"
              onClick={() => setScannerBuka(true)}
            >
              📷 SCAN ABSENSI
            </Button>
          </Card>
        ) : (
          <Card>
            <p className="text-sm text-gray-500">⏰ Absensi dibuka saat diskusi berlangsung.</p>
          </Card>
        )}
        {manualError ? (
          <p role="alert" className="mt-2 rounded-lg bg-red-50 p-3 text-sm text-red-700">
            {manualError}
          </p>
        ) : null}
      </section>

      {/* POIN BAHASAN */}
      {d.agenda ? (
        <section>
          <h2 className="font-mono text-xs font-bold tracking-widest text-gray-400">
            POIN BAHASAN
          </h2>
          <Card className="mt-2 whitespace-pre-wrap text-sm">{d.agenda}</Card>
        </section>
      ) : null}

      {/* CATATAN DISKUSI (notulen) */}
      {d.status === 'selesai' ? (
        <section>
          <h2 className="font-mono text-xs font-bold tracking-widest text-gray-400">
            CATATAN DISKUSI
          </h2>
          <Card className="mt-2">
            {d.notulen ? (
              <p className="whitespace-pre-wrap text-sm">{d.notulen}</p>
            ) : (
              <p className="text-sm italic text-gray-400">Catatan menyusul setelah sesi.</p>
            )}
          </Card>
        </section>
      ) : null}

      {scannerBuka ? (
        <ScannerOverlay
          onDecode={(t) => absen.mutate(t)}
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
