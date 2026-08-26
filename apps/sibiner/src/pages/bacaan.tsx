import { Card, EmptyState, SkeletonBlock } from '@himsi/ui'
import { useQuery } from '@tanstack/react-query'
import { Link, useParams } from 'react-router'
import { api } from '../lib/api'

interface Bacaan {
  id: number
  nama: string
  deskripsi: string | null
  jadwal_hari: string | null
  jadwal_jam: string | null
}
interface Materi {
  id: number
  judul: string
  tipe: 'file' | 'link'
  file_path: string | null
  link_url: string | null
  urutan: number
}

const BASE = (import.meta.env.VITE_API_URL ?? '').replace(/\/api\/v1$/, '')

export function BacaanKatalog() {
  const bacaan = useQuery({
    queryKey: ['bacaan'],
    queryFn: () => api.get<Bacaan[]>('/publik/kelass?komunitas=SIBINER'),
  })

  return (
    <div>
      <h1 className="font-serif text-2xl font-bold text-forest-700">Bacaan Kami</h1>
      {bacaan.isPending ? (
        <div className="mt-4 grid gap-3 sm:grid-cols-2">
          {[0, 1].map((i) => (
            <SkeletonBlock key={i} className="h-24" />
          ))}
        </div>
      ) : !bacaan.data || bacaan.data.length === 0 ? (
        <div className="mt-6">
          <EmptyState title="Belum ada sesi bacaan dibuka" />
        </div>
      ) : (
        <div className="mt-4 grid gap-3 sm:grid-cols-2">
          {bacaan.data.map((b) => (
            <Link key={b.id} to={`/app/bacaan/${b.id}`}>
              <Card className="border-forest-100 transition hover:-translate-y-0.5 hover:shadow-md">
                <p className="font-semibold">{b.nama}</p>
                {b.deskripsi ? (
                  <p className="mt-1 line-clamp-2 text-xs text-gray-500">{b.deskripsi}</p>
                ) : null}
                <p className="mt-2 font-mono text-xs text-gray-400">
                  📖 {b.jadwal_hari ?? '-'} {b.jadwal_jam ?? ''}
                </p>
              </Card>
            </Link>
          ))}
        </div>
      )}
    </div>
  )
}

export function BacaanDetail() {
  const { id } = useParams()
  const detail = useQuery({
    queryKey: ['bacaan', id],
    queryFn: () =>
      api.get<Bacaan & { pengajar?: { nama: string }[]; materis: Materi[] }>(`/kelass/${id}`),
    retry: false,
  })

  if (detail.isPending) return <SkeletonBlock className="h-48" />
  if (detail.isError || !detail.data) {
    return (
      <EmptyState
        title="Rangkuman khusus anggota disetujui"
        description="Hubungi ketua Divisi Organisasi."
      />
    )
  }

  const b = detail.data

  return (
    <div className="space-y-5">
      <Link to="/app" className="font-mono text-xs text-forest-600">
        ← BACAAN
      </Link>
      <header>
        <h1 className="font-serif text-2xl font-bold italic text-forest-700">{b.nama}</h1>
        {b.pengajar?.length ? (
          <p className="mt-1 text-sm text-gray-500">
            dibahas oleh {b.pengajar.map((p) => p.nama).join(', ')}
          </p>
        ) : null}
      </header>

      <section aria-label="Sesi & rangkuman">
        {!b.materis || b.materis.length === 0 ? (
          <EmptyState title="Rangkuman menyusul setelah sesi pertama ⏳" />
        ) : (
          <ol className="divide-y divide-forest-50 rounded-xl border border-forest-100 bg-white">
            {b.materis.map((m) => (
              <li key={m.id} className="flex items-center justify-between gap-3 px-4 py-3">
                <span className="flex min-w-0 items-center gap-3">
                  <span className="font-mono text-xs text-forest-600">sesi {m.urutan}</span>
                  <span className="truncate text-sm">{m.judul}</span>
                </span>
                {m.tipe === 'file' && m.file_path ? (
                  <a
                    href={`${BASE}/storage/${m.file_path}`}
                    target="_blank"
                    rel="noreferrer"
                    className="shrink-0 rounded-md bg-forest-50 px-3 py-1.5 font-mono text-[11px] font-bold text-forest-700"
                  >
                    ⬇ unduh
                  </a>
                ) : m.link_url ? (
                  <a
                    href={m.link_url}
                    target="_blank"
                    rel="noreferrer"
                    className="shrink-0 rounded-md bg-kertas px-3 py-1.5 font-mono text-[11px] font-bold text-forest-700"
                  >
                    🔗 buka
                  </a>
                ) : null}
              </li>
            ))}
          </ol>
        )}
      </section>
    </div>
  )
}
