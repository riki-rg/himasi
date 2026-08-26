import { Badge, Card, EmptyState, Reveal, SkeletonBlock } from '@himsi/ui'
import { useQuery } from '@tanstack/react-query'
import { Link } from 'react-router'
import { api } from '../lib/api'

interface Proyek {
  id: number
  judul: string
  slug: string
  deskripsi: string | null
  teknologi: string[] | null
  pembuat?: { nama: string } | null
}
interface KelasItem {
  id: number
  nama: string
  jadwal_hari: string | null
  jadwal_jam: string | null
}
interface Pengurus {
  nim: string
  nama: string
  foto_path: string | null
}

const BIDANG = [
  {
    icon: '🌐',
    label: 'Web Dev',
    desc: 'HTML/CSS/JS · React · Laravel — bikin website dari nol sampai production.',
  },
  {
    icon: '📡',
    label: 'IoT',
    desc: 'Arduino · ESP32 · sensor & aktuator — ngubungin dunia fisik sama kode.',
  },
  {
    icon: '🔗',
    label: 'Jaringan',
    desc: 'Mikrotik · subnetting · routing — pahami cara internet bekerja.',
  },
  {
    icon: '🖥',
    label: 'Server',
    desc: 'Linux · Docker · VPS — deploy dan maintain infrastruktur sendiri.',
  },
]

const TESTIMONI = [
  {
    text: 'Dulu gak bisa apa-apa, sekarang udah deploy 3 proyek ke VPS.',
    oleh: 'Bima P. — Anggota 2023',
  },
  {
    text: 'BitSI itu tempat pertama gua belajar coding secara serius.',
    oleh: 'Citra D. — Anggota 2022',
  },
  {
    text: 'Komunitasnya suportif banget, gak ada yang dianggap bodoh.',
    oleh: 'Dimas E. — Anggota 2024',
  },
]

