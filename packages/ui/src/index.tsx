import type { ButtonHTMLAttributes, InputHTMLAttributes, ReactNode } from 'react'
import { initial as toInitial } from './lib/api'
import { cn } from './lib/cn'

export { cn }

type ButtonVariant = 'primary' | 'outline' | 'ghost' | 'danger'

export function Button({
  variant = 'primary',
  className,
  loading = false,
  children,
  ...props
}: ButtonHTMLAttributes<HTMLButtonElement> & { variant?: ButtonVariant; loading?: boolean }) {
  return (
    <button
      {...props}
      disabled={props.disabled || loading}
      className={cn(
        'inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-semibold transition',
        'focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2',
        'disabled:cursor-not-allowed disabled:opacity-50',
        variant === 'primary' &&
          'bg-cobalt-600 text-white hover:bg-cobalt-700 focus-visible:outline-cobalt-600',
        variant === 'outline' &&
          'border border-cobalt-300 text-cobalt-700 hover:bg-cobalt-50 focus-visible:outline-cobalt-400',
        variant === 'ghost' && 'text-gray-700 hover:bg-gray-100',
        variant === 'danger' && 'border border-red-300 text-red-700 hover:bg-red-50',
        className,
      )}
    >
      {loading ? <Spinner /> : null}
      {children}
    </button>
  )
}

export function Spinner({ className }: { className?: string }) {
  return (
    <span
      aria-label="memproses"
      className={cn(
        'inline-block size-4 animate-spin rounded-full border-2 border-current border-t-transparent',
        className,
      )}
    />
  )
}

export function Card({ className, children }: { className?: string; children: ReactNode }) {
  return (
    <div className={cn('rounded-xl border border-gray-200 bg-white p-5 shadow-sm', className)}>
      {children}
    </div>
  )
}

export function Field({
  label,
  error,
  required,
  hint,
  children,
}: {
  label: string
  error?: string[]
  required?: boolean
  hint?: string
  children: ReactNode
}) {
  return (
    <div className="flex flex-col gap-1">
      {/* biome-ignore lint/a11y/noLabelWithoutControl: kontrol dikirim lewat children (Input/Select) */}
      <label className="flex flex-col gap-1 text-sm font-medium text-gray-800">
        <span>
          {label}
          {required ? <span className="text-red-500"> *</span> : null}
        </span>
        {children}
      </label>
      {hint && !error ? <p className="text-xs text-gray-500">{hint}</p> : null}
      {error?.map((e) => (
        <p key={e} role="alert" className="text-xs text-red-600">
          {e}
        </p>
      ))}
    </div>
  )
}

export function Input({ className, ...props }: InputHTMLAttributes<HTMLInputElement>) {
  return (
    <input
      {...props}
      className={cn(
        'rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm outline-none transition',
        'focus:border-cobalt-500 focus:ring-2 focus:ring-cobalt-100',
        className,
      )}
    />
  )
}

export type BadgeTone = 'neutral' | 'cobalt' | 'amber' | 'green' | 'red'

export function Badge({ tone = 'neutral', children }: { tone?: BadgeTone; children: ReactNode }) {
  return (
    <span
      className={cn(
        'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold',
        tone === 'neutral' && 'bg-gray-100 text-gray-700',
        tone === 'cobalt' && 'bg-cobalt-50 text-cobalt-700',
        tone === 'amber' && 'bg-amber-50 text-amber-700',
        tone === 'green' && 'bg-green-50 text-green-700',
        tone === 'red' && 'bg-red-50 text-red-700',
      )}
    >
      {children}
    </span>
  )
}

export function Avatar({
  nama,
  src,
  size = 40,
}: { nama: string; src?: string | null; size?: number }) {
  return src ? (
    // eslint-disable-next-line @next/next/no-img-element
    <img src={src} alt={nama} width={size} height={size} className="rounded-full object-cover" />
  ) : (
    <span
      style={{ width: size, height: size }}
      className="inline-flex items-center justify-center rounded-full bg-cobalt-600 text-xs font-bold text-white"
    >
      {toInitial(nama)}
    </span>
  )
}

/** State matrix helpers — wajib di setiap section data-driven (wireframe konvensi). */
export function SkeletonBlock({ className }: { className?: string }) {
  return <div className={cn('animate-pulse rounded-lg bg-gray-200', className)} />
}

export function EmptyState({ title, description }: { title: string; description?: string }) {
  return (
    <div className="flex flex-col items-center gap-1 rounded-xl border border-dashed border-gray-300 p-8 text-center">
      <p className="font-medium text-gray-700">{title}</p>
      {description ? <p className="text-sm text-gray-500">{description}</p> : null}
    </div>
  )
}

export function ErrorState({ onRetry }: { onRetry?: () => void }) {
  return (
    <div className="flex flex-col items-start gap-2 rounded-xl bg-red-50 p-4">
      <p className="text-sm font-medium text-red-800">Gagal memuat data.</p>
      {onRetry ? (
        <Button variant="outline" onClick={onRetry} className="!px-3 !py-1.5 text-xs">
          Coba lagi
        </Button>
      ) : null}
    </div>
  )
}

export {
  ApiError,
  createApi,
  formatRupiah,
  formatTanggal,
  initial,
  type ProblemJson,
  type ApiClientOptions,
} from './lib/api'
