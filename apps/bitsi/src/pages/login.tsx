import { ApiError, Button, Field, Input } from '@himsi/ui'
import { useMutation, useQueryClient } from '@tanstack/react-query'
import { type FormEvent, useState } from 'react'
import { Link, useNavigate, useSearchParams } from 'react-router'
import { api, simpanToken } from '../lib/api'

interface LoginResponse {
  token: string
  user: { id: number; name: string; email: string }
}

export default function LoginPage() {
  const navigate = useNavigate()
  const [params] = useSearchParams()
  const queryClient = useQueryClient()
  const [error, setError] = useState<string | null>(null)

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
    <div className="blueprint-grid grid min-h-screen place-items-center px-5">
      <div className="w-full max-w-sm">
        <p className="text-center font-mono text-xs font-bold tracking-[0.25em] text-cobalt-600">
          SELAMAT DATANG KEMBALI
        </p>
        <h1 className="mt-2 text-center font-mono text-xl font-bold tracking-widest">▣ BitSI</h1>

        {mutation.isPending ? null : error ? (
          <p role="alert" className="mt-6 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">
            {error}
          </p>
        ) : null}

        <form className="mt-6 space-y-4" onSubmit={onSubmit}>
          <Field label="Email" required>
            <Input name="email" type="email" required autoComplete="email" />
          </Field>
          <Field label="Password" required>
            <Input name="password" type="password" required autoComplete="current-password" />
          </Field>
          <Button type="submit" loading={mutation.isPending} className="w-full">
            MASUK →
          </Button>
        </form>

        <p className="mt-6 text-center text-sm text-gray-500">
          Belum punya akun?{' '}
          <Link to="/daftar" className="font-medium text-cobalt-600 hover:text-cobalt-700">
            Daftar dulu
          </Link>
        </p>
      </div>
    </div>
  )
}
