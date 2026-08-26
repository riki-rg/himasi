import { Suspense, lazy } from 'react'
import { Link, Navigate, Outlet, createBrowserRouter, useLocation, useNavigate } from 'react-router'
import { api, hapusToken, punyaToken } from './lib/api'

const HomePage = lazy(() => import('./pages/home'))
const LoginPage = lazy(() => import('./pages/login'))
const AppPage = lazy(() => import('./pages/app'))
const DiskusiDetailPage = lazy(() => import('./pages/diskusi-detail'))
const BacaanKatalog = lazy(() =>
  import('./pages/bacaan').then((m) => ({ default: m.BacaanKatalog })),
)
const BacaanDetail = lazy(() => import('./pages/bacaan').then((m) => ({ default: m.BacaanDetail })))

function RequireAuth() {
  const location = useLocation()
  return punyaToken() ? (
    <Outlet />
  ) : (
    <Navigate to="/login" replace state={{ dari: location.pathname }} />
  )
}

function Fallback() {
  return <div className="grid min-h-screen place-items-center text-forest-700">Memuat…</div>
}

function AppShell() {
  const navigate = useNavigate()

  return (
    <div className="min-h-screen">
      <header className="flex items-center justify-between border-b border-forest-100 bg-kertas px-5 py-4">
        <Link to="/" className="font-serif text-lg font-bold italic text-forest-700">
          Sibiner
        </Link>
        <button
          type="button"
          className="text-sm text-forest-600 hover:text-forest-700"
          onClick={() =>
            api.post('/auth/logout').finally(() => {
              hapusToken()
              navigate('/login', { replace: true })
            })
          }
        >
          Keluar
        </button>
      </header>

      <Suspense fallback={<Fallback />}>
        <Outlet />
      </Suspense>

      <footer className="px-5 py-8 text-center font-serif text-sm italic text-forest-600">
        © Sibiner · Divisi Organisasi · HIMSI UMKU
      </footer>
    </div>
  )
}

export const router = createBrowserRouter([
  {
    element: (
      <Suspense fallback={<Fallback />}>
        <AppShell />
      </Suspense>
    ),
    children: [
      { path: '/', element: <HomePage /> },
      { path: '/login', element: <LoginPage /> },
      {
        path: '/app',
        element: <RequireAuth />,
        children: [
          { index: true, element: <AppPage /> },
          { path: 'diskusi/:id', element: <DiskusiDetailPage /> },
          { path: 'bacaan', element: <BacaanKatalog /> },
          { path: 'bacaan/:id', element: <BacaanDetail /> },
        ],
      },
      { path: '*', element: <Navigate to="/" replace /> },
    ],
  },
])
