import { Badge, Card, EmptyState, SkeletonBlock } from '@himsi/ui'
import { useQuery } from '@tanstack/react-query'
import { useOutletContext } from 'react-router'
import { type MeResponse, api } from '../lib/api'

interface Rapat {
  id: number
  judul: string
  tanggal: string
  jam_mulai: string
  tempat: string | null
  window_aktif: boolean
}
interface KelasItem {
  id: number
  nama: string
  jadwal_hari: string | null
  jadwal_jam: string | null
}
interface Pengumuman {
  id: number
  judul: string
  prioritas: string
}

export default function DashboardPage() {
  const me = useOutletContext<ReturnType<typeof useQuery<MeResponse>>>()

  const rapat = useQuery({
    queryKey: ['rapat-terdekat'],
    queryFn: () =>
      api.get<{ data: Rapat[] }>('/rapat?komunitas=BITSI&status=dijadwalkan&per_page=1'),
  })

  const kelas = useQuery({
    queryKey: ['kelas'],
    queryFn: () => api.get<KelasItem[]>('/publik/kelass?komunitas=BITSI'),
  })

  const pengumuman = useQuery({
    queryKey: ['pengumuman'],
    queryFn: () => api.get<Pengumuman[]>('/publik/pengumumans?komunitas=BITSI'),
  })

  const nama = me.data?.member?.nama ?? me.data?.name ?? 'BitSI member'

  return (
    <div className="space-y-8">
      <header>
        <h1 className="rise text-xl font-bold">Selamat datang, {nama} 👋</h1>
        <p className="mt-1 text-sm text-gray-500">
          anggota BITSI · angkatan {me.data?.member?.angkatan ?? '-'}
        </p>
      </header>

      {/* RAPAT TERDEKAT — hero card */}
      <section aria-label="Rapat terdekat">
        {rapat.isPending ? (
          <SkeletonBlock className="h-28 w-full" />
        ) : !rapat.data || rapat.data.data.length === 0 ? (
          <EmptyState title="Belum ada rapat dijadwalkan" description="Pantau terus ya" />
        ) : (
          (() => {
            const r = rapat.data.data[0]
            if (!r) return null
            return (
              <Card className="breathe border-l-4 border-l-cobalt-600">
                <div className="flex flex-wrap items-center gap-2">
                  <span className="size-2 animate-pulse rounded-full bg-cobalt-500" aria-hidden />
                  <p className="font-mono text-xs font-bold tracking-widest text-cobalt-700">
                    {r.judul.toUpperCase()}
                  </p>
                </div>
                <p className="mt-2 text-sm text-gray-700">
                  {new Date(r.tanggal).toLocaleDateString('id-ID', {
                    weekday: 'long',
                    day: 'numeric',
                    month: 'short',
                  })}{' '}
                  · {r.jam_mulai} {r.tempat ? `· ${r.tempat}` : ''}
                </p>
                {r.window_aktif ? (
                  <Badge tone="green">Absensi dibuka — scan sekarang!</Badge>
                ) : null}
              </Card>
            )
          })()
        )}
      </section>

      {/* KELAS TERDEKAT */}
      <section aria-label="Kelas">
        <h2 className="font-mono text-xs font-bold tracking-widest text-gray-500">KELAS BELAJAR</h2>
        {kelas.isPending ? (
          <div className="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-3">
            {[0, 1, 2].map((i) => (
              <SkeletonBlock key={i} className="h-24" />
            ))}
          </div>
        ) : !kelas.data || kelas.data.length === 0 ? null : (
          <div className="mt-3 grid gap-3 sm:grid-cols-3">
            {kelas.data.slice(0, 3).map((k) => (
              <Card key={k.id}>
                <p className="font-semibold">{k.nama}</p>
                <p className="mt-1 font-mono text-xs text-gray-400">
                  {k.jadwal_hari ?? '-'} {k.jadwal_jam ?? ''}
                </p>
              </Card>
            ))}
          </div>
        )}
      </section>

      {/* PENGUMUMAN */}
      <section aria-label="Pengumuman">
        <h2 className="font-mono text-xs font-bold tracking-widest text-gray-500">PENGUMUMAN</h2>
        {!pengumuman.data || pengumuman.data.length === 0 ? null : (
          <ul className="mt-3 space-y-2">
            {pengumuman.data.slice(0, 3).map((p) => (
              <li key={p.id} className="flex items-center gap-2 text-sm">
                📢<span>{p.judul}</span>
                {p.prioritas === 'penting' ? <Badge tone="amber">penting</Badge> : null}
              </li>
            ))}
          </ul>
        )}
      </section>
    </div>
  )
}
