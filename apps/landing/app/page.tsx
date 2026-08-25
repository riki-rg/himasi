import { BITSI_URL, KONTEN_STATIS, SIBINER_URL } from '@/lib/content'
import { ambilPublik } from '@/lib/server-api'
import { Badge, Card, EmptyState, SkeletonBlock } from '@himsi/ui'
import Link from 'next/link'

export const revalidate = 60

interface Pengumuman {
  id: number
  judul: string
  prioritas: string
}
interface Artikel {
  id: number
  judul: string
  slug: string
  cover_path: string | null
  kategori: string | null
  published_at: string | null
  author?: { name: string } | null
}
interface Event {
  id: number
  judul: string
  lokasi: string | null
  mulai: string
}
interface Album {
  id: number
  judul: string
  cover_path: string | null
  jumlah_foto?: number
}

export default async function HomePage() {
  const [pengumumans, artikels, events, albums] = await Promise.all([
    ambilPublik<Pengumuman[]>('/publik/pengumumans'),
    ambilPublik<{ data: Artikel[] }>('/publik/artikels?per_page=3'),
    ambilPublik<{ data: Event[] }>('/publik/events?mendatang=true&per_page=3'),
    ambilPublik<{ data: Album[] }>('/publik/galeri/albums?per_page=4'),
  ])

  const pengumumanPenting = pengumumans?.find((p) => p.prioritas === 'penting')

  return (
    <div>
      {/* HERO */}
      <section className="blueprint-grid border-b border-gray-200">
        <div className="mx-auto max-w-6xl px-4 py-20 text-center md:py-28">
          <h1 className="font-mono text-2xl font-bold leading-snug tracking-wider md:text-4xl">
            HIMPUNAN MAHASISWA
            <br />
            SISTEM INFORMASI
          </h1>
          <p className="mt-2 font-mono text-xs tracking-widest text-cobalt-600 md:text-sm">
            {KONTEN_STATIS.universitas}
          </p>
          <p className="mt-6 text-lg italic text-gray-600">"{KONTEN_STATIS.tagline}"</p>

          <div className="mt-8 flex flex-wrap items-center justify-center gap-3">
            <Link
              href="#komunitas"
              className="rounded-lg bg-cobalt-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-cobalt-700"
            >
              Lihat Program
            </Link>
            <a
              href={BITSI_URL || '#'}
              target={BITSI_URL ? '_blank' : undefined}
              rel="noreferrer"
              className="rounded-lg border border-cobalt-300 px-5 py-2.5 text-sm font-semibold text-cobalt-700 transition hover:bg-cobalt-50"
            >
              Gabung Komunitas
            </a>
          </div>
        </div>
      </section>

      {/* PENGUMUMAN PENTING — skip render bila tidak ada (state matrix) */}
      {pengumumanPenting ? (
        <div className="bg-amber-50 px-4 py-3 text-center text-sm font-medium text-amber-900">
          ⚠ {pengumumanPenting.judul}
        </div>
      ) : null}

      {/* TENTANG + STATISTIK (konten statis — jarang berubah) */}
      <section className="mx-auto max-w-6xl px-4 py-16">
        <h2 className="font-mono text-sm font-bold tracking-widest text-cobalt-600">
          TENTANG KAMI
        </h2>
        <div className="mt-4 grid gap-8 md:grid-cols-2">
          <p className="text-gray-700">{KONTEN_STATIS.tentang}</p>
          <ul className="space-y-2 text-gray-700">
            <li>
              ✦ <strong>Visi:</strong> {KONTEN_STATIS.visi}
            </li>
            {KONTEN_STATIS.misi.map((m) => (
              <li key={m}>✦ {m}</li>
            ))}
          </ul>
        </div>

        <dl className="mt-10 grid grid-cols-2 gap-4 md:grid-cols-4">
          {KONTEN_STATIS.statistik.map((s) => (
            <Card key={s.label} className="text-center">
              <dd className="font-mono text-3xl font-bold text-cobalt-600">{s.nilai}</dd>
              <dt className="mt-1 text-sm text-gray-500">{s.label}</dt>
            </Card>
          ))}
        </dl>
      </section>

      {/* KOMUNITAS */}
      <section id="komunitas" className="border-y border-gray-200 bg-white py-16">
        <div className="mx-auto max-w-6xl px-4">
          <h2 className="font-mono text-sm font-bold tracking-widest text-cobalt-600">
            KOMUNITAS KAMI
          </h2>
          <div className="mt-6 grid gap-4 md:grid-cols-2">
            <KomunitasCard
              emoji="⚡"
              nama="BitSI"
              deskripsi="Bit Of Sistem Informasi — Web Dev · IoT · Jaringan · Server. Ngoprek bareng, belajar bareng."
              href={BITSI_URL || '#'}
            />
            <KomunitasCard
              emoji="📚"
              nama="Sibiner"
              deskripsi="SI Bicara Nalar & Literasi — bedah buku, perdebatan sehat, rangkuman terbuka."
              href={SIBINER_URL || '#'}
            />
          </div>
        </div>
      </section>

      {/* BERITA TERBARU */}
      <Section title="BERITA TERBARU" href="/artikel" hrefLabel="Semua berita →">
        {!artikels ? null : artikels.data.length === 0 ? null : (
          <div className="grid gap-4 md:grid-cols-3">
            {artikels.data.map((a) => (
              <a key={a.id} href={`/artikel/${a.slug}`} className="group">
                <Card className="h-full transition group-hover:-translate-y-0.5 group-hover:shadow-md">
                  <Badge tone="cobalt">{a.kategori ?? 'Umum'}</Badge>
                  <h3 className="mt-3 font-semibold leading-snug group-hover:text-cobalt-700">
                    {a.judul}
                  </h3>
                  {a.published_at ? (
                    <p className="mt-2 font-mono text-xs text-gray-400">
                      {new Date(a.published_at).toLocaleDateString('id-ID')}
                    </p>
                  ) : null}
                </Card>
              </a>
            ))}
          </div>
        )}
      </Section>

      {/* AGENDA MENDATANG */}
      <Section title="AGENDA MENDATANG" href="/agenda" hrefLabel="Semua agenda →" bg>
        {!events ? (
          <SkeletonBlock className="h-16 w-full max-w-xl" />
        ) : events.data.length === 0 ? (
          <EmptyState
            title="Belum ada agenda"
            description="Pantau Instagram kami untuk info kegiatan."
          />
        ) : (
          <ul className="divide-y divide-gray-100 rounded-xl border border-gray-200 bg-white">
            {events.data.map((e) => (
              <li key={e.id} className="flex flex-wrap items-center gap-x-4 gap-y-1 px-5 py-4">
                <span aria-hidden>📅</span>
                <span className="font-mono text-sm text-cobalt-700">
                  {new Date(e.mulai).toLocaleDateString('id-ID', {
                    day: 'numeric',
                    month: 'short',
                  })}
                </span>
                <span className="font-semibold">{e.judul}</span>
                {e.lokasi ? <span className="text-sm text-gray-500">@ {e.lokasi}</span> : null}
              </li>
            ))}
          </ul>
        )}
      </Section>

      {/* GALERI PILIHAN */}
      <Section title="GALERI" href="/galeri" hrefLabel="Semua galeri →">
        {!albums ? null : albums.data.length === 0 ? null : (
          <div className="grid grid-cols-2 gap-3 md:grid-cols-4">
            {albums.data.map((al) => (
              <a
                key={al.id}
                href="/galeri"
                className="overflow-hidden rounded-lg border border-gray-200"
              >
                {al.cover_path ? (
                  // eslint-disable-next-line @next/next/no-img-element
                  <img
                    src={`${process.env.NEXT_PUBLIC_API_URL}/storage/${al.cover_path}`}
                    alt={al.judul}
                    className="aspect-video w-full object-cover transition hover:scale-105"
                  />
                ) : (
                  <div className="grid aspect-video place-items-center bg-gray-50 text-xs text-gray-400">
                    {al.judul}
                  </div>
                )}
              </a>
            ))}
          </div>
        )}
      </Section>
    </div>
  )
}

