import { Button } from '@himsi/ui'
import { cn } from '@himsi/ui'
import { useQuery } from '@tanstack/react-query'
import { Link, NavLink, Navigate, Outlet, useNavigate } from 'react-router'
import { type MeResponse, api, hapusToken, punyaToken } from '../lib/api'

const MENU = [
  { to: '/app', label: 'Dashboard', icon: '●' },
  { to: '/app/rapat', label: 'Rapat', icon: '○' },
  { to: '/app/kelas', label: 'Kelas', icon: '○' },
  { to: '/app/profil', label: 'Profil', icon: '○' },
]

function pakaiMenuPengurus(me?: MeResponse): boolean {
  return (me?.penugasan_aktif ?? []).some(
    (p) => p.tingkat === 'utama' && (p.komunitas_kode === null || p.komunitas_kode === 'BITSI'),
  )
}

export default function AppLayout() {
  const navigate = useNavigate()

  if (!punyaToken()) {
    return <Navigate to="/login?redirect=/app" replace />
  }

  const me = useQuery({
    queryKey: ['me'],
    queryFn: () => api.get<MeResponse>('/auth/me'),
    retry: false,
  })

  function keluar() {
    api.post('/auth/logout').finally(() => {
      hapusToken()
      navigate('/login', { replace: true })
    })
  }

  return (
    <div className="flex min-h-screen">
      {/* Sidebar desktop */}
      <aside className="hidden w-60 shrink-0 border-r border-gray-200 bg-white p-5 md:flex md:flex-col">
        <p className="font-mono font-bold tracking-widest">▣ BitSI</p>
        {pakaiMenuPengurus(me.data) ? (
          <div className="mt-6">
            <p className="font-mono text-[10px] font-bold tracking-widest text-gray-400">
              PENGURUS
            </p>
            <nav className="mt-2 flex flex-col gap-1">
              <NavLink
                to="/app/pengurus/presenter"
                className={({ isActive }) =>
                  cn(
                    'rounded-lg px-3 py-2.5 text-sm font-medium',
                    isActive ? 'bg-cobalt-50 text-cobalt-700' : 'text-gray-600 hover:bg-gray-50',
                  )
                }
              >
                🛠 Presenter QR
              </NavLink>
              <NavLink
                to="/app/pengurus/pendaftar"
                className={({ isActive }) =>
                  cn(
                    'rounded-lg px-3 py-2.5 text-sm font-medium',
                    isActive ? 'bg-cobalt-50 text-cobalt-700' : 'text-gray-600 hover:bg-gray-50',
                  )
                }
              >
                🛠 Approve Pendaftar
              </NavLink>
            </nav>
          </div>
        ) : null}

        <nav className="mt-8 flex flex-col gap-1">
          {MENU.map((m) => (
            <NavLink
              key={m.to}
              to={m.to}
              end={m.to === '/app'}
              className={({ isActive }) =>
                cn(
                  'rounded-lg px-3 py-2.5 text-sm font-medium transition',
                  isActive ? 'bg-cobalt-50 text-cobalt-700' : 'text-gray-600 hover:bg-gray-50',
                )
              }
            >
              <span aria-hidden className="mr-2 text-[10px]">
                {m.icon}
              </span>
              {m.label}
            </NavLink>
          ))}
        </nav>

        <div className="mt-auto">
          {me.data ? (
            <div className="mb-3 text-sm">
              <p className="font-semibold">{me.data.member?.nama ?? me.data.name}</p>
              <p className="text-xs text-gray-400">angkatan {me.data.member?.angkatan ?? '-'}</p>
            </div>
          ) : null}
          <Button variant="ghost" className="w-full !justify-start" onClick={keluar}>
            Keluar
          </Button>
        </div>
      </aside>

      <div className="flex min-w-0 flex-1 flex-col">
        {/* Header mobile */}
        <header className="flex items-center justify-between border-b border-gray-200 bg-white px-4 py-3 md:hidden">
          <p className="font-mono font-bold tracking-widest">▣ BitSI</p>
          <button type="button" onClick={keluar} className="text-sm text-gray-500">
            Keluar
          </button>
        </header>

        <main className="mx-auto w-full max-w-4xl flex-1 px-4 pb-24 pt-6 md:px-6 md:pb-10">
          {me.isError ? (
            <p className="rounded-lg bg-red-50 p-4 text-sm text-red-700">
              Sesi tidak valid — coba login ulang.
            </p>
          ) : (
            <Outlet context={me} />
          )}
        </main>

        {/* Bottom tab mobile */}
        <nav className="sticky bottom-0 grid grid-cols-4 border-t border-gray-200 bg-white md:hidden">
          {MENU.map((m) => (
            <NavLink
              key={m.to}
              to={m.to}
              end={m.to === '/app'}
              className={({ isActive }) =>
                cn(
                  'py-3 text-center text-xs font-medium',
                  isActive ? 'text-cobalt-700' : 'text-gray-400',
                )
              }
            >
              {m.label}
            </NavLink>
          ))}
        </nav>
      </div>

      <Link to="/daftar" className="hidden" aria-hidden />
    </div>
  )
}
