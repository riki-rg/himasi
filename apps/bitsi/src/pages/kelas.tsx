import { Card, EmptyState, SkeletonBlock } from '@himsi/ui'
import { useQuery } from '@tanstack/react-query'
import { Link, useParams } from 'react-router'
import { api } from '../lib/api'

interface Kelas {
  id: number
  nama: string
  deskripsi: string | null
  divisi?: string | null
  jadwal_hari: string | null
  jadwal_jam: string | null
  tempat: string | null
  jumlah_materi?: number
}
interface Materi {
  id: number
  judul: string
  tipe: 'file' | 'link'
  file_path: string | null
  link_url: string | null
  urutan: number
}

const BASE_STORAGE = (import.meta.env.VITE_API_URL ?? '').replace(/\/api\/v1$/, '')

export function KelasCatalogPage() {
  const kelas = useQuery({
    queryKey: ['kelas'],
    queryFn: () => api.get<Kelas[]>('/publik/kelass?komunitas=BITSI'),
  })

  return (
    <div>
      <h1 className="text-xl font-bold">Kelas Belajar</h1>

      {kelas.isPending ? (
        <div className="mt-4 grid gap-3 sm:grid-cols-3">
          {[0, 1, 2].map((i) => (
            <SkeletonBlock key={i} className="h-24" />
          ))}
        </div>
      ) : !kelas.data || kelas.data.length === 0 ? (
        <div className="mt-6">
          <EmptyState title="Belum ada kelas dibuka" description="Nantikan info" />
        </div>
      ) : (
        <div className="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
          {kelas.data.map((k) => (
            <Link key={k.id} to={`/app/kelas/${k.id}`}>
              <Card className="h-full transition hover:-translate-y-0.5 hover:shadow-md">
                <p className="font-mono text-[10px] font-bold tracking-widest text-cobalt-600">
                  {(k.divisi ?? 'UMUM').toUpperCase()}
                </p>
                <p className="mt-1 font-semibold">{k.nama}</p>
                <p className="mt-1 font-mono text-xs text-gray-400">
                  {k.jadwal_hari ?? '-'} {k.jadwal_jam ?? ''}
                  {k.tempat ? ` · ${k.tempat}` : ''}
                </p>
              </Card>
            </Link>
          ))}
        </div>
      )}
    </div>
  )
}

export function KelasDetailPage() {
  const { id } = useParams()

  const kelas = useQuery({
    queryKey: ['kelas', id],
    queryFn: () =>
      api.get<Kelas & { pengajar: { nama: string }[]; materis: Materi[] }>(`/kelass/${id}`),
    retry: false,
  })

  if (kelas.isPending) return <SkeletonBlock className="h-48 w-full" />
  if (kelas.isError) {
    return (
      <EmptyState
        title="Kamu tidak punya akses"
        description="Materi khusus anggota komunitas yang sudah disetujui."
      />
    )
  }

  const k = kelas.data

  return (
    <div className="space-y-5">
      <Link to="/app" className="font-mono text-xs text-gray-400 hover:text-cobalt-600">
        ← KELAS
      </Link>
      <header>
        <p className="font-mono text-[10px] font-bold tracking-widest text-cobalt-600">
          {(k.divisi ?? 'UMUM').toUpperCase()}
        </p>
        <h1 className="mt-1 text-xl font-bold">{k.nama}</h1>
        <p className="mt-1 text-sm text-gray-500">
          Jadwal rutin: {k.jadwal_hari ?? '-'} {k.jadwal_jam ?? ''}
          {k.tempat ? ` · ${k.tempat}` : ''}
          {k.pengajar?.length ? ` · 👨‍🏫 ${k.pengajar.map((p) => p.nama).join(', ')}` : ''}
        </p>
      </header>

      <section aria-label="Materi sesi">
        <h2 className="font-mono text-xs font-bold tracking-widest text-gray-500">MATERI SESI</h2>
        {!k.materis || k.materis.length === 0 ? (
          <div className="mt-2">
            <EmptyState title="Materi akan diunggah setelah sesi pertama" />
          </div>
        ) : (
          <ol className="mt-2 divide-y divide-gray-100 rounded-xl border border-gray-200 bg-white">
            {k.materis.map((m) => (
              <li key={m.id} className="flex items-center justify-between gap-3 px-4 py-3">
                <span className="flex min-w-0 items-center gap-3">
                  <span className="font-mono text-xs text-gray-400">
                    {String(m.urutan).padStart(2, '0')}
                  </span>
                  <span className="truncate text-sm font-medium">{m.judul}</span>
                </span>
                {m.tipe === 'file' && m.file_path ? (
                  <a
                    href={`${BASE_STORAGE}/storage/${m.file_path}`}
                    target="_blank"
                    rel="noreferrer"
                    className="shrink-0 rounded-md bg-cobalt-50 px-3 py-1.5 font-mono text-[11px] font-bold text-cobalt-700"
                  >
                    ⬇ UNDUH
                  </a>
                ) : m.link_url ? (
                  <a
                    href={m.link_url}
                    target="_blank"
                    rel="noreferrer"
                    className="shrink-0 rounded-md bg-gray-50 px-3 py-1.5 font-mono text-[11px] font-bold text-gray-600"
                  >
                    🔗 BUKA
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
