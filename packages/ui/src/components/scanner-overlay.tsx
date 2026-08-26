'use client'

import { Button, Input } from '@himsi/ui'
import { Html5Qrcode } from 'html5-qrcode'
import { useEffect, useRef, useState } from 'react'

interface ScannerOverlayProps {
  onDecode: (token: string) => void
  manualError?: string | null
  onClose: () => void
}

/** Overlay kamera fullscreen — frame sudut cobalt + fallback ketik kode (bitsi-rapat-detail.md). */
export function ScannerOverlay({ onDecode, manualError, onClose }: ScannerOverlayProps) {
  const [izinDitolak, setIzinDitolak] = useState(false)
  const scannerRef = useRef<Html5Qrcode | null>(null)
  const regionId = 'absensi-scanner-region'

  useEffect(() => {
    const scanner = new Html5Qrcode(regionId, { verbose: false })
    scannerRef.current = scanner

    scanner
      .start(
        { facingMode: 'environment' },
        { fps: 10, qrbox: { width: 240, height: 240 } },
        (text) => {
          void scanner.stop().catch(() => {})
          onDecode(text)
        },
        () => setIzinDitolak(true),
      )
      .catch(() => {})

    return () => {
      scanner.stop().catch(() => {})
    }
  }, [onDecode])

  function submitManual(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault()
    const token = String(new FormData(e.currentTarget).get('token') ?? '').trim()
    if (token) onDecode(token)
  }

  return (
    <div
      className="fixed inset-0 z-50 flex flex-col bg-black/90 text-white"
      role="dialog"
      aria-modal
    >
      <div className="flex items-center justify-between p-4">
        <p className="font-mono text-xs tracking-widest">ARAHKAN KE QR PENGURUS</p>
        <button
          type="button"
          onClick={onClose}
          aria-label="Tutup scanner"
          className="text-2xl leading-none"
        >
          ✕
        </button>
      </div>

      <div className="relative mx-auto aspect-square w-full max-w-xs">
        <div
          id={regionId}
          className="h-full w-full overflow-hidden rounded-lg [&_video]:object-cover"
        />
        {!izinDitolak ? (
          <div
            className="pointer-events-none absolute inset-6 rounded-lg border-2 border-cobalt-400/0 [border-image:none]"
            aria-hidden
          >
            {[
              'top-0 left-0 border-t-4 border-l-4',
              'top-0 right-0 border-t-4 border-r-4',
              'bottom-0 left-0 border-b-4 border-l-4',
              'bottom-0 right-0 border-b-4 border-r-4',
            ].map((pos) => (
              <span key={pos} className={`absolute size-8 ${pos} border-cobalt-400`} />
            ))}
          </div>
        ) : null}
      </div>

      {izinDitolak ? (
        <p className="mx-auto mt-4 max-w-xs px-5 text-center text-sm text-gray-300">
          Kamera tidak tersedia / izin ditolak. Ketik kode manual di bawah.
        </p>
      ) : null}

      <form onSubmit={submitManual} className="mx-auto mt-auto flex w-full max-w-sm gap-2 p-5">
        <Input
          name="token"
          placeholder="Atau ketik kode manual…"
          autoComplete="off"
          className="flex-1 !bg-white/10 !text-white placeholder:text-gray-400"
          aria-invalid={manualError ? true : undefined}
        />
        <Button type="submit">Absen</Button>
      </form>
      {manualError ? (
        <p role="alert" className="pb-5 text-center text-sm text-red-400">
          {manualError}
        </p>
      ) : null}
    </div>
  )
}
