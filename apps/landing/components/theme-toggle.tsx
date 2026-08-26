'use client'

import { useEffect, useState } from 'react'

/** Toggle 🌙 dark mode — persist localStorage, sinkron class html.dark. */
export function ThemeToggle() {
  const [gelap, setGelap] = useState(false)

  useEffect(() => {
    setGelap(document.documentElement.classList.contains('dark'))
  }, [])

  function toggle() {
    const next = !gelap
    setGelap(next)
    document.documentElement.classList.toggle('dark', next)
    try {
      localStorage.setItem('himsi-theme', next ? 'dark' : 'light')
    } catch {}
  }

  return (
    <button
      type="button"
      onClick={toggle}
      aria-label={gelap ? 'Aktifkan mode terang' : 'Aktifkan mode gelap'}
      aria-pressed={gelap}
      className="rounded-lg p-2 text-base transition hover:bg-gray-100 dark:hover:bg-white/10"
    >
      {gelap ? '☀️' : '🌙'}
    </button>
  )
}
