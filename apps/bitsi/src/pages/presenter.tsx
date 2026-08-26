import { Badge, Button, Card, EmptyState } from '@himsi/ui'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { useCallback, useEffect, useRef, useState } from 'react'
import QRCode from 'react-qr-code'
import { Link } from 'react-router'
import { api } from '../lib/api'

interface Rapat {
  id: number
  judul: string
  tanggal: string
  jam_mulai: string
  tempat: string | null
  window_aktif: boolean
}
interface QrPayload {
  payload: string
  expires_in: number
}
interface Rekap {
  hadir: number
  total_peserta: number
  rincian: { member?: { nama: string } | null; kehadiran: string | null }[]
}

const REFRESH_AWAL_MS = 52_000

export default function PresenterPage() {
  const [rapatAktif, setRapatAktif] = useState<Rapat | null>(null)
  const rapatHariIni = useQuery({
    queryKey: ['presenter-rapat'],
    queryFn: () =>
      api.get<{ data: Rapat[] }>('/rapat?komunitas=BITSI&status=dijadwalkan&per_page=10'),
  })

  if (!rapatAktif) {
    const hariIni = rapatHariIni.data?.data ?? []
    return (
      <div className="mx-auto max-w-2xl">
        <h1 className="text-xl font-bold">Mode Absensi</h1>
        {!rapatHariIni.data ? (
          <p className="mt-6 text-sm text-gray-500">Memuat…</p>
        ) : hariIni.length === 0 ? (
          <div className="mt-8">
            <EmptyState
              title="Tidak ada rapat hari ini"
              description="Buat rapat dulu lewat panel."
            />
          </div>
        ) : (
          <ul className="mt-6 space-y-3">
            {hariIni.map((r) => (
              <li key={r.id}>
                <Card className="flex flex-wrap items-center justify-between gap-3">
                  <div>
                    <p className="font-semibold">{r.judul}</p>
                    <p className="font-mono text-xs text-gray-400">
                      {r.jam_mulai} · {r.tempat ?? '—'}
                    </p>
                  </div>
                  <Button onClick={() => setRapatAktif(r)}>MULAI ABSENSI →</Button>
                </Card>
              </li>
            ))}
          </ul>
        )}
      </div>
    )
  }

  return <PresentationMode rapat={rapatAktif} onKeluar={() => setRapatAktif(null)} />
}

