import { ApiError, Badge, Button, Card, EmptyState, Field, Input } from '@himsi/ui'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { useState } from 'react'
import { Link, useParams } from 'react-router'
import { api } from '../lib/api'

interface Kelas {
  id: number
  nama: string
  jadwal_hari: string | null
  jadwal_jam: string | null
  jumlah_materi?: number
}
interface Materi {
  id: number
  judul: string
  tipe: 'file' | 'link'
  file_path: string | null
  link_url: string | null
  urutan: number
}

export default function KelolaKelasPage() {
  const queryClient = useQueryClient()
  const [formBuka, setFormBuka] = useState(false)
  const [errors, setErrors] = useState<Record<string, string[]>>({})

  const daftar = useQuery({
    queryKey: ['kelola-kelas'],
    queryFn: () => api.get<Kelas[]>('/publik/kelass?komunitas=BITSI'),
  })

  const buatKelas = useMutation({
    mutationFn: (body: Record<string, unknown>) => api.post('/kelass', body),
    onSuccess: () => {
      setFormBuka(false)
      setErrors({})
      void queryClient.invalidateQueries({ queryKey: ['kelola-kelas'] })
    },
    onError: (err) => {
      if (err instanceof ApiError && err.status === 422) setErrors(err.errors ?? {})
    },
  })

  return (
    <div>
      <div className="flex items-center justify-between">
        <h1 className="text-xl font-bold">Kelas Belajar</h1>
        <Button onClick={() => setFormBuka(true)}>+ Kelas Baru</Button>
      </div>

      {formBuka ? (
        <Card className="mt-4">
          <form
            className="grid gap-3 sm:grid-cols-2"
            onSubmit={(e) => {
              e.preventDefault()
              const f = new FormData(e.currentTarget)
              buatKelas.mutate(Object.fromEntries(f.entries()))
            }}
          >
            <Field label="Nama Kelas" required error={errors.nama}>
              <Input name="nama" required />
            </Field>
            <Field label="Jadwal Hari" hint="cth: Sabtu">
              <Input name="jadwal_hari" />
            </Field>
            <Field label="Jadwal Jam" hint="cth: 16.00">
              <Input name="jadwal_jam" />
            </Field>
            <Field label="Tempat">
              <Input name="tempat" placeholder="Lab SI / Daring" />
            </Field>
            <div className="sm:col-span-2 flex justify-end gap-2">
              <Button type="button" variant="ghost" onClick={() => setFormBuka(false)}>
                Batal
              </Button>
              <Button type="submit" loading={buatKelas.isPending}>
                Buat
              </Button>
            </div>
          </form>
        </Card>
      ) : null}

      {daftar.isPending ? (
        <p className="mt-6 text-sm text-gray-400">Memuat…</p>
      ) : !daftar.data || daftar.data.length === 0 ? (
        <div className="mt-8">
          <EmptyState title="Belum ada kelas" />
        </div>
      ) : (
        <ul className="mt-5 space-y-2">
          {daftar.data.map((k) => (
            <li key={k.id}>
              <Link to={`/app/pengurus/kelas/${k.id}`} className="block">
                <Card className="flex flex-wrap items-center justify-between gap-2 transition hover:-translate-y-0.5 hover:shadow-md">
                  <span>
                    <span className="block font-semibold">{k.nama}</span>
                    <span className="block font-mono text-xs text-gray-400">
                      {k.jadwal_hari ?? '-'} {k.jadwal_jam ?? ''}
                    </span>
                  </span>
                  <Badge tone="cobalt">Kelola →</Badge>
                </Card>
              </Link>
            </li>
          ))}
        </ul>
      )}
    </div>
  )
}

