<x-filament-panels::page.simple>
    <div class="himsi-login-wrapper">
        <div class="himsi-login-grid" aria-hidden></div>

        <div class="himsi-login-card">
            <div class="himsi-login-brand">
                <span class="himsi-login-logo">▣</span>
                <p class="himsi-login-title">HIMSI UMKU</p>
                <p class="himsi-login-subtitle">Panel Pengurus — Sistem Informasi</p>
            </div>

            {{ $this->form }}

            <button
                type="submit"
                class="fi-btn fi-btn-color-primary fi-btn-size-md himsi-login-submit"
            >
                Masuk ke Panel →
            </button>

            <p class="himsi-login-footer">
                © 2026 HIMSI · Universitas Muhammadiyah Kudus
            </p>
        </div>
    </div>

    <style>
        .himsi-login-wrapper {
            display: grid;
            place-items: center;
            min-height: 100vh;
            position: relative;
            overflow: hidden;
            background: #0b1220;
            padding: 1.5rem;
        }

        .himsi-login-grid {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(to right, rgb(122 157 248 / 0.05) 1px, transparent 1px),
                linear-gradient(to bottom, rgb(122 157 248 / 0.05) 1px, transparent 1px);
            background-size: 28px 28px;
            pointer-events: none;
        }

        .himsi-login-grid::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse 70% 50% at 50% -10%, rgb(47 91 224 / 0.15), transparent);
        }

        .himsi-login-card {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 24rem;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgb(122 157 248 / 0.15);
            border-radius: 1rem;
            padding: 2.5rem 2rem;
            backdrop-filter: blur(12px);
        }

        .himsi-login-brand {
            text-align: center;
            margin-bottom: 2rem;
        }

        .himsi-login-logo {
            font-size: 2rem;
            color: #4f78f0;
        }

        .himsi-login-title {
            font-family: 'IBM Plex Mono', monospace;
            font-size: 1rem;
            font-weight: 700;
            letter-spacing: 0.25em;
            color: #a5c0fc;
            margin-top: 0.5rem;
        }

        .himsi-login-subtitle {
            font-size: 0.7rem;
            letter-spacing: 0.15em;
            color: #64748b;
            margin-top: 0.25rem;
        }

        /* Override Filament form fields */
        .himsi-login-card .fi-input-wrp {
            border-radius: 0.5rem !important;
            border-color: rgb(122 157 248 / 0.25) !important;
            background: transparent !important;
        }

        .himsi-login-card .fi-input-wrp:focus-within {
            border-color: #4f78f0 !important;
            box-shadow: 0 0 0 2px rgb(79 120 240 / 0.15) !important;
        }

        .himsi-login-card .fi-input {
            background: transparent !important;
            color: #e6edf7 !important;
        }

        .himsi-login-card .fi-label {
            font-family: 'IBM Plex Mono', monospace !important;
            font-size: 0.65rem !important;
            font-weight: 600 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.15em !important;
            color: #64748b !important;
        }

        .himsi-login-submit {
            width: 100%;
            margin-top: 1.5rem;
            padding: 0.75rem !important;
            border-radius: 0.5rem !important;
            background: #2f5be0 !important;
            font-weight: 700 !important;
            font-size: 0.8rem !important;
            letter-spacing: 0.15em !important;
            transition: all 0.2s ease;
        }

        .himsi-login-submit:hover {
            background: #4f78f0 !important;
            transform: translateY(-1px);
        }

        .himsi-login-submit:active {
            transform: scale(0.98);
        }

        .himsi-login-footer {
            text-align: center;
            font-size: 0.65rem;
            color: #334155;
            margin-top: 2rem;
        }
    </style>
</x-filament-panels::page.simple>
