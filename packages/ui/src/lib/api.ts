export interface ProblemJson {
  type?: string
  title?: string
  status: number
  detail?: string
  errors?: Record<string, string[]>
}

export class ApiError extends Error {
  constructor(
    public status: number,
    public type: string | null,
    public title: string,
    message: string,
    public errors?: Record<string, string[]>,
  ) {
    super(message)
    this.name = 'ApiError'
  }

  get problemSlug(): string {
    if (!this.type) return ''
    return this.type.split('/problems/')[1] ?? ''
  }

  static async from(response: Response): Promise<ApiError> {
    let body: ProblemJson | null = null
    try {
      body = (await response.json()) as ProblemJson
    } catch {
      body = null
    }

    return new ApiError(
      response.status,
      body?.type ?? null,
      body?.title ?? (response.statusText || 'Terjadi kesalahan server.'),
      body?.detail ?? body?.title ?? 'Terjadi kesalahan.',
      body?.errors,
    )
  }
}

export interface ApiClientOptions {
  baseUrl: () => string
  getToken?: () => string | null
  onUnauthorized?: () => void
}

export function createApi({ baseUrl, getToken, onUnauthorized }: ApiClientOptions) {
  async function request<T>(path: string, init?: RequestInit): Promise<T> {
    const headers = new Headers(init?.headers)
    const token = getToken?.()
    if (token && !headers.has('Authorization')) {
      headers.set('Authorization', `Bearer ${token}`)
    }
    if (init?.body && !(init.body instanceof FormData) && !headers.has('Content-Type')) {
      headers.set('Content-Type', 'application/json')
    }

    let response: Response
    try {
      response = await fetch(`${baseUrl()}${path}`, { ...init, headers })
    } catch {
      throw new ApiError(
        0,
        null,
        'Koneksi gagal',
        'Tidak bisa menghubungi server. Cek koneksi internetmu.',
      )
    }

    if (!response.ok) {
      const error = await ApiError.from(response)
      if (response.status === 401) onUnauthorized?.()
      throw error
    }

    if (response.status === 204) return undefined as T
    return (await response.json()) as T
  }

  return {
    request,
    get: <T>(path: string) => request<T>(path),
    post: <T>(path: string, body?: unknown) =>
      request<T>(path, {
        method: 'POST',
        body: body instanceof FormData ? body : JSON.stringify(body ?? {}),
      }),
    put: <T>(path: string, body?: unknown) =>
      request<T>(path, {
        method: 'PUT',
        body: body instanceof FormData ? body : JSON.stringify(body ?? {}),
      }),
    patch: <T>(path: string, body?: unknown) =>
      request<T>(path, {
        method: 'PATCH',
        body: body instanceof FormData ? body : JSON.stringify(body ?? {}),
      }),
    delete: <T>(path: string) => request<T>(path, { method: 'DELETE' }),
  }
}

export function formatRupiah(value: number | string): string {
  const num = typeof value === 'string' ? Number.parseFloat(value) : value
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(num)
}

export function formatTanggal(value: string, style: 'long' | 'short' = 'long'): string {
  return new Date(value).toLocaleDateString('id-ID', {
    weekday: style === 'long' ? 'long' : undefined,
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })
}

export function initial(nama: string): string {
  return nama
    .split(' ')
    .map((n) => n.charAt(0))
    .slice(0, 2)
    .join('')
    .toUpperCase()
}
