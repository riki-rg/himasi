'use client'

import { type CSSProperties, type ReactNode, useEffect, useRef, useState } from 'react'

/**
 * Scroll-reveal — elemen muncul fade-up sekali saat masuk viewport.
 * Reduced-motion: langsung tampak tanpa animasi.
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
      { rootMargin: '-40px 0px' },
    )
    io.observe(el)
    return () => io.disconnect()
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

/** Angka count-up untuk statistik — instant jika reduced-motion. */
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
      let raf = 0

      const tick = (now: number) => {
        const t = Math.min((now - mulai) / durasi, 1)
        const eased = 1 - Math.pow(1 - t, 3)
        setNilai(Math.round(eased * numeric))
        if (t < 1) raf = requestAnimationFrame(tick)
      }
      raf = requestAnimationFrame(tick)
      return () => cancelAnimationFrame(raf)
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
