import { API_URL } from '@/lib/content'
import { ambilPublik } from '@/lib/server-api'
import { EmptyState } from '@himsi/ui'

export const revalidate = 60
export const metadata = { title: 'Galeri' }

interface Album {
  id: number
  judul: string
  deskripsi: string | null
  cover_path: string | null
  jumlah_foto?: number
}

export default async function GaleriIndexPage() {
  const data = await ambilPublik<{ data: Album[] }>('/publik/galeri/albums?per_page=24')

  return (
    <div className="mx-auto max-w-6xl px-4 py-12">
      <h1 className="font-mono text-lg font-bold tracking-widest text-cobalt-600">
        GALERI DOKUMENTASI
      </h1>
      {!data || data.data.length === 0 ? (
        <div className="mt-8">
          <EmptyState title="Album menyusul" />
        </div>
      ) : (
        <div className="mt-6 grid gap-4 sm:grid-cols-2 md:grid-cols-3">
          {data.data.map((al) => (
            <figure
              key={al.id}
              className="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm"
            >
              {al.cover_path ? (
                // eslint-disable-next-line @next/next/no-img-element
                <img
                  src={`${API_URL}/storage/${al.cover_path}`}
                  alt={al.judul}
                  className="aspect-video w-full object-cover transition hover:scale-105"
                />
              ) : (
                <div className="grid aspect-video place-items-center bg-gray-50 text-sm text-gray-400">
                  Tanpa cover
                </div>
              )}
              <figcaption className="p-4">
                <p className="font-semibold">{al.judul}</p>
                <p className="mt-1 font-mono text-xs text-gray-400">{al.jumlah_foto ?? 0} foto</p>
              </figcaption>
            </figure>
          ))}
        </div>
      )}
    </div>
  )
}
