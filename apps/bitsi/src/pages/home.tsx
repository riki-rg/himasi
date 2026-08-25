import { Badge, Button } from '@himsi/ui'
import { Link } from 'react-router'

const BIDANG = ['Web Dev', 'IoT', 'Jaringan', 'Server']

export default function HomePage() {
  return (
    <div className="min-h-screen">
      <header className="flex items-center justify-between px-5 py-4">
        <p className="font-mono font-bold tracking-widest">▣ BitSI</p>
        <Link to="/login">
          <Button variant="outline" className="!px-3 !py-1.5 text-xs">
            Masuk
          </Button>
        </Link>
      </header>

      <section className="blueprint-grid border-y border-gray-200 px-5 py-20 text-center">
        <p className="font-mono text-xs font-bold tracking-[0.3em] text-cobalt-600">
          BIT OF SISTEM INFORMASI
        </p>
        <h1 className="mt-4 text-3xl font-bold md:text-4xl">
          Ngoprek bareng,
          <br />
          belajar bareng.
        </h1>
        <p className="mx-auto mt-4 max-w-md text-gray-600">
          Komunitas tech HIMSI UMKU untuk yang suka web development, IoT, jaringan, dan server.
        </p>

        <div className="mt-8 flex flex-wrap justify-center gap-2">
          {BIDANG.map((b) => (
            <Badge key={b} tone="cobalt">
              {b}
            </Badge>
          ))}
        </div>

        <Link to="/daftar" className="mt-10 inline-block">
          <Button className="!px-6 !py-3">GABUNG BITSI →</Button>
        </Link>
      </section>

      <footer className="px-5 py-6 text-center font-mono text-xs text-gray-400">
        © BitSI · Divisi Pengembangan Diri · HIMSI UMKU
      </footer>
    </div>
  )
}
