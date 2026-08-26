import { Badge, Card, EmptyState, SkeletonBlock } from '@himsi/ui'
import { useQuery } from '@tanstack/react-query'
import { Link } from 'react-router'
import { api } from '../lib/api'

interface Rapat {
  id: number
  judul: string
  tanggal: string
  jam_mulai: string
  tempat: string | null
  status: string
  window_aktif: boolean
}

export default function RapatListPage() {
  const rapat = useQuery({
    queryKey: ['rapat'],
    queryFn: () => api.get<{ data: Rapat[] }>('/rapat?komunitas=BITSI&per_page=50'),
  })

  const semua = rapat.data?.data ?? []
  const mendatang = semua.filter((r) => r.status === 'dijadwalkan')
  const lampau = semua.filter((r) => r.status !== 'dijadwalkan')

  function Grup({ judul, items }: { judul: string; items: Rapat[] }) {
    return (
      <section className="mt-6">
        <h2 className="font-mono text-xs font-bold tracking-widest text-gray-500">
          {judul.toUpperCase()}
        </h2>
        <div className="mt-3 space-y-2">
          {items.map((r) => (
            <Link key={r.id} to={`/app/rapat/${r.id}`} className="block">
              <Card className="transition hover:-translate-y-0.5 hover:shadow-md">
                <div className="flex flex-wrap items-center justify-between gap-2">
                  <p className="font-semibold">{r.judul}</p>
                  {r.window_aktif ? (
                    <Badge tone="green">berlangsung</Badge>
                  ) : r.status === 'dibatalkan' ? (
                    <Badge tone="red">dibatalkan</Badge>
                  ) : r.status === 'selesai' ? (
                    <Badge>selesai</Badge>
                  ) : null}
                </div>
                <p className="mt-1 font-mono text-xs text-gray-400">
                  {new Date(r.tanggal).toLocaleDateString('id-ID', {
                    weekday: 'short',
                    day: 'numeric',
                    month: 'short',
                  })}{' '}
                  · {r.jam_mulai} {r.tempat ? `· ${r.tempat}` : ''}
                </p>
              </Card>
            </Link>
          ))}
        </div>
      </section>
    )
  }

  return (
    <div>
      <h1 className="text-xl font-bold">Rapat</h1>

      {rapat.isPending ? (
        <div className="mt-4 space-y-2">
          {[0, 1].map((i) => (
            <SkeletonBlock key={i} className="h-20" />
          ))}
        </div>
      ) : semua.length === 0 ? (
        <div className="mt-6">
          <EmptyState title="Belum ada rapat dijadwalkan" description="Pantau terus ya" />
        </div>
      ) : (
        <>
          <Grup judul="Mendatang" items={mendatang} />
          <Grup judul="Lampau" items={lampau} />
        </>
      )}
    </div>
  )
}
