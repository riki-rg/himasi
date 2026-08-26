import { BITSI_URL, KONTEN_STATIS, SIBINER_URL } from '@/lib/content'
import { IG_FALLBACK } from '@/lib/ig'
import { ambilPublik } from '@/lib/server-api'
import { Badge, Card, CountUp, EmptyState, Reveal } from '@himsi/ui'
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
      {/* HERO — staggered rise */}
      <section className="blueprint-grid border-b border-gray-200 dark:border-white/10">
        <div className="mx-auto max-w-6xl px-4 py-20 text-center md:py-28">
          <h1 className="rise font-mono text-2xl font-bold leading-snug tracking-wider text-balance md:text-4xl">
            HIMPUNAN MAHASISWA
            <br />
            SISTEM INFORMASI
          </h1>
          <p className="rise rise-1 mt-2 font-mono text-xs tracking-widest text-cobalt-600 dark:text-cobalt-300 md:text-sm">
            {KONTEN_STATIS.universitas}
          </p>
          <p className="rise rise-2 mt-6 text-lg italic text-gray-600 dark:text-gray-300">
            “{KONTEN_STATIS.tagline}”
          </p>

          <div className="rise rise-3 mt-8 flex flex-wrap items-center justify-center gap-3">
            <Link
              href="#komunitas"
              className="rounded-lg bg-cobalt-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:-translate-y-0.5 hover:bg-cobalt-700"
            >
              Lihat Program
            </Link>
            <a
              href={BITSI_URL || IG_FALLBACK}
              target="_blank"
              rel="noreferrer"
              className="rounded-lg border border-cobalt-300 px-5 py-2.5 text-sm font-semibold text-cobalt-700 transition hover:-translate-y-0.5 hover:bg-cobalt-50 dark:border-cobalt-400/40 dark:text-cobalt-300 dark:hover:bg-white/5"
            >
              Gabung Komunitas
            </a>
          </div>
        </div>
      </section>

      {/* PENGUMUMAN PENTING — slide-in, skip render bila tidak ada */}
      {pengumumanPenting ? (
        <output className="rise block bg-amber-50 px-4 py-3 text-center text-sm font-medium text-amber-900 dark:bg-amber-500/15 dark:text-amber-200">
          ⚠ {pengumumanPenting.judul}
        </output>
      ) : null}

      {/* TENTANG + STATISTIK (statis + count-up) */}
      <section className="mx-auto max-w-6xl px-4 py-16">
        <Reveal>
          <h2 className="font-mono text-sm font-bold tracking-widest text-cobalt-600 dark:text-cobalt-300">
            TENTANG KAMI
          </h2>
          <div className="mt-4 grid gap-8 md:grid-cols-2">
            <p className="text-gray-700 dark:text-gray-300">{KONTEN_STATIS.tentang}</p>
            <ul className="space-y-2 text-gray-700 dark:text-gray-300">
              <li>
                ✦ <strong>Visi:</strong> {KONTEN_STATIS.visi}
              </li>
              {KONTEN_STATIS.misi.map((m) => (
                <li key={m}>✦ {m}</li>
              ))}
            </ul>
          </div>
        </Reveal>

        <dl className="mt-10 grid grid-cols-2 gap-4 md:grid-cols-4">
          {KONTEN_STATIS.statistik.map((s, i) => (
            <Reveal key={s.label} delay={i * 90}>
              <Card className="surface text-center">
                <dd className="font-mono text-3xl font-bold text-cobalt-600 dark:text-cobalt-300">
                  <CountUp target={s.nilai} />
                </dd>
                <dt className="mt-1 text-sm text-gray-500 dark:text-gray-400">{s.label}</dt>
              </Card>
            </Reveal>
          ))}
        </dl>
      </section>

      {/* KOMUNITAS */}
      <section
        id="komunitas"
        className="scroll-mt-20 border-y border-gray-200 bg-white py-16 dark:border-white/10 dark:bg-white/[0.02]"
      >
        <div className="mx-auto max-w-6xl px-4">
          <Reveal>
            <h2 className="font-mono text-sm font-bold tracking-widest text-cobalt-600 dark:text-cobalt-300">
              KOMUNITAS KAMI
            </h2>
            <div className="mt-6 grid gap-4 md:grid-cols-2">
              <KomunitasCard
                emoji="⚡"
                nama="BitSI"
                deskripsi="Web Dev · IoT · Jaringan · Server. Ngoprek bareng, belajar bareng."
                href={BITSI_URL || IG_FALLBACK}
              />
              <KomunitasCard
                emoji="📚"
                nama="Sibiner"
                deskripsi="Bicara Nalar & Literasi — bedah buku, perdebatan sehat, rangkuman terbuka."
                href={SIBINER_URL || IG_FALLBACK}
              />
            </div>
          </Reveal>
        </div>
      </section>

      {/* BERITA */}
      <Section title="BERITA TERBARU" href="/artikel" hrefLabel="Semua berita →">
        {!artikels ? null : artikels.data.length === 0 ? null : (
          <div className="grid gap-4 md:grid-cols-3">
            {artikels.data.map((a, i) => (
              <Reveal key={a.id} delay={i * 90}>
                <a href={`/artikel/${a.slug}`} className="group block h-full">
                  <Card className="surface h-full transition group-hover:-translate-y-1 group-hover:shadow-md">
                    <Badge tone="cobalt">{a.kategori ?? 'Umum'}</Badge>
                    <h3 className="mt-3 font-semibold leading-snug group-hover:text-cobalt-700">
                      {a.judul}
                    </h3>
                    {a.published_at ? (
                      <p className="mt-2 font-mono text-xs tabular-nums text-gray-400 dark:text-gray-500">
                        {new Date(a.published_at).toLocaleDateString('id-ID')}
                      </p>
                    ) : null}
                  </Card>
                </a>
              </Reveal>
            ))}
          </div>
        )}
      </Section>

      {/* AGENDA */}
      <Section title="AGENDA MENDATANG" href="/agenda" hrefLabel="Semua agenda →" bg>
        {!events ? (
          <p className="text-sm text-gray-400">Memuat agenda…</p>
        ) : events.data.length === 0 ? (
          <EmptyState title="Belum ada agenda" description="Pantau Instagram kami." />
        ) : (
          <ul className="divide-y divide-gray-100 rounded-xl border border-gray-200 bg-white dark:divide-white/5 dark:border-white/10 dark:bg-white/[0.03]">
            {events.data.map((e) => (
              <li key={e.id} className="flex flex-wrap items-center gap-x-4 gap-y-1 px-5 py-4">
                <span aria-hidden>📅</span>
                <span className="font-mono text-sm tabular-nums text-cobalt-700 dark:text-cobalt-300">
                  {new Date(e.mulai).toLocaleDateString('id-ID', {
                    day: 'numeric',
                    month: 'short',
                  })}
                </span>
                <span className="font-semibold">{e.judul}</span>
                {e.lokasi ? (
                  <span className="text-sm text-gray-500 dark:text-gray-400">@ {e.lokasi}</span>
                ) : null}
              </li>
            ))}
          </ul>
        )}
      </Section>

      {/* GALERI */}
      <Section title="GALERI" href="/galeri" hrefLabel="Semua galeri →">
        {!albums ? null : albums.data.length === 0 ? null : (
          <div className="grid grid-cols-2 gap-3 md:grid-cols-4">
            {albums.data.map((al, i) => (
              <Reveal key={al.id} delay={i * 70}>
                <Link
                  href="/galeri"
                  className="block overflow-hidden rounded-lg border border-gray-200 dark:border-white/10"
                >
                  {al.cover_path ? (
                    // eslint-disable-next-line @next/next/no-img-element
                    <img
                      src={`${process.env.NEXT_PUBLIC_API_URL}/storage/${al.cover_path}`}
                      alt={`Cover album ${al.judul}`}
                      width={640}
                      height={360}
                      loading="lazy"
                      className="aspect-video w-full object-cover transition hover:scale-105"
                    />
                  ) : (
                    <div className="grid aspect-video place-items-center bg-gray-50 text-xs text-gray-400 dark:bg-white/5">
                      {al.judul}
                    </div>
                  )}
                </Link>
              </Reveal>
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
      className="group block rounded-xl border border-gray-200 bg-gradient-to-br from-white to-cobalt-50 p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg dark:border-white/10 dark:from-white/[0.04] dark:to-cobalt-600/10"
    >
      <p className="text-3xl transition group-hover:-rotate-6" aria-hidden>
        {emoji}
      </p>
      <h3 className="mt-3 text-lg font-bold group-hover:text-cobalt-700 dark:group-hover:text-cobalt-300">
        {nama}
      </h3>
      <p className="mt-1 text-sm text-gray-600 dark:text-gray-300">{deskripsi}</p>
      <p className="mt-4 font-mono text-xs font-bold tracking-widest text-cobalt-600 dark:text-cobalt-300">
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
    <section className={bg ? 'border-t border-gray-200 py-14 dark:border-white/10' : 'py-14'}>
      <div className="mx-auto max-w-6xl px-4">
        <div className="flex items-center justify-between">
          <h2 className="font-mono text-sm font-bold tracking-widest text-cobalt-600 dark:text-cobalt-300">
            {title}
          </h2>
          {href && hrefLabel ? (
            <a
              href={href}
              className="text-sm font-medium text-cobalt-600 hover:text-cobalt-700 dark:text-cobalt-300"
            >
              {hrefLabel}
            </a>
          ) : null}
        </div>
        <div className="mt-6">{children}</div>
      </div>
    </section>
  )
}
