'use client'

import { type CSSProperties, type ReactNode, useEffect, useRef, useState } from 'react'

/**
 * Scroll-reveal — fade-up saat masuk viewport.
 * Konten SELALU visible secara default (opacity 1 di CSS),
 * animasi hanya tambahan visual. Reduced-motion: skip animasi.
 */
export function Reveal({
  children,
  delay = 0,
  className,
}: {
  children: ReactNode
  delay?: number
  className?: string
}) {
  const ref = useRef<HTMLDivElement>(null)
  const [visible, setVisible] = useState(false)

  useEffect(() => {
    const el = ref.current
    if (!el) return

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      setVisible(true)
      return
    }

    const io = new IntersectionObserver(
      ([entry]) => {
        if (entry?.isIntersecting) {
          setVisible(true)
          io.disconnect()
        }
      },
      { rootMargin: '0px 0px' },
    )
    io.observe(el)

    // Fallback: force visible setelah 1.5 detik apapun yang terjadi
    const timer = setTimeout(() => setVisible(true), 1500)

    return () => {
      io.disconnect()
      clearTimeout(timer)
    }
  }, [])

  return (
    <div
      ref={ref}
      className={`reveal ${className ?? ''}`}
      data-visible={visible}
      style={{ '--reveal-delay': `${delay}ms` } as CSSProperties}
    >
      {children}
    </div>
  )
}

/** Angka count-up — instant jika reduced-motion atau NaN. */
export function CountUp({ target, suffix = '' }: { target: string; suffix?: string }) {
  const numeric = Number.parseInt(target.replace(/\D/g, ''), 10)
  const hasSuffix = target.replace(/[0-9]/g, '')
  const ref = useRef<HTMLSpanElement>(null)
  const [nilai, setNilai] = useState(0)

  useEffect(() => {
    if (Number.isNaN(numeric)) return
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      setNilai(numeric)
      return
    }

    const io = new IntersectionObserver(([entry]) => {
      if (!entry?.isIntersecting) return
      io.disconnect()

      const durasi = 900
      const mulai = performance.now()

      const tick = (now: number) => {
        const t = Math.min((now - mulai) / durasi, 1)
        const eased = 1 - Math.pow(1 - t, 3)
        setNilai(Math.round(eased * numeric))
        if (t < 1) requestAnimationFrame(tick)
      }
      requestAnimationFrame(tick)
    })

    if (ref.current) io.observe(ref.current)
    return () => io.disconnect()
  }, [numeric])

  if (Number.isNaN(numeric)) return <span>{target}</span>

  return (
    <span ref={ref} className="tabular-nums">
      {nilai.toLocaleString('id-ID')}
      {hasSuffix || suffix}
    </span>
  )
}
