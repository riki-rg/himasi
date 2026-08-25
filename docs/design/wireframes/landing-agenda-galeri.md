# Wireframe — Agenda `/agenda` & Galeri Index `/galeri` Landing Page

> Status: Draft v1 · Visual: Engineering Blueprint · Dua halaman list dalam satu dokumen (pola serupa)

---

## 1. Layout ASCII

### `/agenda` — Daftar Event

```
┌──────────────────────────────────────────────────────────────┐
│ AGENDA KEGIATAN                                              │
│ Tab: [ Mendatang ]  [ Lampau ]                               │
│ Filter komunitas: [Semua] [HIMSI] [BitSI] [Sibiner]          │
├──────────────────────────────────────────────────────────────┤
│ ┌──────────────────────────────────────────────────────────┐ │
│ │ ┌────────┐  SEMINAR KARIER SISTEM INFORMASI              │ │
│ │ │ poster │  📅 Sab, 12 Sep · 13.00–16.00                │ │
│ │ │ 16:9   │  📍 Auditorium UMKU                           │ │
│ │ └────────┘  Deskripsi singkat dua baris…                 │ │
│ │            badge: HIMSI                    [Detail →]    │ │
│ └──────────────────────────────────────────────────────────┘ │
│ ┌────────┐ ┌────────┐ ┌────────┐                            │
│ │ event  │ │ event  │ │ event  │   ← grid 3 kolom           │
│ └────────┘ └────────┘ └────────┘                            │
│              pagination                                     │
└──────────────────────────────────────────────────────────────┘
  Detail event `/agenda/[id]`: poster besar · deskripsi penuh ·
  waktu-lokasi · peta/link daring · tombol share
```

### `/galeri` — Index Album

```
┌──────────────────────────────────────────────────────────────┐
│ GALERI DOKUMENTASI                                           │
│ Momen-momen yang kami abadikan.                              │
├──────────────────────────────────────────────────────────────┤
│ ┌───────────────┐ ┌───────────────┐ ┌───────────────┐        │
│ │ [cover foto]  │ │ [cover foto]  │ │ [cover foto]  │        │
│ │               │ │               │ │               │        │
│ │ WORKSHOP IOT  │ │ SEMINAR       │ │ LOMBA UI/UX   │        │
│ │ 24 foto       │ │ KARIER 18 fto │ │ 31 foto       │        │
│ │ 15 Agu 2026   │ │ 02 Agu 2026   │ │ 20 Jul 2026   │        │
│ └───────────────┘ └───────────────┘ └───────────────┘        │
│   hover: cover zoom + overlay judul                          │
│              pagination                                      │
└──────────────────────────────────────────────────────────────┘
```

## 2. Component Tree

```
<AgendaPage>
├── <TabMendatangLampau>          query param ?waktu=
├── <KomunitasChips>
├── <EventCardFeatured>           event terdekat mendatang
├── <EventGrid> + <Pagination>
<GaleriIndexPage>
└── <AlbumCard[]>                 cover · judul · jumlah foto · tanggal
```

## 3. State Matrix

| Kondisi | Agenda | Galeri |
|---------|--------|--------|
| Empty mendatang | "Belum ada agenda — pantau IG kami 📷" + link | "Album menyusul" |
| Empty lampau | "Belum ada arsip kegiatan" | disembunyikan tab |
| Error API | inline retry per section (ADR L7 anti-blank) | sama |
| Filter tanpa hasil | empty state + reset chip | — |

## 4. UX Notes

- Tab mendatang default; event lampau berguna untuk arsip & nostalgia
- Badge komunitas berwarna: HIMSI cobalt · BitSI sky · Sibiner amber (satu bahasa visual lintas halaman)
- Kartu galeri memakai cover dari foto pertama album (bukan placeholder generik)
- Deep-link filter (`/agenda?komunitas=BITSI`) dipakai app komunitas untuk menuju agenda mereka

## 5. Responsive

| Breakpoint | Agenda | Galeri |
|-----------|--------|--------|
| `<640px` | kartu vertikal stack | grid 2 kolom |
| `≥1024px` | grid 3 kolom | grid 3-4 kolom |

---
*Terintegrasi:* PRD landing §3 · openapi `/publik/events`, `/publik/galeri/albums`
