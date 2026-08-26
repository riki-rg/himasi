<style>
/* ===== HIMSI Filament Theme — cobalt x IBM Plex x blueprint ===== */
:root { --himsi-cobalt: #2f5be0; --himsi-ink: #0b1220; }

body, .fi-body {
    font-family: 'IBM Plex Sans', ui-sans-serif, system-ui, sans-serif !important;
}

/* Login page — full redesign via CSS only */
.fi-simple-layout {
    max-width: none !important;
    padding: 0 !important;
    min-height: 100vh !important;
    display: grid !important;
    place-items: center !important;
    background: var(--himsi-ink) !important;
    background-image:
        radial-gradient(ellipse 60% 50% at 50% -10%, rgb(79 120 240 / 0.12), transparent),
        linear-gradient(to right, rgb(122 157 248 / 0.04) 1px, transparent 1px),
        linear-gradient(to bottom, rgb(122 157 248 / 0.04) 1px, transparent 1px) !important;
    background-size: auto, 28px 28px, 28px 28px !important;
}

.fi-simple-layout .fi-simple-main {
    width: 100% !important;
    max-width: 24rem !important;
    background: rgb(255 255 255 / 0.04) !important;
    border: 1px solid rgb(122 157 248 / 0.15) !important;
    border-radius: 1rem !important;
    padding: 2.5rem 2rem !important;
    backdrop-filter: blur(12px);
}

/* Hide Filament branding */
.fi-simple-layout .fi-logo,
.fi-simple-layout .fi-simple-page-heading,
.fi-simple-layout .fi-simple-page-subheading,
.fi-layout .fi-logo img { display: none !important; }

/* Custom brand text injection point */
.fi-simple-layout .fi-simple-main::before {
    content: '▣ HIMSI UMKU';
    display: block;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 1rem;
    font-weight: 700;
    letter-spacing: 0.25em;
    color: #a5c0fc;
    text-align: center;
    margin-bottom: 0.5rem;
}

.fi-simple-layout .fi-simple-main::after {
    content: 'Panel Pengurus — Sistem Informasi';
    display: block;
    font-size: 0.65rem;
    letter-spacing: 0.1em;
    color: #475569;
    text-align: center;
    margin-top: 1.5rem;
}

/* Form fields */
.fi-simple-layout .fi-input-wrp {
    border-radius: 0.5rem !important;
    border-color: rgb(122 157 248 / 0.25) !important;
    background: transparent !important;
}
.fi-simple-layout .fi-input-wrp:focus-within {
    border-color: #4f78f0 !important;
    box-shadow: 0 0 0 3px rgb(79 120 240 / 0.12) !important;
}
.fi-simple-layout .fi-input-wrp input {
    background: transparent !important;
    color: #e6edf7 !important;
}
.fi-simple-layout .fi-label {
    font-family: 'IBM Plex Mono', monospace !important;
    font-size: 0.65rem !important;
    text-transform: uppercase !important;
    letter-spacing: 0.15em !important;
    color: #64748b !important;
}

/* Submit button */
.fi-simple-layout .fi-btn-color-primary {
    width: 100%;
    margin-top: 1.5rem;
    padding: 0.75rem !important;
    border-radius: 0.5rem !important;
    background: var(--himsi-cobalt) !important;
    font-weight: 700 !important;
    letter-spacing: 0.15em !important;
    transition: transform 0.15s ease, background 0.15s ease !important;
}
.fi-simple-layout .fi-btn-color-primary:hover {
    background: #2449bd !important;
    transform: translateY(-1px);
}

/* Sidebar dark */
.fi-sidebar {
    background-color: var(--himsi-ink) !important;
}
.fi-sidebar .fi-sidebar-item-label { color: #94a3b8 !important; }
.fi-sidebar .fi-sidebar-item.fi-sidebar-item-active {
    background: rgb(47 91 224 / 0.12) !important;
    border-radius: 0.5rem;
}
.fi-sidebar .fi-sidebar-item.fi-sidebar-item-active .fi-sidebar-item-label {
    color: #a5c0fc !important;
}
.fi-sidebar .fi-sidebar-group-label { color: #334155 !important; }

/* Body */
.fi-body { background-color: #f8fafc !important; }
.dark .fi-body { background-color: #0f172a !important; }

/* Buttons */
.fi-btn-color-primary { background-color: var(--himsi-cobalt) !important; }
.fi-btn-color-primary:hover { background-color: #2449bd !important; }

/* Table */
.fi-ta-row:hover { background-color: rgb(47 91 224 / 0.04) !important; }
.fi-badge { border-radius: 9999px !important; font-weight: 600 !important; }

/* Stat values */
.fi-stat-value { font-variant-numeric: tabular-nums !important; }

/* Rolling digits */
.nf-digit {
    display: inline-block; overflow: hidden;
    height: 1em; line-height: 1em; vertical-align: bottom;
}
.nf-digit__col {
    display: flex; flex-direction: column;
    transition: transform 0.6s cubic-bezier(0.22,0.61,0.36,1);
}
.nf-digit__cell { height: 1em; line-height: 1em; text-align: center; }

@media (prefers-reduced-motion: reduce) {
    .nf-digit__col { transition: none; }
}
</style>
