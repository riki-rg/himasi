import { Button, Card, Field, Input } from '@himsi/ui'
import { useQuery } from '@tanstack/react-query'
import { useMutation, useQueryClient } from '@tanstack/react-query'
import { type FormEvent, useEffect, useState } from 'react'
import { type MeResponse, api } from '../lib/api'

export default function ProfilPage() {
  const queryClient = useQueryClient()
  const meQuery = useQuery({
    queryKey: ['me'],
    queryFn: () => api.get<MeResponse>('/auth/me'),
    retry: false,
  })
  const me = meQuery.data
  const [dirty, setDirty] = useState(false)
  const [sukses, setSukses] = useState(false)
  const [errors, setErrors] = useState<Record<string, string[]>>({})
  const [pwSukses, setPwSukses] = useState(false)

  const member = me?.member

  const simpan = useMutation({
    mutationFn: (form: FormData) => api.patch('/auth/me', form),
    onSuccess: async () => {
      setSukses(true)
      setDirty(false)
      await queryClient.invalidateQueries({ queryKey: ['me'] })
    },
    onError: (err) => {
      if (err instanceof Error && 'errors' in err)
        setErrors((err as unknown as ApiErr).errors ?? {})
    },
  })

  function onSubmit(e: FormEvent<HTMLFormElement>) {
    e.preventDefault()
    setErrors({})
    setSukses(false)
    simpan.mutate(new FormData(e.currentTarget))
  }

  useEffect(() => {
    if (!dirty) return
    const handler = (e: BeforeUnloadEvent) => {
      e.preventDefault()
    }
    window.addEventListener('beforeunload', handler)
    return () => window.removeEventListener('beforeunload', handler)
  }, [dirty])

  const gantiPassword = useMutation({
    mutationFn: (body: { password_lama: string; password_baru: string }) =>
      api.put('/auth/password', body),
    onSuccess: () => setPwSukses(true),
  })

  return (
    <div className="space-y-6">
      <h1 className="text-xl font-bold">Profil Saya</h1>

      <form onSubmit={onSubmit} onChange={() => setDirty(true)}>
        <Card>
          <div className="flex items-center gap-4">
            <span className="grid size-16 place-items-center rounded-full bg-cobalt-600 font-mono text-lg font-bold text-white">
              {(member?.nama ?? '?').slice(0, 1)}
            </span>
            <div>
              <p className="font-semibold">{member?.nama}</p>
              <p className="text-xs text-gray-500">
                ⚡ Anggota BITSI · 🎓 {member?.angkatan ?? '-'} · {member?.prodi ?? 'SI'}
              </p>
            </div>
          </div>

          <div className="mt-6 grid gap-4 md:grid-cols-2">
            <Field label="NIM" hint="readonly — diubah admin">
              <Input value={member?.nim ?? ''} readOnly disabled />
            </Field>
            <Field label="Nama" required error={errors.nama}>
              <Input name="nama" defaultValue={me?.name} required />
            </Field>
            <Field label="Email" required error={errors.email}>
              <Input name="email" type="email" defaultValue={me?.email} required />
            </Field>
            <Field label="No. HP" error={errors.no_hp}>
              <Input
                name="no_hp"
                defaultValue={undefined}
                placeholder={member ? undefined : '08…'}
              />
            </Field>
            <Field label="Link Portofolio" error={errors.link_portofolio}>
              <Input name="link_portofolio" type="url" placeholder="https://" />
            </Field>
            <Field label="Instagram" error={errors.link_instagram}>
              <Input name="link_instagram" placeholder="@username" />
            </Field>
            <Field label="Foto Profil" error={errors.foto} hint="≤5MB">
              <input
                name="foto"
                type="file"
                accept="image/*"
                className="text-sm file:mr-3 file:rounded-md file:border-0 file:bg-cobalt-50 file:px-3 file:py-1.5 file:text-cobalt-700"
              />
            </Field>
          </div>

          <p className="mt-4 text-xs text-gray-400">
            Tautan portofolio & IG tampil di org chart publik.
          </p>
        </Card>

        {/* KEAMANAN */}
        <Card className="mt-4">
          <p className="font-semibold text-sm">Ganti Password</p>
          {pwSukses ? <p className="mt-2 text-xs text-green-600">Password diganti ✅</p> : null}
          <form
            className="mt-3 grid gap-3 sm:grid-cols-3"
            onSubmit={(e) => {
              e.preventDefault()
              const f = new FormData(e.currentTarget)
              gantiPassword.mutate({
                password_lama: String(f.get('lama') ?? ''),
                password_baru: String(f.get('baru') ?? ''),
              })
            }}
          >
            <input
              name="lama"
              type="password"
              placeholder="Password lama"
              required
              minLength={8}
              className="rounded-lg border border-gray-300 px-3 py-2 text-sm"
            />
            <input
              name="baru"
              type="password"
              placeholder="Password baru (min 8)"
              required
              minLength={8}
              className="rounded-lg border border-gray-300 px-3 py-2 text-sm"
            />
            <Button variant="outline" type="submit" loading={gantiPassword.isPending}>
              Ganti
            </Button>
          </form>
        </Card>

        {/* SAVE BAR sticky saat dirty */}
        {dirty ? (
          <div className="sticky bottom-20 z-10 mt-4 flex items-center justify-between gap-3 rounded-xl border border-gray-200 bg-white p-3 shadow-lg md:bottom-4">
            <p className="text-sm text-gray-600">
              {sukses ? 'Profil tersimpan ✅' : 'Perubahan belum disimpan'}
            </p>
            <Button type="submit" loading={simpan.isPending}>
              SIMPAN PERUBAHAN
            </Button>
          </div>
        ) : null}
      </form>
    </div>
  )
}

interface ApiErr extends Error {
  errors?: Record<string, string[]>
}
