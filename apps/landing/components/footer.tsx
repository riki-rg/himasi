import { KONTEN_STATIS } from '@/lib/content'

export function Footer() {
  return (
    <footer className="surface mt-20 border-t border-gray-200 bg-white dark:border-white/10">
      <div className="mx-auto grid max-w-6xl gap-8 px-4 py-10 md:grid-cols-3">
        <div>
          <p className="font-mono font-bold tracking-widest">▣ HIMSI UMKU</p>
          <p className="mt-2 text-sm text-gray-500 dark:text-gray-400">
            © 2026 · Universitas Muhammadiyah Kudus
          </p>
        </div>
        <nav
          aria-label="Navigasi footer"
          className="flex flex-col gap-2 text-sm text-gray-600 dark:text-gray-300"
        >
          <span className="font-semibold text-gray-800 dark:text-white">Navigasi</span>
          <a href="/artikel" className="hover:text-cobalt-700 dark:hover:text-cobalt-300">
            Berita
          </a>
          <a href="/agenda" className="hover:text-cobalt-700 dark:hover:text-cobalt-300">
            Agenda
          </a>
          <a href="/galeri" className="hover:text-cobalt-700 dark:hover:text-cobalt-300">
            Galeri
          </a>
        </nav>
        <div className="text-sm text-gray-600 dark:text-gray-300">
          <p className="font-semibold text-gray-800 dark:text-white">Kontak & Sosmed</p>
          <p className="mt-2">✉ {KONTEN_STATIS.kontak.email}</p>
          <p>📷 {KONTEN_STATIS.kontak.instagram}</p>
        </div>
      </div>
    </footer>
  )
}
