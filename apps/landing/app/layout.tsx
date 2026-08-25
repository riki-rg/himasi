import { Footer } from '@/components/footer'
import { Navbar } from '@/components/navbar'
import type { Metadata } from 'next'
import './globals.css'

export const metadata: Metadata = {
  title: {
    default: 'HIMSI UMKU — Himpunan Mahasiswa Sistem Informasi',
    template: '%s · HIMSI UMKU',
  },
  description:
    'Website resmi Himpunan Mahasiswa Sistem Informasi Universitas Muhammadiyah Kudus: berita, agenda, galeri, dan gerbang komunitas BitSI & Sibiner.',
}

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="id">
      <body className="min-h-screen antialiased">
        <Navbar />
        <main>{children}</main>
        <Footer />
      </body>
    </html>
  )
}
