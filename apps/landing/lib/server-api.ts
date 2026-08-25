import { API_URL } from './content'

/** Server fetch dengan ISR + graceful failure (ADR L7: API down ≠ halaman blank). */
export async function ambilPublik<T>(path: string): Promise<T | null> {
  if (!API_URL) return null

  try {
    const res = await fetch(`${API_URL}${path}`, { next: { revalidate: 60 } })
    if (!res.ok) return null
    return (await res.json()) as T
  } catch {
    return null
  }
}
