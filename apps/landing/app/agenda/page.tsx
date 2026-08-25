import { ambilPublik } from '@/lib/server-api'
import { Badge, EmptyState } from '@himsi/ui'

export const revalidate = 60
export const metadata = { title: 'Agenda' }

interface Event {
  id: number
  judul: string
  lokasi: string | null
  mulai: string
  selesai: string | null
}

export default async function AgendaPage() {
  const data = await ambilPublik<{ data: Event[] }>('/publik/events?mendatang=true&per_page=20')

  return (
    <div className="mx-auto max-w-4xl px-4 py-12">
      <h1 className="font-mono text-lg font-bold tracking-widest text-cobalt-600">
        AGENDA KEGIATAN
      </h1>
      {!data ? (
        <p className="mt-8 text-sm text-gray-500">Agenda sedang tidak tersedia.</p>
      ) : data.data.length === 0 ? (
        <div className="mt-8">
          <EmptyState title="Belum ada agenda mendatang" description="Pantau Instagram kami 📷" />
        </div>
      ) : (
        <ul className="mt-6 space-y-3">
          {data.data.map((e) => (
            <li key={e.id} className="rounded-xl border border-gray-200 bg-white p-5">
              <div className="flex flex-wrap items-center gap-x-3 gap-y-1">
                <span className="font-mono text-sm font-bold text-cobalt-700">
                  {new Date(e.mulai).toLocaleDateString('id-ID', {
                    weekday: 'long',
                    day: 'numeric',
                    month: 'short',
                    year: 'numeric',
                  })}
                </span>
                {e.lokasi ? <Badge>{e.lokasi}</Badge> : null}
              </div>
              <h2 className="mt-2 font-semibold">{e.judul}</h2>
            </li>
          ))}
        </ul>
      )}
    </div>
  )
}
