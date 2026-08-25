import { Badge, Card, EmptyState, SkeletonBlock } from '@himsi/ui'
import { useQuery } from '@tanstack/react-query'
import { api } from '../lib/api'

interface Rapat {
  id: number
  judul: string
  tanggal: string
  jam_mulai: string
  window_aktif: boolean
}

export default function AppPage() {
  const diskusi = useQuery({
    queryKey: ['diskusi'],
    queryFn: () => api.get<{ data: Rapat[] }>('/rapat?komunitas=SIBINER&per_page=10'),
  })

  return (
    <main className="mx-auto max-w-3xl px-5 py-12">
      <h1 className="font-serif text-3xl font-bold text-forest-700">Diskusi</h1>
      <p className="mt-1 text-sm text-gray-500">Jadwal bedah buku komunitas</p>

      <section className="mt-8 space-y-3">
        {diskusi.isPending ? (
          [0, 1].map((i) => <SkeletonBlock key={i} className="h-20" />)
        ) : !diskusi.data || diskusi.data.data.length === 0 ? (
          <EmptyState
            title="Belum ada jadwal diskusi"
            description="Sampai jumpa di sesi berikutnya 📖"
          />
        ) : (
          diskusi.data.data.map((d) => (
            <Card key={d.id} className="border-forest-100">
              <div className="flex flex-wrap items-center gap-2">
                <p className="font-semibold">{d.judul}</p>
                {d.window_aktif ? <Badge tone="green">absensi dibuka</Badge> : null}
              </div>
              <p className="mt-1 font-mono text-xs text-gray-400">
                {new Date(d.tanggal).toLocaleDateString('id-ID', {
                  weekday: 'long',
                  day: 'numeric',
                  month: 'long',
                })}{' '}
                · {d.jam_mulai}
              </p>
            </Card>
          ))
        )}
      </section>
    </main>
  )
}
