import { ambilPublik } from '@/lib/server-api'
import { Badge } from '@himsi/ui'
import Link from 'next/link'

export const revalidate = 60

interface Artikel {
  id: number
  judul: string
  slug: string
  kategori: string | null
  published_at: string | null
}

export const metadata = { title: 'Berita' }

export default async function ArtikelPage() {
  const data = await ambilPublik<{ data: Artikel[] }>('/publik/artikels?per_page=12')

  return (
    <div className="mx-auto max-w-6xl px-4 py-12">
      <h1 className="font-mono text-lg font-bold tracking-widest text-cobalt-600">
        BERITA & PUBLIKASI
      </h1>
      {!data ? (
        <p className="mt-8 text-sm text-gray-500">
          Berita sedang tidak tersedia — coba muat ulang.
        </p>
      ) : data.data.length === 0 ? (
        <p className="mt-8 text-sm text-gray-500">Belum ada artikel.</p>
      ) : (
        <div className="mt-6 grid gap-4 md:grid-cols-3">
          {data.data.map((a) => (
            <Link key={a.id} href={`/artikel/${a.slug}`} className="group">
              <article className="h-full rounded-xl border border-gray-200 bg-white p-5 transition group-hover:-translate-y-0.5 group-hover:shadow-md">
                <Badge tone="cobalt">{a.kategori ?? 'Umum'}</Badge>
                <h2 className="mt-3 font-semibold leading-snug group-hover:text-cobalt-700">
                  {a.judul}
                </h2>
                {a.published_at ? (
                  <time className="mt-2 block font-mono text-xs text-gray-400">
                    {new Date(a.published_at).toLocaleDateString('id-ID')}
                  </time>
                ) : null}
              </article>
            </Link>
          ))}
        </div>
      )}
    </div>
  )
}
