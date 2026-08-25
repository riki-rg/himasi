import { API_URL } from '@/lib/content'
import { ambilPublik } from '@/lib/server-api'
import type { Metadata } from 'next'
import { notFound } from 'next/navigation'

export const revalidate = 60

interface Detail {
  id: number
  judul: string
  slug: string
  konten: string
  kategori: string | null
  published_at: string | null
  author?: { name: string } | null
  cover_path?: string | null
}

async function getArtikel(slug: string): Promise<Detail | null> {
  return ambilPublik<Detail>(`/publik/artikels/${slug}`)
}

export async function generateMetadata({
  params,
}: { params: Promise<{ slug: string }> }): Promise<Metadata> {
  const { slug } = await params
  const artikel = await getArtikel(slug)
  return { title: artikel?.judul ?? 'Artikel tidak ditemukan' }
}

export default async function ArtikelDetailPage({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params
  const artikel = await getArtikel(slug)

  if (!artikel) notFound()

  return (
    <article className="mx-auto max-w-3xl px-4 py-12">
      <p className="font-mono text-xs tracking-widest text-cobalt-600">
        {(artikel.kategori ?? 'UMUM').toUpperCase()} ·{' '}
        {artikel.published_at ? new Date(artikel.published_at).toLocaleDateString('id-ID') : ''}
      </p>
      <h1 className="mt-3 text-2xl font-bold leading-tight md:text-3xl">{artikel.judul}</h1>
      {artikel.author ? (
        <p className="mt-2 text-sm text-gray-500">oleh {artikel.author.name}</p>
      ) : null}
      {artikel.cover_path ? (
        // eslint-disable-next-line @next/next/no-img-element
        <img
          src={`${API_URL}/storage/${artikel.cover_path}`}
          alt={artikel.judul}
          className="mt-6 w-full rounded-xl border border-gray-200"
        />
      ) : null}
      <div className="prose prose-gray mt-8 whitespace-pre-wrap leading-relaxed">
        {artikel.konten}
      </div>
      <a
        href="/artikel"
        className="mt-10 inline-block font-mono text-sm text-cobalt-600 hover:text-cobalt-700"
      >
        ← Semua berita
      </a>
    </article>
  )
}
