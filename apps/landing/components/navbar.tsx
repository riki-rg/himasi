'use client'

import { cn } from '@himsi/ui'
import Link from 'next/link'
import { usePathname } from 'next/navigation'
import { useState } from 'react'
import { ThemeToggle } from './theme-toggle'

const NAV = [
  { href: '/artikel', label: 'Berita' },
  { href: '/agenda', label: 'Agenda' },
  { href: '/struktur', label: 'Struktur' },
  { href: '/galeri', label: 'Galeri' },
]

export function Navbar() {
  const pathname = usePathname()
  const [open, setOpen] = useState(false)

  return (
    <header className="sticky top-0 z-50 border-b border-gray-200 bg-paper/80 backdrop-blur dark:border-white/10 dark:bg-[#0b1220]/80">
      <nav className="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
        <Link href="/" className="flex items-center gap-2 font-mono font-bold tracking-widest">
          <span className="inline-block size-3 rounded-sm bg-cobalt-600" aria-hidden />
          HIMSI
        </Link>

        <div className="hidden items-center gap-1 md:flex">
          {NAV.map((item) => (
            <Link
              key={item.href}
              href={item.href}
              className={cn(
                'rounded-md px-3 py-2 text-sm font-medium transition hover:text-cobalt-700 dark:hover:text-cobalt-300',
                pathname.startsWith(item.href)
                  ? 'bg-cobalt-50 text-cobalt-700 dark:bg-cobalt-600/20 dark:text-cobalt-300'
                  : 'text-gray-600 dark:text-gray-300',
              )}
            >
              {item.label}
            </Link>
          ))}
          <ThemeToggle />
        </div>

        <button
          type="button"
          aria-label={open ? 'Tutup menu' : 'Buka menu'}
          aria-expanded={open}
          onClick={() => setOpen((v) => !v)}
          className="rounded-md p-2 hover:bg-gray-100 dark:hover:bg-white/10 md:hidden"
        >
          ☰
        </button>
      </nav>

      {open ? (
        <div className="border-t border-gray-100 bg-white px-4 py-2 md:hidden dark:border-white/10 dark:bg-[#101a2e]">
          {NAV.map((item) => (
            <Link
              key={item.href}
              href={item.href}
              onClick={() => setOpen(false)}
              className="block rounded-md px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-cobalt-50 dark:text-gray-200 dark:hover:bg-white/5"
            >
              {item.label}
            </Link>
          ))}
          <div className="px-3 py-2">
            <ThemeToggle />
          </div>
        </div>
      ) : null}
    </header>
  )
}
