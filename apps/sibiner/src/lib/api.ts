import { createApi } from '@himsi/ui'

const TOKEN_KEY = 'sibiner_token'

export const api = createApi({
  baseUrl: () => import.meta.env.VITE_API_URL ?? '',
  getToken: () => localStorage.getItem(TOKEN_KEY),
  onUnauthorized: () => localStorage.removeItem(TOKEN_KEY),
})

export function simpanToken(token: string): void {
  localStorage.setItem(TOKEN_KEY, token)
}

export function hapusToken(): void {
  localStorage.removeItem(TOKEN_KEY)
}

export interface MeResponse {
  id: number
  name: string
  member: { nim: string; nama: string; angkatan: number } | null
}
