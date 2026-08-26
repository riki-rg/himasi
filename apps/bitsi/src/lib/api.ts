import { createApi } from '@himsi/ui'

const TOKEN_KEY = 'bitsi_token'

export const api = createApi({
  baseUrl: () => import.meta.env.VITE_API_URL ?? '',
  getToken: () => localStorage.getItem(TOKEN_KEY),
  onUnauthorized: () => {
    localStorage.removeItem(TOKEN_KEY)
  },
})

export function simpanToken(token: string): void {
  localStorage.setItem(TOKEN_KEY, token)
}

export function hapusToken(): void {
  localStorage.removeItem(TOKEN_KEY)
}

export function punyaToken(): boolean {
  return localStorage.getItem(TOKEN_KEY) !== null
}

export interface MeResponse {
  id: number
  name: string
  email: string
  member: {
    nim: string
    nama: string
    angkatan: number
    prodi?: string | null
    foto_path?: string | null
  } | null
  komunitas: { kode: string; nama: string }[]
  penugasan_aktif: {
    jabatan: string
    tingkat: string
    divisi: string
    komunitas_kode: string | null
    periode: string
  }[]
}
