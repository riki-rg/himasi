import { API_URL } from '@/lib/content'
import { ambilPublik } from '@/lib/server-api'
import { Avatar, EmptyState } from '@himsi/ui'

export const revalidate = 60
export const metadata = { title: 'Struktur Organisasi' }

interface Pengurus {
  nim: string
  nama: string
  foto_path: string | null
  link_instagram: string | null
}
interface JabatanNode {
  id: number
  nama: string
  tingkat: string
  pengurus: Pengurus[]
}
interface DivisiNode {
  divisi: {
    id: number
    nama: string
    komunitas: { kode: string; nama: string } | null
    urutan: number
  }
  jabatan: JabatanNode[]
}

export default async function StrukturPage() {
  const data = await ambilPublik<DivisiNode[]>('/publik/struktur')

  return (
    <div className="mx-auto max-w-5xl px-4 py-12">
      <h1 className="font-mono text-lg font-bold tracking-widest text-cobalt-600">
        STRUKTUR ORGANISASI
      </h1>
      {!data || data.length === 0 ? (
        <div className="mt-8">
          <EmptyState title="Struktur segera diperbarui" />
        </div>
      ) : (
        <div className="mt-6 space-y-8">
          {data.map((node) => (
            <section
              key={node.divisi.id}
              className="rounded-xl border border-gray-200 bg-white p-6"
            >
              <h2 className="font-mono text-sm font-bold tracking-wider">
                {node.divisi.nama.toUpperCase()}
                {node.divisi.komunitas ? (
                  <span className="ml-2 rounded bg-cobalt-50 px-2 py-0.5 text-[10px] font-semibold tracking-normal text-cobalt-700">
                    mengelola {node.divisi.komunitas.kode}
                  </span>
                ) : null}
              </h2>

              <div className="mt-4 flex flex-wrap gap-4">
                {node.jabatan.flatMap((j) =>
                  j.pengurus.length === 0
                    ? []
                    : j.pengurus.map((p) => (
                        <div
                          key={`${j.id}-${p.nim}`}
                          className="flex items-center gap-3 rounded-lg border border-gray-100 p-3"
                        >
                          <Avatar
                            nama={p.nama}
                            src={p.foto_path ? `${API_URL}/storage/${p.foto_path}` : null}
                          />
                          <div>
                            <p className="text-sm font-semibold">{p.nama}</p>
                            <p className="text-xs text-gray-500">{j.nama}</p>
                            {p.link_instagram ? (
                              <a
                                href={p.link_instagram}
                                target="_blank"
                                rel="noreferrer"
                                className="text-xs text-cobalt-600 hover:text-cobalt-700"
                              >
                                Instagram →
                              </a>
                            ) : null}
                          </div>
                        </div>
                      )),
                )}
              </div>
            </section>
          ))}
        </div>
      )}
    </div>
  )
}