export function KelolaMateriPage() {
  const { id } = useParams()
  const queryClient = useQueryClient()
  const [errors, setErrors] = useState<Record<string, string[]>>({})

  const kelas = useQuery({
    queryKey: ['kelola-kelas', id],
    queryFn: () => api.get<Kelas & { materis: Materi[] }>(`/kelass/${id}`),
    retry: false,
  })

  function refresh() {
    void queryClient.invalidateQueries({ queryKey: ['kelola-kelas', id] })
  }

  const tambah = useMutation({
    mutationFn: (form: FormData) => api.post(`/kelass/${id}/materis`, form),
    onSuccess: () => {
      setErrors({})
      refresh()
    },
    onError: (err) => {
      if (err instanceof ApiError && err.status === 422) setErrors(err.errors ?? {})
    },
  })

  const ubah = useMutation({
    mutationFn: ({ materiId, body }: { materiId: number; body: Record<string, unknown> }) =>
      api.put(`/materis/${materiId}`, body),
    onSuccess: refresh,
  })

  const hapus = useMutation({
    mutationFn: (materiId: number) => api.delete(`/materis/${materiId}`),
    onSuccess: refresh,
  })

  if (kelas.isPending) return <p className="text-sm text-gray-400">Memuat…</p>
  if (kelas.isError || !kelas.data) {
    return (
      <EmptyState title="Tidak punya akses" description="Hanya pengurus/pengajar kelas terkait." />
    )
  }

  const k = kelas.data
  const BASE = (import.meta.env.VITE_API_URL ?? '').replace(/\/api\/v1$/, '')

  return (
    <div>
      <Link
        to="/app/pengurus/kelas"
        className="font-mono text-xs text-gray-400 hover:text-cobalt-600"
      >
        ← KELOLA KELAS
      </Link>
      <h1 className="mt-2 text-xl font-bold">{k.nama}</h1>

      {/* TAMBAH MATERI */}
      <Card className="mt-4">
        <p className="text-sm font-semibold">+ Tambah Materi</p>
        <form
          className="mt-3 grid gap-3 sm:grid-cols-[1fr_auto_auto]"
          onSubmit={(e) => {
            e.preventDefault()
            tambah.mutate(new FormData(e.currentTarget))
            ;(e.target as HTMLFormElement).reset()
          }}
        >
          <Input name="judul" placeholder="Judul materi" required />
          <select
            name="tipe"
            defaultValue="file"
            className="rounded-lg border border-gray-300 px-3 py-2 text-sm"
          >
            <option value="file">File ≤10MB</option>
            <option value="link">Link eksternal</option>
          </select>
          <div className="flex gap-2 sm:col-span-3">
            <input
              name="file"
              type="file"
              className="text-sm file:mr-3 file:rounded-md file:border-0 file:bg-cobalt-50 file:px-3 file:py-1.5 file:text-cobalt-700"
            />
            <Input
              name="link_url"
              type="url"
              placeholder="https:// (bila link)"
              className="flex-1"
            />
            <Button type="submit" loading={tambah.isPending}>
              Tambah
            </Button>
          </div>
        </form>
        {Object.values(errors)
          .flat()
          .map((e) => (
            <p key={e} role="alert" className="mt-2 text-xs text-red-600">
              {e}
            </p>
          ))}
      </Card>

      {/* DAFTAR MATERI — urutan panah + edit inline + hapus */}
      {!k.materis || k.materis.length === 0 ? (
        <div className="mt-6">
          <EmptyState title="Belum ada materi" />
        </div>
      ) : (
        <ol className="mt-4 divide-y divide-gray-100 rounded-xl border border-gray-200 bg-white">
          {k.materis.map((m) => (
            <li key={m.id} className="flex flex-wrap items-center gap-2 px-4 py-3">
              <span className="font-mono text-xs text-gray-400">
                {String(m.urutan).padStart(2, '0')}
              </span>

              <InlineJudul
                materi={m}
                onSimpan={(judul) => ubah.mutate({ materiId: m.id, body: { judul } })}
              />

              <span className="ml-auto flex items-center gap-1">
                <button
                  type="button"
                  aria-label="Naikkan urutan"
                  disabled={m.urutan <= 1}
                  onClick={() => ubah.mutate({ materiId: m.id, body: { urutan: m.urutan - 1 } })}
                  className="rounded border border-gray-200 px-2 py-1 text-xs disabled:opacity-30"
                >
                  ⬆
                </button>
                <button
                  type="button"
                  aria-label="Turunkan urutan"
                  disabled={m.urutan >= k.materis.length}
                  onClick={() => ubah.mutate({ materiId: m.id, body: { urutan: m.urutan + 1 } })}
                  className="rounded border border-gray-200 px-2 py-1 text-xs disabled:opacity-30"
                >
                  ⬇
                </button>
                {m.tipe === 'file' && m.file_path ? (
                  <a
                    href={`${BASE}/storage/${m.file_path}`}
                    target="_blank"
                    rel="noreferrer"
                    className="rounded bg-cobalt-50 px-2 py-1 text-xs font-bold text-cobalt-700"
                  >
                    ⬇
                  </a>
                ) : m.link_url ? (
                  <a
                    href={m.link_url}
                    target="_blank"
                    rel="noreferrer"
                    className="rounded bg-gray-50 px-2 py-1 text-xs font-bold text-gray-600"
                  >
                    🔗
                  </a>
                ) : null}
                <button
                  type="button"
                  aria-label={`Hapus ${m.judul}`}
                  onClick={() => {
                    if (confirm(`Hapus materi "${m.judul}"?`)) hapus.mutate(m.id)
                  }}
                  className="rounded border border-red-200 px-2 py-1 text-xs text-red-600"
                >
                  🗑
                </button>
              </span>
            </li>
          ))}
        </ol>
      )}
    </div>
  )
}

function InlineJudul({ materi, onSimpan }: { materi: Materi; onSimpan: (judul: string) => void }) {
  const [nilai, setNilai] = useState(materi.judul)

  return (
    <input
      value={nilai}
      onChange={(e) => setNilai(e.target.value)}
      onBlur={() => {
        if (nilai.trim() && nilai !== materi.judul) onSimpan(nilai.trim())
      }}
      onKeyDown={(e) => {
        if (e.key === 'Enter') (e.target as HTMLInputElement).blur()
      }}
      aria-label={`Edit judul ${materi.judul}`}
      className="min-w-0 flex-1 rounded border border-transparent px-1 py-0.5 text-sm hover:border-gray-200 focus:border-cobalt-400 focus:outline-none"
    />
  )
}
