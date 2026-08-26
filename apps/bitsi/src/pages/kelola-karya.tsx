import { ApiError, Badge, Button, Card, EmptyState, Field, Input } from '@himsi/ui'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { type FormEvent, useState } from 'react'
import { api } from '../lib/api'

interface Proyek {
  id: number
  judul: string
  slug: string
  deskripsi: string | null
  thumbnail_path: string | null
  link_demo: string | null
  link_repo: string | null
  teknologi: string[] | null
  pembuat?: { id: number; nim: string; nama: string } | null
  status: 'draft' | 'published'
}

export default function KelolaKaryaPage() {
  const queryClient = useQueryClient()
  const [filterStatus, setFilterStatus] = useState('')
  const [cari, setCari] = useState('')
  const [formBuka, setFormBuka] = useState(false)
  const [editData, setEditData] = useState<Proyek | null>(null)
  const [errors, setErrors] = useState<Record<string, string[]>>({})
  const [teknologi, setTeknologi] = useState<string[]>([])
  const [teknologiInput, setTeknologiInput] = useState('')

  const daftar = useQuery({
    queryKey: ['kelola-karya', filterStatus, cari],
    queryFn: () => {
      const q = new URLSearchParams()
      if (filterStatus) q.set('status', filterStatus)
      if (cari) q.set('q', cari)
      return api.get<{ data: Proyek[] }>(`/proyeks?${q.toString()}`)
    },
  })

  const anggota = useQuery({
    queryKey: ['anggota-pilihan'],
    queryFn: () =>
      api.get<{ data: { id: number; nama: string; nim: string }[] }>('/anggota?per_page=100'),
    enabled: formBuka,
  })

  function refresh() {
    void queryClient.invalidateQueries({ queryKey: ['kelola-karya'] })
    void queryClient.invalidateQueries({ queryKey: ['rak-buku'] })
  }

  const simpan = useMutation({
    mutationFn: ({ id, form }: { id?: number; form: FormData }) =>
      id ? api.put(`/proyeks/${id}`, form) : api.post('/proyeks', form),
    onSuccess: () => {
      setFormBuka(false)
      setEditData(null)
      setErrors({})
      refresh()
    },
    onError: (err) => {
      if (err instanceof ApiError && err.status === 422) setErrors(err.errors ?? {})
    },
  })

  const hapus = useMutation({
    mutationFn: (id: number) => api.delete(`/proyeks/${id}`),
    onSuccess: refresh,
  })

  const togglePublish = useMutation({
    mutationFn: ({ id, status }: { id: number; status: 'draft' | 'published' }) =>
      api.put(`/proyeks/${id}`, { status }),
    onSuccess: refresh,
  })

  function submitForm(e: FormEvent<HTMLFormElement>) {
    e.preventDefault()
    const fd = new FormData(e.currentTarget)
    fd.delete('teknologi_chip')
    fd.set('teknologi', JSON.stringify(teknologi))
    if (!fd.get('thumbnail')) fd.delete('thumbnail')
    simpan.mutate({ id: editData?.id, form: fd })
  }

  return (
    <div>
      <div className="flex flex-wrap items-center justify-between gap-3">
        <h1 className="text-xl font-bold">Karya Anggota</h1>
        <Button
          onClick={() => {
            setEditData(null)
            setTeknologi([])
            setFormBuka(true)
          }}
        >
          + Tambah
        </Button>
      </div>

      <div className="mt-4 flex flex-wrap gap-2">
        <Input
          placeholder="Cari…"
          value={cari}
          onChange={(e) => setCari(e.target.value)}
          className="!w-48"
        />
        {['', 'published', 'draft'].map((s) => (
          <button
            key={s || 'semua'}
            type="button"
            onClick={() => setFilterStatus(s)}
            className={`rounded-full px-4 py-1.5 text-sm font-medium ${filterStatus === s ? 'bg-cobalt-600 text-white' : 'border border-gray-300 text-gray-600'}`}
          >
            {s || 'Semua'}
          </button>
        ))}
      </div>

      {/* LIST */}
      {daftar.isPending ? (
        <p className="mt-6 text-sm text-gray-400">Memuat…</p>
      ) : !daftar.data || daftar.data.data.length === 0 ? (
        <div className="mt-8">
          <EmptyState title="Belum ada karya" description="Tambah yang pertama!" />
        </div>
      ) : (
        <ul className="mt-5 space-y-3">
          {daftar.data.data.map((p) => (
            <li key={p.id}>
              <Card>
                <div className="flex flex-wrap items-center justify-between gap-3">
                  <span className="flex min-w-0 items-center gap-3">
                    <span
                      aria-hidden
                      className="grid size-12 shrink-0 place-items-center rounded-md bg-gradient-to-br from-cobalt-100 to-white text-lg"
                    >
                      🖼
                    </span>
                    <span className="min-w-0">
                      <span className="block truncate font-semibold">{p.judul}</span>
                      <span className="block text-xs text-gray-500">
                        oleh {p.pembuat?.nama ?? '-'}
                        {p.teknologi?.length ? ` · ${p.teknologi.join(', ')}` : ''}
                      </span>
                    </span>
                  </span>
                  <Badge tone={p.status === 'published' ? 'green' : 'amber'}>{p.status}</Badge>
                </div>
                <div className="mt-3 flex flex-wrap gap-2">
                  <Button
                    variant="outline"
                    className="!px-3 !py-1.5 text-xs"
                    onClick={() => {
                      setEditData(p)
                      setTeknologi(p.teknologi ?? [])
                      setFormBuka(true)
                    }}
                  >
                    Edit
                  </Button>
                  <Button
                    variant="ghost"
                    className="!px-3 !py-1.5 text-xs"
                    onClick={() =>
                      togglePublish.mutate({
                        id: p.id,
                        status: p.status === 'draft' ? 'published' : 'draft',
                      })
                    }
                  >
                    {p.status === 'draft' ? 'Publish' : 'Unpublish'}
                  </Button>
                  <Button
                    variant="danger"
                    className="!px-3 !py-1.5 text-xs"
                    onClick={() => {
                      if (confirm(`Hapus karya "${p.judul}"?`)) hapus.mutate(p.id)
                    }}
                  >
                    Hapus
                  </Button>
                </div>
              </Card>
            </li>
          ))}
        </ul>
      )}

      {/* FORM DIALOG */}
      {formBuka ? (
        <div className="fixed inset-0 z-50 overflow-y-auto bg-black/40 p-4 md:p-8">
          <Card className="mx-auto max-w-xl bg-white">
            <div className="flex items-center justify-between">
              <p className="font-bold">{editData ? `Edit: ${editData.judul}` : 'Karya Baru'}</p>
              <button
                type="button"
                onClick={() => setFormBuka(false)}
                aria-label="Tutup"
                className="text-xl"
              >
                ✕
              </button>
            </div>

            <form className="mt-4 space-y-4" onSubmit={submitForm}>
              <Field label="Judul Proyek" required error={errors.judul}>
                <Input name="judul" required defaultValue={editData?.judul} />
              </Field>
              <Field label="Deskripsi" error={errors.deskripsi}>
                <textarea
                  name="deskripsi"
                  rows={3}
                  defaultValue={editData?.deskripsi ?? ''}
                  className="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-cobalt-500"
                />
              </Field>
              <Field label="Thumbnail ≤5MB" error={errors.thumbnail}>
                <input
                  name="thumbnail"
                  type="file"
                  accept="image/*"
                  className="text-sm file:mr-3 file:rounded-md file:border-0 file:bg-cobalt-50 file:px-3 file:py-1.5 file:text-cobalt-700"
                />
              </Field>
              <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <Field label="Link Demo" error={errors.link_demo}>
                  <Input
                    name="link_demo"
                    type="url"
                    defaultValue={editData?.link_demo ?? ''}
                    placeholder="https://"
                  />
                </Field>
                <Field label="Link Repo" error={errors.link_repo}>
                  <Input
                    name="link_repo"
                    type="url"
                    defaultValue={editData?.link_repo ?? ''}
                    placeholder="https://github.com/"
                  />
                </Field>
              </div>
              <Field label="Teknologi" hint="Enter untuk menambah chip">
                <Input
                  name="teknologi_chip"
                  value={teknologiInput}
                  onChange={(e) => setTeknologiInput(e.target.value)}
                  onKeyDown={(e) => {
                    if (e.key === 'Enter') {
                      e.preventDefault()
                      const v = teknologiInput.trim()
                      if (v && !teknologi.includes(v)) setTeknologi((t) => [...t, v])
                      setTeknologiInput('')
                    }
                  }}
                />
                <span className="mt-2 flex flex-wrap gap-1.5">
                  {teknologi.map((t) => (
                    <button
                      key={t}
                      type="button"
                      onClick={() => setTeknologi((arr) => arr.filter((x) => x !== t))}
                      className="rounded-full bg-cobalt-50 px-2.5 py-0.5 text-xs font-medium text-cobalt-700"
                    >
                      {t} ×
                    </button>
                  ))}
                </span>
              </Field>
              <Field label="Pembuat" required error={errors.pembuat_id}>
                <select
                  name="pembuat_id"
                  required
                  defaultValue={editData?.pembuat?.id ?? ''}
                  className="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm"
                >
                  <option value="" disabled>
                    cari anggota…
                  </option>
                  {(anggota.data?.data ?? []).map((a) => (
                    <option key={a.id} value={a.id}>
                      {a.nama} — {a.nim}
                    </option>
                  ))}
                </select>
              </Field>
              <fieldset className="text-sm">
                <legend className="font-medium text-gray-800">Status</legend>
                <label className="mr-4">
                  <input
                    type="radio"
                    name="status"
                    value="draft"
                    defaultChecked={!editData || editData.status === 'draft'}
                  />{' '}
                  Draft
                </label>
                <label>
                  <input
                    type="radio"
                    name="status"
                    value="published"
                    defaultChecked={editData?.status === 'published'}
                  />{' '}
                  Published
                </label>
              </fieldset>

              <div className="flex justify-end gap-2 pt-2">
                <Button type="button" variant="ghost" onClick={() => setFormBuka(false)}>
                  Batal
                </Button>
                <Button type="submit" loading={simpan.isPending}>
                  Simpan
                </Button>
              </div>
            </form>
          </Card>
        </div>
      ) : null}
    </div>
  )
}
