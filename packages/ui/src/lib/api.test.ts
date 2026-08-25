import { describe, expect, it, vi } from 'vitest'
import { ApiError, createApi, formatRupiah, initial } from './api'
import { cn } from './cn'

describe('cn()', () => {
  it('menggabungkan class dan menyelesaikan konflik tailwind', () => {
    expect(cn('p-2', 'px-4')).toBe('p-2 px-4')
    expect(cn('text-sm', false && 'hidden', 'font-bold')).toBe('text-sm font-bold')
  })
})

describe('formatRupiah', () => {
  it('memformat angka ke rupiah id-ID', () => {
    expect(formatRupiah(150000)).toContain('150.000')
    expect(formatRupiah('25000.00')).toContain('25.000')
  })
})

describe('initial', () => {
  it('mengambil inisial maksimal dua kata', () => {
    expect(initial('Rizky Maulana')).toBe('RM')
    expect(initial('bima')).toBe('B')
  })
})

describe('ApiError', () => {
  it('parse problem+json RFC 7807 dengan errors', async () => {
    const body = JSON.stringify({
      type: 'https://api/problems/validation',
      title: 'Data tidak valid',
      status: 422,
      detail: 'Beberapa field tidak sesuai.',
      errors: { nim: ['NIM sudah terdaftar.'] },
    })
    const response = new Response(body, { status: 422 })
    const error = await ApiError.from(response)

    expect(error.status).toBe(422)
    expect(error.problemSlug).toBe('validation')
    expect(error.errors?.nim?.[0]).toContain('terdaftar')
  })

  it('fallback pesan ramah saat body bukan json', async () => {
    const error = await ApiError.from(new Response('oops', { status: 500 }))
    expect(error.title).toBe('Terjadi kesalahan server.')
    expect(error.message).toContain('kesalahan')
  })
})

describe('createApi', () => {
  it('menyuntikkan Authorization dari getToken', async () => {
    let received: string | null = null
    const api = createApi({
      baseUrl: () => 'https://api.test/api/v1',
      getToken: () => 'token123',
    })

    const mockFetch = vi.fn(async (_url: string, init?: RequestInit) => {
      received = new Headers(init?.headers).get('Authorization')
      return new Response(JSON.stringify({ ok: true }), { status: 200 })
    })
    vi.stubGlobal('fetch', mockFetch)

    await api.get('/auth/me')
    expect(received).toBe('Bearer token123')
    vi.unstubAllGlobals()
  })

  it('melempar ApiError koneksi saat fetch gagal total', async () => {
    const api = createApi({ baseUrl: () => 'https://api.test' })
    const mockFetch = vi.fn(async () => {
      throw new TypeError('network down')
    })
    vi.stubGlobal('fetch', mockFetch)

    await expect(api.get('/x')).rejects.toThrow('Tidak bisa menghubungi server')
    vi.unstubAllGlobals()
  })
})