function KomunitasCard({
  emoji,
  nama,
  deskripsi,
  href,
}: { emoji: string; nama: string; deskripsi: string; href: string }) {
  return (
    <a
      href={href}
      target="_blank"
      rel="noreferrer"
      className="group block rounded-xl border border-gray-200 bg-gradient-to-br from-white to-cobalt-50 p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg"
    >
      <p className="text-3xl" aria-hidden>
        {emoji}
      </p>
      <h3 className="mt-3 text-lg font-bold group-hover:text-cobalt-700">{nama}</h3>
      <p className="mt-1 text-sm text-gray-600">{deskripsi}</p>
      <p className="mt-4 font-mono text-xs font-bold tracking-widest text-cobalt-600">
        MASUK APP →
      </p>
    </a>
  )
}

function Section({
  title,
  href,
  hrefLabel,
  bg,
  children,
}: {
  title: string
  href?: string
  hrefLabel?: string
  bg?: boolean
  children: React.ReactNode
}) {
  return (
    <section className={bg ? 'border-t border-gray-200 py-14' : 'py-14'}>
      <div className="mx-auto max-w-6xl px-4">
        <div className="flex items-center justify-between">
          <h2 className="font-mono text-sm font-bold tracking-widest text-cobalt-600">{title}</h2>
          {href && hrefLabel ? (
            <a href={href} className="text-sm font-medium text-cobalt-600 hover:text-cobalt-700">
              {hrefLabel}
            </a>
          ) : null}
        </div>
        <div className="mt-6">{children}</div>
      </div>
    </section>
  )
}
