import { ApiError, Button, Field, Input } from '@himsi/ui'
import { useMutation, useQueryClient } from '@tanstack/react-query'
import { type FormEvent, useState } from 'react'
import { useNavigate } from 'react-router'
import { api, simpanToken } from '../lib/api'

export default function LoginPage() {
  const navigate = useNavigate()
  const queryClient = useQueryClient()
  const [error, setError] = useState<string | null>(null)

  const mutation = useMutation({
    mutationFn: (body: { email: string; password: string }) =>
      api.post<{ token: string }>('/auth/login', body),
    onSuccess: async (data) => {
      simpanToken(data.token)
      await queryClient.invalidateQueries({ queryKey: ['me'] })
      navigate('/app', { replace: true })
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
    <main className="grid min-h-[60vh] place-items-center px-5 py-16">
      <div className="w-full max-w-sm rounded-xl border border-forest-100 bg-white p-8">
        <h1 className="text-center font-serif text-2xl font-bold italic text-forest-700">
          Selamat datang kembali
        </h1>
        <p className="mt-2 text-center text-xs text-gray-500">Khusus anggota Sibiner terdaftar</p>

        {error ? (
          <p role="alert" className="mt-5 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">
            {error}
          </p>
        ) : null}

        <form className="mt-6 space-y-4" onSubmit={onSubmit}>
          <Field label="Email" required>
            <Input name="email" type="email" required />
          </Field>
          <Field label="Password" required>
            <Input name="password" type="password" required />
          </Field>
          <Button
            type="submit"
            loading={mutation.isPending}
            className="w-full !bg-forest-700 hover:!bg-forest-600"
          >
            MASUK
          </Button>
        </form>
      </div>
    </main>
  )
}
