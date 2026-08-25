import { KONTEN_STATIS } from '@/lib/content'

export function Footer() {
  return (
    <footer className="mt-20 border-t border-gray-200 bg-white">
      <div className="mx-auto grid max-w-6xl gap-8 px-4 py-10 md:grid-cols-3">
        <div>
          <p className="font-mono font-bold tracking-widest">▣ HIMSI UMKU</p>
          <p className="mt-2 text-sm text-gray-500">© 2026 · Universitas Muhammadiyah Kudus</p>
        </div>
        <nav className="flex flex-col gap-2 text-sm text-gray-600">
          <span className="font-semibold text-gray-800">Navigasi</span>
          <a href="/artikel" className="hover:text-cobalt-700">
            Berita
          </a>
          <a href="/agenda" className="hover:text-cobalt-700">
            Agenda
          </a>
          <a href="/galeri" className="hover:text-cobalt-700">
            Galeri
          </a>
        </nav>
        <div className="text-sm text-gray-600">
          <p className="font-semibold text-gray-800">Kontak & Sosmed</p>
          <p className="mt-2">✉ {KONTEN_STATIS.kontak.email}</p>
          <p>📷 {KONTEN_STATIS.kontak.instagram}</p>
        </div>
      </div>
    </footer>
  )
}
