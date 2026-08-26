import { ApiError, Badge, Button, Card, EmptyState, SkeletonBlock } from '@himsi/ui'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { useState } from 'react'
import { api } from '../lib/api'

interface Pendaftar {
  id: number
  member: {
    nim: string
    nama: string
    prodi?: string | null
    angkatan: number
    email?: string | null
    no_hp?: string | null
    link_portofolio?: string | null
    link_instagram?: string | null
  } | null
  status: 'pending' | 'disetujui' | 'ditolak'
  daftar_pada: string | null
}

const TABS = [
  { status: 'pending', label: '⏳ Menunggu' },
  { status: 'disetujui', label: '✅ Disetujui' },
  { status: 'ditolak', label: '🚫 Ditolak' },
] as const

export default function PendaftarPage() {
  const [tab, setTab] = useState<(typeof TABS)[number]['status']>('pending')
  const queryClient = useQueryClient()
  const [undoId, setUndoId] = useState<number | null>(null)

  const daftar = useQuery({
    queryKey: ['pendaftar', tab],
    queryFn: () => api.get<Pendaftar[]>(`/keanggotaan?komunitas=BITSI&status=${tab}`),
  })

  const proses = useMutation({
    mutationFn: ({ id, status }: { id: number; status: 'disetujui' | 'ditolak' }) =>
      api.patch(`/keanggotaan/${id}`, { status }),
    onSuccess: async (_data, vars) => {
      await queryClient.invalidateQueries({ queryKey: ['pendaftar'] })
      if (vars.status === 'disetujui') {
        setUndoId(vars.id)
        setTimeout(() => setUndoId(null), 5000)
      }
    },
    onError: (err) => {
      if (err instanceof ApiError && err.status === 409) {
        void queryClient.invalidateQueries({ queryKey: ['pendaftar'] })
      }
    },
  })

  function undo() {
    if (undoId === null) return
    proses.mutate({ id: undoId, status: 'ditolak' })
    setUndoId(null)
  }

  return (
    <div>
      <h1 className="text-xl font-bold">Persetujuan Anggota Baru</h1>

      <div className="mt-4 flex flex-wrap gap-2">
        {TABS.map((t) => (
          <button
            key={t.status}
            type="button"
            onClick={() => setTab(t.status)}
            className={`rounded-full px-4 py-1.5 text-sm font-medium transition ${
              tab === t.status
                ? 'bg-cobalt-600 text-white'
                : 'border border-gray-300 text-gray-600 hover:bg-gray-50'
            }`}
          >
            {t.label}
          </button>
        ))}
      </div>

      {daftar.isPending ? (
        <div className="mt-6 space-y-3">
          {[0, 1].map((i) => (
            <SkeletonBlock key={i} className="h-28" />
          ))}
        </div>
      ) : !daftar.data || daftar.data.length === 0 ? (
        <div className="mt-8">
          <EmptyState
            title={tab === 'pending' ? 'Tidak ada pendaftar menunggu 🎉' : `Belum ada data ${tab}`}
          />
        </div>
      ) : (
        <ul className="mt-6 space-y-3">
          {daftar.data.map((p) =>
            p.member ? (
              <li key={p.id}>
                <Card className={proses.isPending ? 'opacity-60' : undefined}>
                  <div className="flex flex-wrap items-start justify-between gap-3">
                    <div className="min-w-0">
                      <p className="font-semibold">{p.member.nama}</p>
                      <p className="font-mono text-xs text-gray-400">
                        NIM {p.member.nim} · {p.member.angkatan} · {p.member.prodi ?? 'SI'}
                      </p>
                      <p className="mt-1 text-xs text-gray-500">
                        ✉ {p.member.email ?? '-'} {p.member.no_hp ? `· 📱 ${p.member.no_hp}` : ''}
                      </p>
                      <div className="mt-1.5 flex gap-3 text-xs">
                        {p.member.link_portofolio ? (
                          <a
                            href={p.member.link_portofolio}
                            target="_blank"
                            rel="noreferrer"
                            className="text-cobalt-600"
                          >
                            🔗 portofolio
                          </a>
                        ) : null}
                        {p.member.link_instagram ? (
                          <a
                            href={p.member.link_instagram}
                            target="_blank"
                            rel="noreferrer"
                            className="text-cobalt-600"
                          >
                            📷 instagram
                          </a>
                        ) : null}
                      </div>
                    </div>

                    {tab === 'pending' ? (
                      <div className="flex shrink-0 gap-2">
                        <Button
                          className="!px-4 !py-1.5 text-xs"
                          loading={proses.isPending}
                          onClick={() => proses.mutate({ id: p.id, status: 'disetujui' })}
                        >
                          ✓ Setujui
                        </Button>
                        <Button
                          variant="danger"
                          className="!px-4 !py-1.5 text-xs"
                          onClick={() => proses.mutate({ id: p.id, status: 'ditolak' })}
                        >
                          ✕ Tolak
                        </Button>
                      </div>
                    ) : (
                      <Badge tone={p.status === 'disetujui' ? 'green' : 'red'}>{p.status}</Badge>
                    )}
                  </div>
                  {p.daftar_pada ? (
                    <p className="mt-2 font-mono text-[10px] text-gray-300">
                      daftar {new Date(p.daftar_pada).toLocaleString('id-ID')}
                    </p>
                  ) : null}
                </Card>
              </li>
            ) : null,
          )}
        </ul>
      )}

      {/* Undo toast */}
      {undoId !== null ? (
        <div className="fixed bottom-24 left-1/2 z-50 -translate-x-1/2 rounded-full bg-gray-900 px-5 py-2.5 text-sm text-white shadow-xl md:bottom-6">
          Disetujui.
          <button
            type="button"
            onClick={undo}
            className="ml-3 font-bold text-cobalt-300 hover:text-cobalt-200"
          >
            Batalkan?
          </button>
        </div>
      ) : null}
    </div>
  )
}
