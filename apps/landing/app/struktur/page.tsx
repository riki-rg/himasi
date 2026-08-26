import { API_URL } from '@/lib/content'
import { ambilPublik } from '@/lib/server-api'
import { Avatar, EmptyState } from '@himsi/ui'

export const revalidate = 60
export const metadata = { title: 'Struktur Organisasi' }

interface Periode {
  id: number
  nama: string
  status: string
}
interface Pengurus {
  nim: string
  nama: string
  foto_path: string | null
  link_instagram: string | null
}
interface JabatanNode {
  id: number
  nama: string
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

const TABS = ['', 'HIMSI', 'BITSI', 'SIBINER'] as const

export default async function StrukturPage({
  searchParams,
}: {
  searchParams: Promise<{ periode?: string; komunitas?: string }>
}) {
  const sp = await searchParams
  const qs = new URLSearchParams()
  if (sp.periode) qs.set('periode', sp.periode)
  if (sp.komunitas) qs.set('komunitas', sp.komunitas)

  const [periodes, data] = await Promise.all([
    ambilPublik<Periode[]>('/periodes'),
    ambilPublik<DivisiNode[]>(`/publik/struktur?${qs.toString()}`),
  ])

  const periodeAktif = periodes?.find((p) => p.status === 'aktif')
  const periodeDipilih = periodes?.find((p) => String(p.id) === sp.periode)
  const arsipDipilih = periodeDipilih?.status === 'arsip'

  return (
    <div className="mx-auto max-w-5xl px-4 py-12">
      <h1 className="font-mono text-lg font-bold tracking-widest text-cobalt-600">
        STRUKTUR ORGANISASI
      </h1>

      {/* PERIODE SELECTOR + badge arsip */}
      {periodes && periodes.length > 1 ? (
        <form className="mt-4 inline-flex items-center gap-2" action="/struktur" method="get">
          {sp.komunitas ? <input type="hidden" name="komunitas" value={sp.komunitas} /> : null}
          <label htmlFor="periode" className="text-sm text-gray-500">
            Periode:
          </label>
          <select
            id="periode"
            name="periode"
            defaultValue={sp.periode ?? String(periodeAktif?.id ?? '')}
            className="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm"
          >
            {periodes.map((p) => (
              <option key={p.id} value={p.id}>
                {p.nama}
              </option>
            ))}
          </select>
          <button
            type="submit"
            className="rounded-lg bg-cobalt-600 px-3 py-1.5 text-xs font-semibold text-white"
          >
            Lihat
          </button>
        </form>
      ) : null}
      {arsipDipilih && periodeDipilih ? (
        <p className="mt-3">
          <span className="rounded bg-amber-50 px-2.5 py-1 font-mono text-[11px] font-bold tracking-wide text-amber-700">
            ARSIP · {periodeDipilih.nama}
          </span>
        </p>
      ) : null}

      {/* TAB KOMUNITAS */}
      <nav aria-label="Filter komunitas" className="mt-5 flex flex-wrap gap-2">
        {TABS.map((kode) => {
          const aktif = (sp.komunitas ?? '') === kode
          const href = `/struktur?${new URLSearchParams({ ...(sp.periode ? { periode: sp.periode } : {}), ...(kode ? { komunitas: kode } : {}) }).toString()}`
          return (
            <a
              key={kode || 'semua'}
              href={href}
              className={`rounded-full px-4 py-1.5 text-sm font-medium transition ${
                aktif
                  ? 'bg-cobalt-600 text-white'
                  : 'border border-gray-300 text-gray-600 hover:bg-gray-50'
              }`}
            >
              {kode || 'Semua'}
            </a>
          )
        })}
      </nav>

      {!data || data.length === 0 ? (
        <div className="mt-8">
          <EmptyState title="Struktur segera diperbarui" />
        </div>
      ) : (
        <div className="mt-8 space-y-8">
          {data.map((node) => (
            <section
              key={node.divisi.id}
              id={node.divisi.nama.toLowerCase().replace(/[^a-z]+/g, '-')}
              className="rounded-xl border border-gray-200 bg-white p-6 scroll-mt-24"
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
                {node.jabatan.flatMap((j) => {
                  const kartu = j.pengurus.map((p) => (
                    <div
                      key={`${node.divisi.id}-${j.id}-${p.nim}`}
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
                  ))
                  return kartu.length > 0
                    ? kartu
                    : [
                        <div
                          key={`${node.divisi.id}-${j.id}-kosong`}
                          className="flex items-center rounded-lg border border-dashed border-gray-200 p-3"
                        >
                          <p className="text-xs text-gray-400">
                            {j.nama} — jabatan kosong, akan diisi
                          </p>
                        </div>,
                      ]
                })}
              </div>
            </section>
          ))}
        </div>
      )}
    </div>
  )
}