function PresentationMode({ rapat, onKeluar }: { rapat: Rapat; onKeluar: () => void }) {
  const queryClient = useQueryClient()
  const containerRef = useRef<HTMLDivElement>(null)
  const lastFetchRef = useRef(Date.now())
  const [payload, setPayload] = useState<QrPayload | null>(null)
  const [sisaDetik, setSisaDetik] = useState(60)
  const [jeda, setJeda] = useState(false)
  const [konfirmasiKeluar, setKonfirmasiKeluar] = useState(false)

  const ambilQr = useMutation({
    mutationFn: () => api.get<QrPayload>(`/rapat/${rapat.id}/qr`),
    onSuccess: (data) => {
      setPayload(data)
      setSisaDetik(data.expires_in)
      lastFetchRef.current = Date.now()
    },
  })

  useEffect(() => {
    ambilQr.mutate()
    void navigator.wakeLock?.request('screen').catch(() => {})
  })

  /* Ticker 1 detik — refresh payload di t=52s (8s sebelum kedaluwarsa). */
  useEffect(() => {
    if (jeda) return
    const timer = setInterval(() => {
      setSisaDetik((s) => Math.max(0, s - 1))
      if (Date.now() - lastFetchRef.current >= REFRESH_AWAL_MS) {
        ambilQr.mutate()
      }
    }, 1000)
    return () => clearInterval(timer)
  }, [jeda])

  /* Counter absen poll tiap 10s */
  const rekap = useQuery({
    queryKey: ['presenter-rekap', rapat.id],
    queryFn: () => api.get<Rekap>(`/rapat/${rapat.id}/rekap`),
    refetchInterval: jeda ? false : 10_000,
  })

  const akhiri = useMutation({
    mutationFn: () => api.put(`/rapat/${rapat.id}`, { status: 'selesai' }),
    onSettled: async () => {
      await queryClient.invalidateQueries({ queryKey: ['rapat'] })
      onKeluar()
    },
  })

  const mintaFullscreen = useCallback(() => {
    void containerRef.current?.requestFullscreen?.().catch(() => {})
  }, [])

  const baruMasuk = (rekap.data?.rincian ?? [])
    .filter((r) => r.kehadiran === 'hadir')
    .slice(-2)
    .map((r) => pendek(r.member?.nama ?? ''))

  function pendek(nama: string): string {
    const bagian = nama.split(' ')
    return `${bagian[0]} ${bagian[1]?.charAt(0) ?? ''}.`.trim()
  }

  if (konfirmasiKeluar || akhiri.isPending) {
    return (
      <div className="grid min-h-[70vh] place-items-center px-5">
        <Card className="max-w-sm text-center">
          <p className="font-semibold">Akhiri sesi absensi?</p>
          <p className="mt-2 font-mono text-xs text-gray-400">
            hadir {rekap.data?.hadir ?? 0}/{rekap.data?.total_peserta ?? '?'}
          </p>
          <div className="mt-5 flex justify-center gap-2">
            <Button
              variant="ghost"
              onClick={() => setKonfirmasiKeluar(false)}
              disabled={akhiri.isPending}
            >
              Batal
            </Button>
            <Button onClick={() => akhiri.mutate()} loading={akhiri.isPending}>
              Ya, akhiri
            </Button>
          </div>
        </Card>
      </div>
    )
  }

  return (
    <div
      ref={containerRef}
      className="blueprint-grid -m-4 min-h-[calc(100vh+2rem)] p-4 md:-m-6 md:min-h-[calc(100vh+3rem)] md:p-6"
    >
      <header className="flex items-start justify-between gap-3">
        <div>
          <p className="font-mono text-xs font-bold tracking-widest text-cobalt-700">
            {rapat.judul.toUpperCase()}
          </p>
          <p className="text-xs text-gray-500">{rapat.tempat ?? ''}</p>
        </div>
        <div className="flex gap-2">
          <button
            type="button"
            aria-label={jeda ? 'Lanjutkan' : 'Jeda'}
            onClick={() => setJeda((v) => !v)}
            className="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm"
          >
            {jeda ? '▶' : '⏸'}
          </button>
          <button
            type="button"
            onClick={mintaFullscreen}
            aria-label="Layar penuh"
            className="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm"
          >
            ⛶
          </button>
          <button
            type="button"
            onClick={() => setKonfirmasiKeluar(true)}
            aria-label="Keluar"
            className="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm"
          >
            ✕
          </button>
        </div>
      </header>

      {jeda ? (
        <div className="mt-16 rounded-xl border border-dashed border-gray-300 p-10 text-center text-gray-500">
          ⏸ Sesuatu dijeda — QR disembunyikan sementara.
        </div>
      ) : (
        <>
          <div className="mx-auto mt-8 w-fit bg-white p-5 shadow-xl">
            {payload ? (
              <QRCode
                value={payload.payload}
                size={Math.min(
                  360,
                  typeof window !== 'undefined' ? Math.min(window.innerWidth - 120, 420) : 320,
                )}
                bgColor="#ffffff"
                fgColor="#000000"
              />
            ) : (
              <div className="grid size-64 place-items-center text-xs text-gray-400">
                menyiapkan QR…
              </div>
            )}
          </div>

          {/* Rotasi ring */}
          <p
            className="mt-4 text-center font-mono text-xs tracking-widest text-gray-600"
            aria-live="polite"
          >
            ⟳ rotasi otomatis · {'▓'.repeat(Math.ceil(sisaDetik / 10))}
            {'░'.repeat(6 - Math.ceil(sisaDetik / 10))} {sisaDetik}s
            {ambilQr.isPending ? (
              <span className="ml-2 text-cobalt-600">(menyegarkan…)</span>
            ) : null}
          </p>
          {payload ? (
            <p className="mt-2 break-all px-6 text-center font-mono text-[10px] leading-relaxed text-gray-400 select-all">
              kode manual: {payload.payload}
            </p>
          ) : null}
        </>
      )}

      {/* Live counter */}
      <footer className="mx-auto mt-10 max-w-md text-center">
        <p className="font-mono text-2xl font-bold text-cobalt-700">
          👥 {rekap.data?.hadir ?? 0} sudah absen
        </p>
        {baruMasuk.length > 0 && !jeda ? (
          <p className="mt-2 text-sm text-cobalt-600">✨ baru masuk: {baruMasuk.join(' · ')}</p>
        ) : null}
        <p className="mt-4 text-xs text-gray-400">
          Scan pakai kamera HP — tidak perlu install apapun
        </p>
      </footer>

      <div className="mt-6 flex justify-center">
        <Link
          to="/app/rapat"
          className="font-mono text-xs text-gray-400 hover:text-cobalt-600"
          onClick={(e) => e.preventDefault()}
        >
          <Badge>mode presenter aktif</Badge>
        </Link>
      </div>
    </div>
  )
}
