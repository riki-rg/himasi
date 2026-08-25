import { Suspense, lazy } from 'react'
import { Link, Navigate, Outlet, createBrowserRouter, useNavigate } from 'react-router'
import { api, hapusToken } from './lib/api'

const HomePage = lazy(() => import('./pages/home'))
const LoginPage = lazy(() => import('./pages/login'))
const AppPage = lazy(() => import('./pages/app'))

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
      { path: '/app', element: <AppPage /> },
      { path: '*', element: <Navigate to="/" replace /> },
    ],
  },
])
