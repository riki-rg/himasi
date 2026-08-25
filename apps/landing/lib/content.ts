export const KONTEN_STATIS = {
  nama: 'HIMPUNAN MAHASISWA SISTEM INFORMASI',
  universitas: 'UNIVERSITAS MUHAMMADIYAH KUDUS',
  tagline: 'Satu Himpunan, Banyak Wawasan',
  tentang:
    'HIMSI adalah wadah pengembangan diri mahasiswa Sistem Informasi UMKU — mengasah nalar teknologi, organisasi, dan literasi lewat tiga komunitas unggulan.',
  visi: 'Menjadi himpunan yang adaptif, kolaboratif, dan berdampak bagi mahasiswa Sistem Informasi.',
  misi: [
    'Mengembangkan kompetensi teknologi lewat komunitas BitSI',
    'Menumbuhkan budaya literasi & nalar kritis lewat Sibiner',
    'Memperkuat jejaring alumni dan industri',
  ],
  statistik: [
    { label: 'Anggota', nilai: '150+' },
    { label: 'Komunitas', nilai: '2' },
    { label: 'Divisi', nilai: '5' },
    { label: 'Event/Tahun', nilai: '20+' },
  ],
  kontak: {
    email: 'himsi@umku.ac.id',
    instagram: '@himsiumku',
  },
} as const

export const API_URL = process.env.NEXT_PUBLIC_API_URL ?? ''
export const BITSI_URL = process.env.NEXT_PUBLIC_BITSI_URL ?? ''
export const SIBINER_URL = process.env.NEXT_PUBLIC_SIBINER_URL ?? ''
