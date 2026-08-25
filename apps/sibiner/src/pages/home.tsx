import { Badge, Card, EmptyState, SkeletonBlock } from '@himsi/ui'
import { useQuery } from '@tanstack/react-query'
import { api } from '../lib/api'

interface Proyek {
  id: number
  judul: string
  slug: string
  deskripsi: string | null
  teknologi: string[] | null
  pembuat?: { nama: string } | null
}

export default function HomePage() {
  const rak = useQuery({
    queryKey: ['rak-buku'],
    queryFn: () => api.get<{ data: Proyek[] }>('/publik/proyeks?komunitas=SIBINER&per_page=12'),
  })

  return (
    <main className="mx-auto max-w-5xl px-5 py-14">
      {/* HERO */}
      <section className="text-center">
        <p aria-hidden className="text-3xl">
          📚
        </p>
        <h1 className="mt-4 font-serif text-4xl font-bold italic leading-tight text-forest-700 md:text-5xl">
          Sistem Informasi Bicara Nalar
          <br />
          dan Literasi
        </h1>
        <p className="mx-auto mt-5 max-w-xl text-gray-600">
          Komunitas baca-baca bareng anggota SI UMKU. Dari novel sampai nonfiksi — dibahas,
          diperdebatkan, dirangkum bareng.
        </p>
      </section>

      {/* RAK BUKU */}
      <section className="mt-16" aria-label="Rak buku kami">
        <div className="flex items-end justify-between">
          <h2 className="font-serif text-2xl font-bold text-forest-700">Rak Buku Kami</h2>
        </div>

        {rak.isPending ? (
          <div className="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {[0, 1, 2, 3].map((i) => (
              <SkeletonBlock key={i} className="aspect-2/3" />
            ))}
          </div>
        ) : !rak.data || rak.data.data.length === 0 ? (
          <div className="mt-6">
            <EmptyState title="Rak masih kosong" description="Review pertama segera diisi!" />
          </div>
        ) : (
          <div className="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {rak.data.data.map((b) => (
              <Card
                key={b.id}
                className="border-forest-100 transition hover:-translate-y-1 hover:shadow-md"
              >
                <div
                  aria-hidden
                  className="mb-3 grid aspect-2/3 place-items-center rounded-md bg-gradient-to-br from-forest-100 to-forest-50 font-serif text-4xl italic text-forest-700"
                >
                  {b.judul.charAt(0)}
                </div>
                <p className="font-semibold leading-snug">{b.judul}</p>
                <p className="mt-1 line-clamp-2 text-xs text-gray-500">{b.deskripsi ?? '—'}</p>
                <p className="mt-2 font-mono text-[10px] tracking-wide text-gray-400">
                  oleh {b.pembuat?.nama ?? 'Anonim'}
                </p>
                {b.teknologi?.length ? (
                  <div className="mt-2 flex flex-wrap gap-1">
                    {b.teknologi.slice(0, 2).map((t) => (
                      <Badge key={t} tone="neutral">
                        {t}
                      </Badge>
                    ))}
                  </div>
                ) : null}
              </Card>
            ))}
          </div>
        )}
      </section>

      {/* DISKUSI RUTIN + CTA */}
      <section className="mt-16 rounded-xl border border-forest-100 bg-white p-6 text-center">
        <p className="font-serif text-xl font-bold text-forest-700">Diskusi rutin</p>
        <p className="mt-2 text-sm text-gray-600">
          📖 Sabtu malam · 19.30 · satu buku per dua minggu
        </p>
        <p className="mx-auto mt-4 max-w-md text-sm text-gray-500">
          Sibiner khusus anggota HIMSI aktif — hubungi ketua Divisi Organisasi lewat Instagram untuk
          gabung.
        </p>
      </section>
    </main>
  )
}
