import { ApiError, Button, Field, Input } from '@himsi/ui'
import { useMutation } from '@tanstack/react-query'
import { type FormEvent, useState } from 'react'
import { Link, useNavigate } from 'react-router'
import { api } from '../lib/api'

interface RegisterResponse {
  id: number
  nama: string
}

const LANGKAH = ['Daftar', 'Review', 'Aktif']

export default function DaftarPage() {
  const navigate = useNavigate()
  const [sukses, setSukses] = useState(false)
  const [errors, setErrors] = useState<Record<string, string[]>>({})
  const [networkGagal, setNetworkGagal] = useState(false)

  const mutation = useMutation({
    mutationFn: (form: FormData) =>
      api.post<RegisterResponse>(
        '/auth/register?komunitas=BITSI',
        Object.fromEntries([...Array.from(form.entries()), ['komunitas', 'BITSI']]),
      ),
    onSuccess: () => setSukses(true),
    onError: (err) => {
      if (err instanceof ApiError && err.status === 422) {
        setErrors(err.errors ?? {})
      } else if (err instanceof ApiError && err.status === 0) {
        setNetworkGagal(true)
      }
    },
  })

  function onSubmit(e: FormEvent<HTMLFormElement>) {
    e.preventDefault()
    setErrors({})
    setNetworkGagal(false)
    mutation.mutate(new FormData(e.currentTarget))
  }

  return (
    <div className="min-h-screen">
      <header className="flex items-center justify-between px-5 py-4">
        <Link to="/" className="font-mono font-bold tracking-widest">
          ▣ BitSI
        </Link>
        <Link to="/login" className="text-sm font-medium text-cobalt-600 hover:text-cobalt-700">
          Sudah punya akun? Masuk →
        </Link>
      </header>

      {sukses ? (
        <section className="blueprint-grid mx-auto max-w-md px-5 py-24 text-center">
          <p className="text-5xl" aria-hidden>
            ⏳
          </p>
          <h1 className="mt-6 text-xl font-bold">Pendaftaran terkirim!</h1>
          <p className="mt-3 text-gray-600">
            Ketua BitSI akan mereview pendaftaranmu (biasanya ≤ 3 hari). Coba login nanti untuk cek
            status.
          </p>
          <div
            className="mt-8 flex justify-center gap-2"
            aria-label={`Langkah: ${LANGKAH.join(' → ')}`}
          >
            {LANGKAH.map((l, i) => (
              <span
                key={l}
                className={
                  i <= 1
                    ? 'size-2.5 rounded-full bg-cobalt-600'
                    : 'size-2.5 rounded-full bg-gray-300'
                }
              />
            ))}
          </div>
          <Button variant="outline" className="mt-10" onClick={() => navigate('/')}>
            Kembali ke Beranda
          </Button>
        </section>
      ) : (
        <main className="mx-auto grid max-w-4xl gap-10 px-5 py-10 md:grid-cols-[1.2fr_1fr]">
          <section>
            <h1 className="font-mono text-sm font-bold tracking-[0.25em] text-cobalt-700">
              GABUNG BITSI
            </h1>
            <p className="mt-1 text-sm text-gray-500">Satu form. Tiga langkah.</p>

            {networkGagal ? (
              <div className="mt-4 rounded-lg bg-cobalt-50 px-4 py-3 text-sm text-cobalt-800">
                Koneksi bermasalah — data form tetap aman.
              </div>
            ) : null}

            <form className="mt-6 space-y-4" onSubmit={onSubmit} noValidate>
              <Field label="NIM" required error={errors.nim}>
                <Input name="nim" required inputMode="numeric" placeholder="2101050001" />
              </Field>
              <Field label="Nama Lengkap" required error={errors.nama}>
                <Input name="nama" required />
              </Field>
              <div className="grid grid-cols-2 gap-3">
                <Field label="Email UMKU" required error={errors.email}>
                  <Input name="email" type="email" required />
                </Field>
                <Field label="Angkatan" required error={errors.angkatan}>
                  <Input
                    name="angkatan"
                    type="number"
                    min={2015}
                    max={2030}
                    defaultValue={new Date().getFullYear()}
                    required
                  />
                </Field>
              </div>
              <div className="grid grid-cols-2 gap-3">
                <Field label="Password" required error={errors.password} hint="min. 8 karakter">
                  <Input name="password" type="password" minLength={8} required />
                </Field>
                <Field label="No. HP" error={errors.no_hp}>
                  <Input name="no_hp" inputMode="tel" placeholder="08…" />
                </Field>
              </div>
              <Field label="Prodi" error={errors.prodi}>
                <select
                  name="prodi"
                  className="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm outline-none focus:border-cobalt-500"
                >
                  <option>Sistem Informasi</option>
                </select>
              </Field>
              <div className="grid grid-cols-2 gap-3">
                <Field label="Link Portofolio" error={errors.link_portofolio}>
                  <Input name="link_portofolio" type="url" placeholder="https://" />
                </Field>
                <Field label="Instagram" error={errors.link_instagram}>
                  <Input name="link_instagram" placeholder="@username" />
                </Field>
              </div>

              <label className="flex items-start gap-2 text-sm text-gray-700">
                <input
                  type="checkbox"
                  name="setuju"
                  required
                  className="mt-0.5 accent-cobalt-600"
                />
                Saya anggota HIMSI aktif & setuju ketentuan komunitas
              </label>

              <Button type="submit" loading={mutation.isPending} className="w-full">
                DAFTAR SEKARANG →
              </Button>
            </form>
          </section>

          <aside className="hidden md:block">
            <h2 className="font-semibold text-gray-800">Kenapa gabung?</h2>
            <ol className="mt-4 space-y-3 text-sm text-gray-600">
              <li>
                <strong>01</strong> · Belajar 4 bidang tech
              </li>
              <li>
                <strong>02</strong> · Praktik langsung proyek
              </li>
              <li>
                <strong>03</strong> · Komunitas ngoprek bareng
              </li>
            </ol>
            <hr className="my-6 border-dashed border-gray-300" />
            <p className="text-xs text-gray-500">
              ⏳ Ketua review ≤3 hari · ✅ Notif disetujui · 🚀 Login & mulai
            </p>
          </aside>
        </main>
      )}
    </div>
  )
}