export default function HomePage() {
  const karya = useQuery({
    queryKey: ['home-karya'],
    queryFn: () => api.get<{ data: Proyek[] }>('/publik/proyeks?komunitas=BITSI&per_page=6'),
  })

  const kelas = useQuery({
    queryKey: ['home-kelas'],
    queryFn: () => api.get<KelasItem[]>('/publik/kelass?komunitas=BITSI'),
  })

  const pengurus = useQuery({
    queryKey: ['home-pengurus'],
    queryFn: () =>
      api.get<{ divisi: { nama: string }; jabatan: { nama: string; pengurus: Pengurus[] }[] }[]>(
        '/publik/struktur?komunitas=BITSI',
      ),
  })

  return (
    <div className="min-h-screen">
      {/* NAVBAR */}
      <header className="flex items-center justify-between px-5 py-4">
        <p className="font-mono font-bold tracking-widest">▣ BitSI</p>
        <Link to="/login">
          <button
            type="button"
            className="rounded-md border border-cobalt-500/30 px-4 py-1.5 font-mono text-xs font-bold uppercase tracking-wider transition hover:border-cobalt-500 hover:bg-cobalt-50"
          >
            Masuk
          </button>
        </Link>
      </header>

      {/* HERO */}
      <section className="blueprint-grid relative border-y border-gray-200 px-5 py-24 md:py-32">
        <div className="mx-auto max-w-3xl text-center">
          <p className="rise font-mono text-[10px] font-bold uppercase tracking-[0.35em] text-cobalt-500">
            Bit Of Sistem Informasi
          </p>
          <h1 className="rise rise-1 mt-5 text-balance text-3xl font-bold leading-tight md:text-5xl">
            Ngoprek bareng,
            <br />
            belajar bareng.
          </h1>
          <p className="rise rise-2 mx-auto mt-5 max-w-lg text-gray-600">
            Komunitas tech HIMSI UMKU untuk yang suka web development, IoT, jaringan, dan server.
            Dari nol sampai production.
          </p>

          <div className="rise rise-3 mt-8 flex flex-wrap items-center justify-center gap-2">
            {BIDANG.map((b) => (
              <Badge key={b.label} tone="cobalt">
                {b.label}
              </Badge>
            ))}
          </div>

          <div className="rise rise-4 mt-10 flex flex-wrap justify-center gap-3">
            <Link
              to="/daftar"
              className="rounded-lg bg-cobalt-600 px-7 py-3 text-sm font-bold text-white transition hover:-translate-y-0.5 hover:bg-cobalt-700 active:scale-[0.98]"
            >
              GABUNG BITSI →
            </Link>
            <a
              href="#karya"
              className="rounded-lg border border-cobalt-300 px-7 py-3 text-sm font-semibold text-cobalt-700 transition hover:-translate-y-0.5 hover:bg-cobalt-50"
            >
              Lihat Karya
            </a>
          </div>
        </div>
      </section>

      {/* BIDANG */}
      <section className="mx-auto max-w-5xl px-5 py-16" aria-label="Bidang">
        <Reveal>
          <p className="font-mono text-[10px] font-bold uppercase tracking-[0.3em] text-cobalt-500">
            4 Bidang Keahlian
          </p>
          <div className="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {BIDANG.map((b) => (
              <Card key={b.label} className="transition hover:-translate-y-1 hover:shadow-md">
                <p className="text-2xl" aria-hidden>
                  {b.icon}
                </p>
                <p className="mt-3 font-semibold">{b.label}</p>
                <p className="mt-1 text-xs leading-relaxed text-gray-500">{b.desc}</p>
              </Card>
            ))}
          </div>
        </Reveal>
      </section>

      {/* SHOWCASE KARYA */}
      <section
        id="karya"
        className="border-y border-gray-200 bg-white py-16 scroll-mt-16"
        aria-label="Showcase karya"
      >
        <div className="mx-auto max-w-5xl px-5">
          <Reveal>
            <p className="font-mono text-[10px] font-bold uppercase tracking-[0.3em] text-cobalt-500">
              Karya Anggota
            </p>
            {!karya.isPending && (!karya.data || karya.data.data.length === 0) ? (
              <EmptyState title="Belum ada karya" description="Jadi yang pertama!" />
            ) : (
              <div className="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                {karya.isPending
                  ? [0, 1, 2].map((i) => <SkeletonBlock key={i} className="h-36" />)
                  : karya.data?.data.map((p) => (
                      <Card
                        key={p.id}
                        className="group transition hover:-translate-y-1 hover:shadow-md"
                      >
                        <p className="font-semibold">{p.judul}</p>
                        <p className="mt-1 line-clamp-2 text-xs text-gray-500">{p.deskripsi}</p>
                        <span className="mt-3 flex flex-wrap gap-1">
                          {(p.teknologi ?? []).slice(0, 3).map((t) => (
                            <Badge key={t}>{t}</Badge>
                          ))}
                        </span>
                        <p className="mt-2 font-mono text-[10px] text-gray-400">
                          oleh {p.pembuat?.nama}
                        </p>
                      </Card>
                    ))}
              </div>
            )}
          </Reveal>
        </div>
      </section>

      {/* KELAS RUTIN */}
      <section className="mx-auto max-w-5xl px-5 py-16" aria-label="Kelas rutin">
        <Reveal>
          <p className="font-mono text-[10px] font-bold uppercase tracking-[0.3em] text-cobalt-500">
            Kelas Rutin
          </p>
          {kelas.isPending ? (
            <div className="mt-6 grid gap-3 sm:grid-cols-3">
              {[0, 1, 2].map((i) => (
                <SkeletonBlock key={i} className="h-20" />
              ))}
            </div>
          ) : !kelas.data || kelas.data.length === 0 ? (
            <EmptyState title="Belum ada kelas dibuka" />
          ) : (
            <div className="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
              {kelas.data.map((k) => (
                <Card key={k.id}>
                  <p className="font-semibold">{k.nama}</p>
                  <p className="mt-1 font-mono text-xs tabular-nums text-gray-400">
                    📅 {k.jadwal_hari ?? '-'} {k.jadwal_jam ?? ''}
                  </p>
                </Card>
              ))}
            </div>
          )}
        </Reveal>
      </section>

      {/* PENGURUS */}
      <section className="mx-auto max-w-5xl px-5 py-16" aria-label="Pengurus">
        <Reveal>
          <p className="font-mono text-[10px] font-bold uppercase tracking-[0.3em] text-cobalt-500">
            Tim Kami
          </p>
          {pengurus.isPending ? (
            <div className="mt-6 flex flex-wrap gap-3">
              {[0, 1, 2].map((i) => (
                <SkeletonBlock key={i} className="h-14 w-40" />
              ))}
            </div>
          ) : !pengurus.data || pengurus.data.length === 0 ? null : (
            <div className="mt-6 space-y-4">
              {pengurus.data.map((node) => (
                <div key={node.divisi.nama}>
                  {(node.jabatan ?? []).flatMap((j) =>
                    (j.pengurus ?? []).map((p) => (
                      <span
                        key={p.nim}
                        className="mr-3 inline-flex items-center gap-2 rounded-lg border border-gray-100 px-3 py-2"
                      >
                        <span className="grid size-8 place-items-center rounded-full bg-cobalt-600 font-mono text-xs text-white">
                          {p.nama.charAt(0)}
                        </span>
                        <span>
                          <span className="block text-sm font-medium">{p.nama}</span>
                          <span className="block font-mono text-[10px] text-gray-400">
                            {j.nama}
                          </span>
                        </span>
                      </span>
                    )),
                  )}
                </div>
              ))}
            </div>
          )}
        </Reveal>
      </section>

      {/* TESTIMONI */}
      <section className="border-t border-gray-200 bg-white py-16" aria-label="Testimoni">
        <div className="mx-auto max-w-4xl px-5">
          <Reveal>
            <p className="text-center font-mono text-[10px] font-bold uppercase tracking-[0.3em] text-gray-400">
              Kata Mereka
            </p>
            <div className="mt-8 grid gap-4 md:grid-cols-3">
              {TESTIMONI.map((t) => (
                <Card key={t.oleh} className="text-center">
                  <p className="font-serif text-2xl text-cobalt-200" aria-hidden>
                    ❝
                  </p>
                  <p className="mt-1 text-sm italic text-gray-600">{t.text}</p>
                  <p className="mt-3 font-mono text-[10px] tracking-wide text-gray-400">{t.oleh}</p>
                </Card>
              ))}
            </div>
          </Reveal>
        </div>
      </section>

      {/* CTA */}
      <section className="blueprint-grid py-20 text-center">
        <Reveal>
          <h2 className="text-2xl font-bold md:text-3xl">Mau ikutan?</h2>
          <p className="mx-auto mt-3 max-w-sm text-sm text-gray-500">
            Daftarkan dirimu sebagai anggota BitSI — review ketua ≤3 hari.
          </p>
          <Link
            to="/daftar"
            className="mt-8 inline-block rounded-lg bg-cobalt-600 px-8 py-3.5 font-mono text-xs font-bold uppercase tracking-[0.25em] text-white transition hover:-translate-y-0.5 hover:bg-cobalt-700 active:scale-[0.98]"
          >
            Daftar Sekarang →
          </Link>
        </Reveal>
      </section>

      <footer className="border-t border-gray-200 px-5 py-6 text-center font-mono text-xs text-gray-400">
        © BitSI · Divisi Pengembangan Diri · HIMSI UMKU
      </footer>
    </div>
  )
}
