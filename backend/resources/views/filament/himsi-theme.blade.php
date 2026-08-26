<style>
/* ===== HIMSI Filament Theme v2 — clean light theme, cobalt accent ===== */
:root { --himsi: #2f5be0; --himsi-light: #eef4ff; --himsi-dark: #2449bd; }

/* Font */
body, .fi-body { font-family: 'IBM Plex Sans', ui-sans-serif, system-ui, sans-serif !important; }

/* ===== LOGIN PAGE ===== */
.fi-simple-layout {
    max-width: none !important;
    min-height: 100vh !important;
    display: grid !important;
    place-items: center !important;
    background: #f0f4ff !important;
    background-image:
        radial-gradient(ellipse 50% 40% at 50% 0%, rgb(47 91 224 / 0.08), transparent),
        linear-gradient(to right, rgb(47 91 224 / 0.03) 1px, transparent 1px),
        linear-gradient(to bottom, rgb(47 91 224 / 0.03) 1px, transparent 1px) !important;
    background-size: auto, 24px 24px, 24px 24px !important;
}

/* Hide Filament logo & heading */
.fi-simple-layout .fi-logo,
.fi-simple-layout .fi-simple-page-heading { display: none !important; }

/* Card */
.fi-simple-layout .fi-simple-main {
    width: 100% !important;
    max-width: 26rem !important;
    background: white !important;
    border: 1px solid rgb(47 91 224 / 0.1) !important;
    border-radius: 1rem !important;
    padding: 2.5rem 2.25rem !important;
    box-shadow: 0 4px 24px rgb(47 91 224 / 0.08) !important;
}

/* Inject brand above form */
.fi-simple-layout .fi-simple-main::before {
    content: '';
    display: block;
    width: 48px;
    height: 48px;
    margin: 0 auto 1rem;
    border-radius: 12px;
    background: var(--himsi);
    background: var(--himsi); color: white; font-family: 'IBM Plex Mono', monospace; font-size: 1.4rem; font-weight: 700; line-height: 48px; text-align: center; content: 'H';
}

.fi-simple-layout .fi-simple-main::after {
    content: 'HIMSI UMKU — Panel Pengurus';
    display: block;
    text-align: center;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 0.7rem;
    letter-spacing: 0.15em;
    color: #94a3b8;
    margin-top: 1.5rem;
}

/* Labels */
.fi-simple-layout .fi-label {
    font-size: 0.8rem !important;
    font-weight: 600 !important;
    color: #374151 !important;
}

/* Inputs */
.fi-simple-layout .fi-input-wrp {
    border-radius: 0.6rem !important;
    border-color: #d1d5db !important;
    transition: border-color 0.15s, box-shadow 0.15s !important;
}
.fi-simple-layout .fi-input-wrp:focus-within {
    border-color: var(--himsi) !important;
    box-shadow: 0 0 0 3px rgb(47 91 224 / 0.1) !important;
}

/* Button */
.fi-simple-layout .fi-btn-color-primary {
    width: 100%;
    padding: 0.7rem !important;
    border-radius: 0.6rem !important;
    background: var(--himsi) !important;
    font-weight: 600 !important;
    transition: all 0.15s ease !important;
}
.fi-simple-layout .fi-btn-color-primary:hover {
    background: var(--himsi-dark) !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgb(47 91 224 / 0.25) !important;
}

/* ===== DASHBOARD / PANEL ===== */

/* Sidebar — clean white with cobalt active */
.fi-sidebar {
    background: white !important;
    border-right: 1px solid #f0f0f0 !important;
}
.fi-sidebar .fi-sidebar-item {
    border-radius: 0.5rem !important;
    margin: 1px 8px !important;
    transition: background 0.15s !important;
}
.fi-sidebar .fi-sidebar-item:hover {
    background: #f0f4ff !important;
}
.fi-sidebar .fi-sidebar-item.fi-sidebar-item-active {
    background: var(--himsi-light) !important;
    color: var(--himsi) !important;
}
.fi-sidebar .fi-sidebar-item.fi-sidebar-item-active .fi-sidebar-item-label {
    color: var(--himsi) !important;
    font-weight: 600 !important;
}
.fi-sidebar .fi-sidebar-group-label {
    font-size: 0.65rem !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.1em !important;
    color: #9ca3af !important;
}

/* Topbar */
.fi-topbar {
    background: white !important;
    border-bottom: 1px solid #f0f0f0 !important;
}

/* Body bg */
.fi-body { background-color: #f8fafc !important; }

/* Cards */
.fi-section {
    border-radius: 0.75rem !important;
    box-shadow: 0 1px 3px rgb(0 0 0 / 0.04), 0 1px 2px rgb(0 0 0 / 0.03) !important;
}

/* Buttons */
.fi-btn-color-primary {
    background: var(--himsi) !important;
    border-radius: 0.5rem !important;
}
.fi-btn-color-primary:hover { background: var(--himsi-dark) !important; }

/* Tables */
.fi-ta-row:hover { background: #f0f4ff !important; }
.fi-badge { border-radius: 9999px !important; font-weight: 600 !important; }

/* Stat values */
.fi-stat-value { font-variant-numeric: tabular-nums !important; font-weight: 700 !important; }

/* NumberFlow rolling digits */
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

<style>
/* Stats harus di atas chart */
.fi-wi-stats-overview { order: -1; }

/* Nominal tidak wrap */
.fi-stat-value { white-space: nowrap !important; }

/* Stat label lebih kecil */
.fi-stat-label {
    font-size: 0.75rem !important;
    color: #6b7280 !important;
    font-weight: 500 !important;
}

/* Chart card title */
.fi-wi-chart .fi-section-header-heading { font-size: 0.9rem !important; }
</style>

<style>
/* Override English labels ke Indonesia */
.fi-simple-layout .fi-simple-page-heading {
    font-size: 0 !important;
}
.fi-simple-layout .fi-simple-page-heading::after {
    content: 'Masuk';
    font-size: 1.5rem;
    font-weight: 700;
    color: #111827;
}
.fi-simple-layout .fi-label[for*="email"]::after { content: ''; }
.fi-simple-layout .fi-label[for*="email"] { font-size: 0 !important; }
.fi-simple-layout .fi-label[for*="email"]::before {
    content: 'Alamat Email';
    font-size: 0.8rem;
}
.fi-simple-layout .fi-label[for*="password"] { font-size: 0 !important; }
.fi-simple-layout .fi-label[for*="password"]::before {
    content: 'Password';
    font-size: 0.8rem;
}
.fi-simple-layout .fi-btn-color-primary { font-size: 0 !important; }
.fi-simple-layout .fi-btn-color-primary::after {
    content: 'Masuk ke Panel';
    font-size: 0.875rem;
    font-weight: 600;
}
.fi-simple-layout .fi-checkbox-label { font-size: 0 !important; }
.fi-simple-layout .fi-checkbox-label::after {
    content: 'Ingat saya';
    font-size: 0.875rem;
}
</style>
