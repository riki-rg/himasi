import { type Foto, LightboxGrid } from '@/components/lightbox-grid'
import { ambilPublik } from '@/lib/server-api'
import Link from 'next/link'
import { notFound } from 'next/navigation'

export const revalidate = 60

interface Album {
  id: number
  judul: string
  deskripsi: string | null
  fotos: Foto[]
}

export default async function AlbumPage({
  params,
  searchParams,
}: {
  params: Promise<{ album: string }>
  searchParams: Promise<{ foto?: string }>
}) {
  const [{ album: id }, { foto }] = await Promise.all([params, searchParams])
  const album = await ambilPublik<Album>(`/publik/galeri/albums/${id}`)

  if (!album || album.fotos.length === 0) notFound()

  return (
    <div className="mx-auto max-w-6xl px-4 py-12">
      <Link href="/galeri" className="font-mono text-xs text-gray-400 hover:text-cobalt-600">
        ← SEMUA GALERI
      </Link>

      <header className="mt-4 border-b border-cobalt-200 pb-6">
        <h1 className="text-2xl font-bold md:text-3xl">{album.judul}</h1>
        <p className="mt-2 font-mono text-xs text-gray-500">🖼 {album.fotos.length} foto</p>
        {album.deskripsi ? (
          <p className="mt-3 max-w-2xl text-sm text-gray-600">{album.deskripsi}</p>
        ) : null}
      </header>

      <LightboxGrid
        judul={album.judul}
        fotos={album.fotos}
        fotoAwal={foto ? Number.parseInt(foto, 10) - 1 : undefined}
      />
    </div>
  )
}
