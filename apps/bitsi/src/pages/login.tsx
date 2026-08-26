import { ApiError, Button } from '@himsi/ui'
import { useMutation, useQueryClient } from '@tanstack/react-query'
import { type FormEvent, useState } from 'react'
import { Link, useNavigate, useSearchParams } from 'react-router'
import { api, simpanToken } from '../lib/api'

interface LoginResponse {
  token: string
  user: { id: number; name: string; email: string }
}

/** Ops-console login — blueprint terminal, corner brackets, scanline. */
export default function LoginPage() {
  const navigate = useNavigate()
  const [params] = useSearchParams()
  const queryClient = useQueryClient()
  const [error, setError] = useState<string | null>(null)
  const [shake, setShake] = useState(0)
  const [lihatPw, setLihatPw] = useState(false)

  const mutation = useMutation({
    mutationFn: (body: { email: string; password: string }) =>
      api.post<LoginResponse>('/auth/login', body),
    onSuccess: async (data) => {
      simpanToken(data.token)
      await queryClient.invalidateQueries({ queryKey: ['me'] })
      navigate(params.get('redirect') ?? '/app', { replace: true })
    },
    onError: (err) => {
      setError(err instanceof ApiError ? err.message : 'Terjadi kesalahan.')
      setShake((s) => s + 1)
    },
  })

  function onSubmit(e: FormEvent<HTMLFormElement>) {
    e.preventDefault()
    setError(null)
    const form = new FormData(e.currentTarget)
    mutation.mutate({
      email: String(form.get('email') ?? ''),
      password: String(form.get('password') ?? ''),
    })
  }

  return (
    <div className="login-bg grid min-h-screen place-items-center px-5">
      <div key={shake} className={shake > 0 ? 'w-full max-w-sm shake' : 'w-full max-w-sm'}>
        <p className="text-center font-mono text-[10px] font-bold tracking-[0.35em] text-cobalt-400">
          AUTH — BITSI OPS
        </p>
        <h1 className="rise mt-3 text-center font-mono text-xl font-bold tracking-widest">
          ▣ BitSI<span className="text-cobalt-400">_</span>
        </h1>

        {/* Corner-bracket card */}
        <div className="relative mt-8 rounded-lg border border-cobalt-500/20 bg-white/60 p-7 backdrop-blur-sm dark:border-cobalt-400/30 dark:bg-white/5">
          <Corner pos="-top-px -left-px border-t-2 border-l-2" />
          <Corner pos="-top-px -right-px border-t-2 border-r-2" />
          <Corner pos="-bottom-px -left-px border-b-2 border-l-2" />
          <Corner pos="-bottom-px -right-px border-b-2 border-r-2" />

          {error ? (
            <div
              role="alert"
              className="mb-5 flex items-start gap-2 rounded-md bg-red-50 px-4 py-3 font-mono text-xs text-red-700 dark:bg-red-950 dark:text-red-300"
            >
              <span>✗</span>
              {error}
            </div>
          ) : null}

          <form onSubmit={onSubmit} className="space-y-5">
            <label className="block">
              <span className="font-mono text-[10px] font-bold uppercase tracking-widest text-gray-500">
                Email
              </span>
              <input
                name="email"
                type="email"
                autoComplete="email"
                spellCheck={false}
                required
                className="mt-1.5 w-full rounded-md border border-cobalt-500/30 bg-transparent px-3 py-2 font-mono text-sm outline-none transition focus:border-cobalt-500 focus:bg-white dark:focus:bg-black/30"
              />
            </label>
            <label className="block">
              <span className="font-mono text-[10px] font-bold uppercase tracking-widest text-gray-500">
                Password
              </span>
              <span className="relative mt-1.5 block">
                <input
                  name="password"
                  type={lihatPw ? 'text' : 'password'}
                  autoComplete="current-password"
                  required
                  className="w-full rounded-md border border-cobalt-500/30 bg-transparent px-3 py-2 pr-10 font-mono text-sm outline-none transition focus:border-cobalt-500 focus:bg-white dark:focus:bg-black/30"
                />
                <button
                  type="button"
                  tabIndex={-1}
                  aria-label={lihatPw ? 'Sembunyikan password' : 'Tampilkan password'}
                  onClick={() => setLihatPw((v) => !v)}
                  className="absolute right-2 top-1/2 -translate-y-1/2 p-1 text-xs text-gray-400 hover:text-cobalt-600"
                >
                  {lihatPw ? '🙈' : '👁'}
                </button>
              </span>
            </label>
            <Button
              type="submit"
              loading={mutation.isPending}
              className="w-full !py-3 font-mono !text-xs font-bold uppercase tracking-[0.25em] transition-all active:scale-[0.98]"
            >
              Masuk Sistem →
            </Button>
          </form>
        </div>

        <p className="mt-6 text-center text-sm text-gray-500">
          Belum punya akun?{' '}
          <Link
            to="/daftar"
            className="font-medium text-cobalt-600 underline-offset-2 hover:underline"
          >
            Daftar dulu
          </Link>
        </p>
      </div>
    </div>
  )
}

function Corner({ pos }: { pos: string }) {
  return (
    <span aria-hidden className={`pointer-events-none absolute size-3 ${pos} border-cobalt-500`} />
  )
}
