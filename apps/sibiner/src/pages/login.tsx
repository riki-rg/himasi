import { ApiError, Button } from '@himsi/ui'
import { useMutation, useQueryClient } from '@tanstack/react-query'
import { type FormEvent, useState } from 'react'
import { useLocation, useNavigate } from 'react-router'
import { api, simpanToken } from '../lib/api'

/** Warm Library login — serif italic, paper card, forest button. */
export default function LoginPage() {
  const navigate = useNavigate()
  const location = useLocation()
  const queryClient = useQueryClient()
  const [error, setError] = useState<string | null>(null)

  const mutation = useMutation({
    mutationFn: (body: { email: string; password: string }) =>
      api.post<{ token: string }>('/auth/login', body),
    onSuccess: async (data) => {
      simpanToken(data.token)
      await queryClient.invalidateQueries({ queryKey: ['me'] })
      navigate((location.state as { dari?: string } | null)?.dari ?? '/app', { replace: true })
    },
    onError: (err) => {
      if (err instanceof ApiError && err.problemSlug === 'account-pending') {
        setError('Akunmu masih menunggu persetujuan admin.')
      } else {
        setError(err instanceof ApiError ? err.message : 'Terjadi kesalahan.')
      }
    },
  })

  function onSubmit(e: FormEvent<HTMLFormElement>) {
    e.preventDefault()
    const form = new FormData(e.currentTarget)
    mutation.mutate({
      email: String(form.get('email') ?? ''),
      password: String(form.get('password') ?? ''),
    })
  }

  return (
    <main className="grid min-h-screen place-items-center px-5 py-16">
      <div className="w-full max-w-sm">
        {/* Ornamen atas */}
        <p className="text-center font-serif text-3xl text-forest-600/30" aria-hidden>
          ❦
        </p>
        <h1 className="rise mt-3 text-center font-serif text-3xl font-bold italic text-forest-700 text-balance">
          Selamat datang
          <br />
          kembali
        </h1>
        <p className="mt-3 text-center text-xs tracking-wide text-gray-400">
          Khusus anggota Sibiner terdaftar
        </p>

        {/* Paper card */}
        <div className="rise rise-2 mt-8 rounded-xl border border-forest-100 bg-white p-8 shadow-[0_4px_24px_rgba(30,58,47,0.08)]">
          {error ? (
            <p role="alert" className="mb-5 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">
              {error}
            </p>
          ) : null}

          <form onSubmit={onSubmit} className="space-y-5">
            <label className="block">
              <span className="font-serif text-sm italic text-gray-600">Email</span>
              <input
                name="email"
                type="email"
                autoComplete="email"
                spellCheck={false}
                required
                className="mt-1 w-full border-b border-forest-100 bg-transparent px-1 py-2 text-sm outline-none transition focus:border-forest-600"
              />
            </label>
            <label className="block">
              <span className="font-serif text-sm italic text-gray-600">Password</span>
              <input
                name="password"
                type="password"
                autoComplete="current-password"
                required
                className="mt-1 w-full border-b border-forest-100 bg-transparent px-1 py-2 text-sm outline-none transition focus:border-forest-600"
              />
            </label>
            <Button
              type="submit"
              loading={mutation.isPending}
              className="mt-2 w-full !bg-forest-700 !py-3 font-serif !text-sm font-semibold hover:!bg-forest-600 active:!scale-[0.98]"
            >
              Masuk
            </Button>
          </form>
        </div>

        {/* Quote bawah */}
        <p className="rise rise-4 mt-10 text-center font-serif text-sm italic leading-relaxed text-gray-400">
          “Buku adalah jendela dunia,
          <br />
          diskusi adalah kuncinya.”
        </p>
      </div>
    </main>
  )
}
