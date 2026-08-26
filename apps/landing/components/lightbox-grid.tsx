'use client'

import { cn } from '@himsi/ui'
import { useRouter } from 'next/navigation'
import { useCallback, useEffect, useState } from 'react'

export interface Foto {
  id: number
  path: string
  caption: string | null
  urutan: number
}

/**
 * Masonry grid + lightbox deep-linkable `?foto=N` (galeri-album.md).
 * Keyboard ← → ESC, swipe mobile, preload tetangga ±1.
 */
export function LightboxGrid({
  judul,
  fotos,
  fotoAwal,
}: { judul: string; fotos: Foto[]; fotoAwal?: number }) {
  const router = useRouter()
  const [aktif, setAktif] = useState<number | null>(
    fotoAwal !== undefined && fotoAwal >= 0 && fotoAwal < fotos.length ? fotoAwal : null,
  )

  const buka = useCallback(
    (index: number) => {
      setAktif(index)
      router.replace(`?foto=${index + 1}`, { scroll: false })
      document.body.style.overflow = 'hidden'
    },
    [router],
  )

  const tutup = useCallback(() => {
    setAktif(null)
    router.replace('?', { scroll: false })
    document.body.style.overflow = ''
  }, [router])

  const geser = useCallback(
    (delta: number) => {
      setAktif((cur) => {
        if (cur === null) return cur
        const next = (cur + delta + fotos.length) % fotos.length
        router.replace(`?foto=${next + 1}`, { scroll: false })
        return next
      })
    },
    [fotos.length, router],
  )

  useEffect(() => {
    if (aktif === null) return

    function onKey(e: KeyboardEvent) {
      if (e.key === 'Escape') tutup()
      if (e.key === 'ArrowRight') geser(1)
      if (e.key === 'ArrowLeft') geser(-1)
    }
    window.addEventListener('keydown', onKey)
    return () => window.removeEventListener('keydown', onKey)
  }, [aktif, geser, tutup])

  useEffect(() => {
    const berikut: Foto | undefined = aktif !== null ? fotos[aktif + 1] : undefined
    const sebelum: Foto | undefined = aktif !== null ? fotos[aktif - 1] : undefined
    if (berikut) void preload(berikut)
    if (sebelum) void preload(sebelum)
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [aktif])

  function preload(f: Foto): Promise<void> {
    return new Promise((resolve) => {
      const img = new Image()
      img.onload = () => resolve()
      img.onerror = () => resolve()
      img.src = src(f)
    })
  }

  const current = aktif !== null ? fotos[aktif] : null

  return (
    <>
      {/* MASONRY — tinggi alami via columns */}
      <div className="mt-8 gap-3 [column-fill:_balance] sm:columns-2 lg:columns-4">
        {fotos.map((f, i) => (
          <button
            key={f.id}
            type="button"
            onClick={() => buka(i)}
            className="mb-3 block w-full break-inside-avoid overflow-hidden rounded-lg border border-gray-200 bg-white transition hover:shadow-md"
          >
            {/* eslint-disable-next-line @next/next/no-img-element */}
            <img
              src={src(f)}
              alt={f.caption ?? `${judul} — foto ${i + 1}`}
              loading="lazy"
              className="w-full transition hover:scale-[1.02]"
            />
          </button>
        ))}
      </div>

      {/* LIGHTBOX */}
      {current ? (
        <div
          role="dialog"
          aria-modal
          aria-label={current.caption ?? judul}
          className="fixed inset-0 z-50 flex flex-col bg-black/90 text-white"
        >
          <button
            type="button"
            aria-label="Tutup lightbox"
            className="flex-1 cursor-default"
            onClick={tutup}
          />
          <div className="flex items-center justify-between p-4">
            <span className="font-mono text-xs">
              {(aktif ?? 0) + 1} / {fotos.length}
            </span>
            <button
              type="button"
              onClick={tutup}
              aria-label="Tutup"
              className="text-2xl leading-none"
            >
              ✕
            </button>
          </div>

          <div className="relative -mt-[calc(3rem+1px)] flex min-h-0 flex-1 items-center justify-center px-12">
            <button
              type="button"
              aria-label="Sebelumnya"
              onClick={() => geser(-1)}
              className="absolute left-2 rounded-full bg-white/10 p-3 text-xl hover:bg-white/20"
            >
              ‹
            </button>
            {/* eslint-disable-next-line @next/next/no-img-element */}
            <img
              key={current.id}
              src={src(current)}
              alt={current.caption ?? ''}
              className={cn('max-h-full max-w-full object-contain animate-in fade-in')}
              onTouchStart={(e) => {
                const img = e.currentTarget as HTMLImageElement & { dataset: { x0?: string } }
                img.dataset.x0 = String(e.touches[0]?.clientX ?? 0)
              }}
              onTouchEnd={(e) => {
                const img = e.currentTarget as HTMLImageElement & { dataset: { x0?: string } }
                const dx = (e.changedTouches[0]?.clientX ?? 0) - Number(img.dataset.x0 ?? 0)
                if (Math.abs(dx) > 50) geser(dx > 0 ? -1 : 1)
              }}
            />
            <button
              type="button"
              aria-label="Berikutnya"
              onClick={() => geser(1)}
              className="absolute right-2 rounded-full bg-white/10 p-3 text-xl hover:bg-white/20"
            >
              ›
            </button>
          </div>

          <p className="min-h-10 p-4 text-center font-mono text-xs text-gray-300">
            {current.caption ?? ''}
          </p>
        </div>
      ) : null}
    </>
  )
}

function src(f: Foto): string {
  return `${process.env.NEXT_PUBLIC_API_URL}/storage/${f.path}`
}
