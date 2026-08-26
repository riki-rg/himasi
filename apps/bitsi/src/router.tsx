import { Suspense, lazy } from 'react'
import { Navigate, Outlet, createBrowserRouter } from 'react-router'
const HomePage = lazy(() => import('./pages/home'))
const DaftarPage = lazy(() => import('./pages/daftar'))
const LoginPage = lazy(() => import('./pages/login'))
const AppLayout = lazy(() => import('./pages/app-layout'))
const DashboardPage = lazy(() => import('./pages/dashboard'))
const RapatListPage = lazy(() => import('./pages/rapat-list'))
const RapatDetailPage = lazy(() => import('./pages/rapat-detail'))
const PresenterPage = lazy(() => import('./pages/presenter'))
const ProfilPage = lazy(() => import('./pages/profil'))
const PendaftarPage = lazy(() => import('./pages/pendaftar'))
const KelasCatalogPage = lazy(() =>
  import('./pages/kelas').then((m) => ({ default: m.KelasCatalogPage })),
)
const KelasDetailPage = lazy(() =>
  import('./pages/kelas').then((m) => ({ default: m.KelasDetailPage })),
)

function LazyFallback() {
  return (
    <div className="grid min-h-screen place-items-center">
      <div
        className="size-8 animate-spin rounded-full border-2 border-cobalt-600 border-t-transparent"
        aria-label="memuat"
      />
    </div>
  )
}

export const router = createBrowserRouter([
  {
    element: (
      <Suspense fallback={<LazyFallback />}>
        <Outlet />
      </Suspense>
    ),
    children: [
      { path: '/', element: <HomePage /> },
      { path: '/daftar', element: <DaftarPage /> },
      { path: '/login', element: <LoginPage /> },
      {
        path: '/app',
        element: <AppLayout />,
        children: [
          { index: true, element: <DashboardPage /> },
          { path: 'rapat', element: <RapatListPage /> },
          { path: 'rapat/:id', element: <RapatDetailPage /> },
          { path: 'kelas', element: <KelasCatalogPage /> },
          { path: 'kelas/:id', element: <KelasDetailPage /> },
          { path: 'profil', element: <ProfilPage /> },
          { path: 'pengurus/presenter', element: <PresenterPage /> },
          { path: 'pengurus/pendaftar', element: <PendaftarPage /> },
        ],
      },
      { path: '*', element: <Navigate to="/" replace /> },
    ],
  },
])
