@extends('demo.layout.app')
@section('content')

{{-- ─────────────────────────────────────────────────────────────
     GVI PREMIUM DASHBOARD  ·  Redesign v3
     Font: Poppins (already loaded in layout)
     ───────────────────────────────────────────────────────────── --}}

<style>
/* ── TOKENS ──────────────────────────────────────────────────── */
:root {
    --f: 'Poppins', system-ui, sans-serif;

    /* Surfaces */
    --page  : #f4f6fb;
    --card  : #ffffff;
    --glass : rgba(255,255,255,.08);
    --glass2: rgba(255,255,255,.14);

    /* Hero */
    --h1: #080e1c;
    --h2: #111827;

    /* Ink */
    --ink-1: #0d1526;
    --ink-2: #4b5675;
    --ink-3: #9ba4ba;

    /* Border */
    --line : #e8ecf5;
    --line2: rgba(255,255,255,.14);

    /* Accent palette */
    --indigo: #6366f1;
    --violet: #8b5cf6;
    --green : #10b981;
    --amber : #f59e0b;
    --red   : #ef4444;
    --cyan  : #06b6d4;
    --pink  : #ec4899;
    --gold  : #f59e0b;

    /* Shadows */
    --s0: 0 1px 2px rgba(13,21,38,.04);
    --s1: 0 2px 8px  rgba(13,21,38,.06), 0 1px 2px rgba(13,21,38,.04);
    --s2: 0 8px 24px rgba(13,21,38,.09), 0 2px 6px rgba(13,21,38,.05);
    --s3: 0 20px 50px rgba(13,21,38,.13);

    /* Radii */
    --r1: 10px;
    --r2: 16px;
    --r3: 24px;
    --r4: 32px;

    /* Motion */
    --ease: cubic-bezier(.22,.68,0,1.2);
    --ease2: cubic-bezier(.25,.46,.45,.94);
}

/* ── RESET ───────────────────────────────────────────────────── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

/* ── PAGE ────────────────────────────────────────────────────── */
.db-page {
    font-family: var(--f);
    background: radial-gradient(ellipse 1400px 700px at 70% 10%, #edf0f9, transparent 70%), var(--page);
    min-height: 100vh;
    color: var(--ink-1);
    -webkit-font-smoothing: antialiased;
}

/* ══════════════════════════════════════════════════════════════
   HERO
══════════════════════════════════════════════════════════════ */
.hero {
    background: var(--h1);
    position: relative;
    overflow: hidden;
    padding: 3rem 0 7rem;
}
.hero-inner-deco {
    position: absolute;
    bottom: -1px; left: 0; right: 0;
    height: 56px;
    background: radial-gradient(ellipse 1400px 700px at 70% 10%, #edf0f9, transparent 70%), var(--page);
    clip-path: polygon(0 100%, 100% 100%, 100% 0);
    z-index: 5;
}

/* Layered mesh background */
.hero::before {
    content: '';
    position: absolute; inset: 0;
    background:
        radial-gradient(ellipse 900px 500px at 70% 0%,   rgba(99,102,241,.22) 0%, transparent 65%),
        radial-gradient(ellipse 500px 400px at 0%  100%, rgba(139,92,246,.18) 0%, transparent 60%),
        radial-gradient(ellipse 700px 300px at 100% 60%, rgba(6,182,212,.1)   0%, transparent 55%);
    pointer-events: none;
}

/* Dot-grid texture */
.hero::after {
    content: '';
    position: absolute; inset: 0;
    background-image: radial-gradient(rgba(255,255,255,.06) 1px, transparent 1px);
    background-size: 28px 28px;
    pointer-events: none;
}

.hero-inner {
    max-width: 1300px;
    margin: 0 auto;
    padding: 0 2rem;
    position: relative;
    z-index: 1;
}

/* ── Hero top bar ── */
.hero-bar {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1.5rem;
    flex-wrap: wrap;
    margin-bottom: 2.75rem;
}

.hero-eyebrow {
    font-size: .7rem;
    font-weight: 600;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: #818cf8;
    margin-bottom: .6rem;
    display: flex;
    align-items: center;
    gap: .4rem;
}
.hero-eyebrow::before {
    content: '';
    display: inline-block;
    width: 18px; height: 2px;
    background: #818cf8;
    border-radius: 2px;
}

.hero-name {
    font-size: 2.1rem;
    font-weight: 800;
    color: #ffffff;
    letter-spacing: -.03em;
    line-height: 1.15;
    margin-bottom: .9rem;
}

.pkg-badge {
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    padding: .38rem 1.1rem;
    border-radius: 50px;
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .06em;
    text-transform: uppercase;
}
.pkg-badge.gold   { background: linear-gradient(135deg,#f59e0b,#d97706); color:#fff; box-shadow: 0 4px 16px rgba(245,158,11,.35); }
.pkg-badge.silver { background: linear-gradient(135deg,#6366f1,#4f46e5); color:#fff; box-shadow: 0 4px 16px rgba(99,102,241,.35); }

.hero-right {
    text-align: right;
    flex-shrink: 0;
}
.hero-date-label { font-size: .7rem; color: #6366f1; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; margin-bottom: .25rem; }
.hero-date-val   { font-size: .95rem; font-weight: 700; color: rgba(255,255,255,.9); }
.hero-tz         { font-size: .75rem; color: rgba(255,255,255,.4); margin-top: .15rem; }

/* ── KPI strip ── */
.kpi-strip {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
    gap: 1px;
    background: var(--line2);
    border-radius: var(--r2);
    overflow: hidden;
    border: 1px solid var(--line2);
    backdrop-filter: blur(20px);
}

.kpi-cell {
    background: var(--glass);
    padding: 1.35rem 1.6rem;
    transition: background .2s;
    position: relative;
    cursor: default;
}
.kpi-cell:hover { background: var(--glass2); }

.kpi-label {
    font-size: .65rem;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: rgba(255,255,255,.45);
    margin-bottom: .55rem;
}
.kpi-value {
    font-size: 1.55rem;
    font-weight: 800;
    color: #ffffff;
    letter-spacing: -.02em;
    line-height: 1;
    margin-bottom: .3rem;
}
.kpi-sub {
    font-size: .68rem;
    color: rgba(255,255,255,.4);
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: .3rem;
}
.kpi-sub i { color: #34d399; font-size: .6rem; }

/* ══════════════════════════════════════════════════════════════
   CONTENT WRAPPER  (pulls up over hero)
══════════════════════════════════════════════════════════════ */
.db-body {
    max-width: 1300px;
    margin: -4.5rem auto 3rem;
    padding: 0 2rem;
    position: relative;
    z-index: 10;
}

/* ══════════════════════════════════════════════════════════════
   ADMIN ALERT
══════════════════════════════════════════════════════════════ */
.admin-alert {
    display: flex;
    align-items: center;
    gap: 1.1rem;
    padding: 1.1rem 1.6rem;
    background: linear-gradient(135deg,#ef4444,#dc2626);
    border-radius: var(--r2);
    margin-bottom: 1.6rem;
    text-decoration: none;
    box-shadow: 0 8px 30px rgba(239,68,68,.28);
    animation: pulse-a 2.4s ease infinite;
}
@keyframes pulse-a {
    0%,100% { box-shadow: 0 8px 30px rgba(239,68,68,.28); }
    50%      { box-shadow: 0 8px 42px rgba(239,68,68,.50); }
}
.admin-alert .aa-icon { font-size: 1.6rem; flex-shrink: 0; }
.admin-alert .aa-msg strong { display: block; color:#fff; font-size: .95rem; font-weight: 700; }
.admin-alert .aa-msg span  { color: rgba(255,255,255,.8); font-size: .8rem; }
.admin-alert .aa-count {
    margin-left: auto;
    background: rgba(255,255,255,.22);
    color: #fff;
    border-radius: 50px;
    padding: .3rem 1rem;
    font-size: .85rem;
    font-weight: 800;
    flex-shrink: 0;
}

/* ══════════════════════════════════════════════════════════════
   SECTION LABEL
══════════════════════════════════════════════════════════════ */
.sec-label {
    font-size: .68rem;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: var(--ink-3);
    margin-bottom: 1rem;
    margin-top: .25rem;
    display: flex;
    align-items: center;
    gap: .5rem;
}
.sec-label::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--line);
}

/* ══════════════════════════════════════════════════════════════
   STAT CARDS
══════════════════════════════════════════════════════════════ */
.stat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
    gap: 1.1rem;
    margin-bottom: 1.6rem;
}

.s-card {
    background: var(--card);
    border-radius: var(--r2);
    border: 1px solid var(--line);
    box-shadow: var(--s1);
    padding: 1.6rem;
    display: flex;
    flex-direction: column;
    gap: .8rem;
    text-decoration: none;
    color: inherit;
    transition: transform .22s var(--ease2), box-shadow .22s var(--ease2), border-color .22s;
    position: relative;
    overflow: hidden;
}
.s-card::after {
    content: '';
    position: absolute;
    top: 0; left: 1.5rem; right: 1.5rem;
    height: 2.5px;
    border-radius: 0 0 4px 4px;
    background: var(--accent, var(--indigo));
    opacity: 0;
    transition: opacity .22s;
}
.s-card:hover { transform: translateY(-3px); box-shadow: var(--s2); border-color: #d0d8f0; }
.s-card:hover::after { opacity: 1; }

.s-card-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: .5rem;
}
.s-icon {
    width: 40px; height: 40px;
    border-radius: 11px;
    display: flex; align-items: center; justify-content: center;
    font-size: .95rem;
    flex-shrink: 0;
    background: var(--icon-bg, rgba(99,102,241,.1));
    color: var(--accent, var(--indigo));
}

.s-label { font-size: .7rem; font-weight: 600; color: var(--ink-3); letter-spacing: .06em; text-transform: uppercase; }
.s-value { font-size: 1.75rem; font-weight: 800; color: var(--ink-1); letter-spacing: -.03em; line-height: 1; }
.s-note  { font-size: .72rem; color: var(--ink-3); display: flex; align-items: center; gap: .3rem; font-weight: 500; }
.s-note .up   { color: #10b981; }
.s-note .down { color: #ef4444; }

/* Per-colour hover glow ring */
.sc--indigo:hover { box-shadow: var(--s2), 0 0 0 3px rgba(99,102,241,.14) !important; }
.sc--green:hover  { box-shadow: var(--s2), 0 0 0 3px rgba(16,185,129,.14) !important; }
.sc--amber:hover  { box-shadow: var(--s2), 0 0 0 3px rgba(245,158,11,.14) !important; }
.sc--red:hover    { box-shadow: var(--s2), 0 0 0 3px rgba(239,68,68,.14)  !important; }
.sc--cyan:hover   { box-shadow: var(--s2), 0 0 0 3px rgba(6,182,212,.14)  !important; }
.sc--violet:hover { box-shadow: var(--s2), 0 0 0 3px rgba(139,92,246,.14) !important; }
.sc--pink:hover   { box-shadow: var(--s2), 0 0 0 3px rgba(236,72,153,.14) !important; }
.sc--gold:hover   { box-shadow: var(--s2), 0 0 0 3px rgba(245,158,11,.14) !important; }

/* Colour tokens per card */
.sc--indigo { --accent:#6366f1; --icon-bg:rgba(99,102,241,.1); }
.sc--green  { --accent:#10b981; --icon-bg:rgba(16,185,129,.1); }
.sc--amber  { --accent:#f59e0b; --icon-bg:rgba(245,158,11,.1); }
.sc--red    { --accent:#ef4444; --icon-bg:rgba(239,68,68,.1);  }
.sc--cyan   { --accent:#06b6d4; --icon-bg:rgba(6,182,212,.1);  }
.sc--violet { --accent:#8b5cf6; --icon-bg:rgba(139,92,246,.1); }
.sc--pink   { --accent:#ec4899; --icon-bg:rgba(236,72,153,.1); }
.sc--gold   { --accent:#f59e0b; --icon-bg:rgba(245,158,11,.1); }

/* ══════════════════════════════════════════════════════════════
   PANEL CARD  (reusable wrapper)
══════════════════════════════════════════════════════════════ */
.panel {
    background: var(--card);
    border: 1px solid var(--line);
    border-radius: var(--r3);
    box-shadow: var(--s1);
    margin-bottom: 1.6rem;
}
.panel-head {
    padding: 1.5rem 2rem 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: .75rem;
}
.panel-title {
    font-size: 1rem;
    font-weight: 700;
    color: var(--ink-1);
    letter-spacing: -.015em;
}
.panel-body { padding: 1.5rem 2rem 2rem; }

/* Filter tabs */
.ftabs {
    display: flex;
    gap: .3rem;
    background: #f1f5f9;
    padding: .25rem;
    border-radius: var(--r1);
}
.ftab {
    padding: .32rem .8rem;
    border-radius: 7px;
    border: none;
    background: transparent;
    font-size: .75rem;
    font-weight: 600;
    color: var(--ink-2);
    cursor: pointer;
    font-family: var(--f);
    transition: all .18s;
}
.ftab.active, .ftab:hover {
    background: #fff;
    color: var(--indigo);
    box-shadow: 0 1px 4px rgba(13,21,38,.1);
}

/* ══════════════════════════════════════════════════════════════
   ROI CARDS  (2-col)
══════════════════════════════════════════════════════════════ */
.roi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.1rem;
    margin-bottom: 1.6rem;
}

.roi-card {
    background: var(--card);
    border: 1px solid var(--line);
    border-radius: var(--r2);
    box-shadow: var(--s1);
    padding: 1.75rem;
    transition: transform .22s var(--ease2), box-shadow .22s;
}
.roi-card:hover { transform: translateY(-3px); box-shadow: var(--s2); }

.roi-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
    gap: .5rem;
}
.roi-label {
    font-size: .82rem;
    font-weight: 700;
    color: var(--ink-1);
    display: flex;
    align-items: center;
    gap: .5rem;
}
.roi-label .ri {
    width: 34px; height: 34px;
    border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    font-size: .85rem;
}
.roi-badge {
    font-size: .65rem;
    font-weight: 700;
    letter-spacing: .06em;
    text-transform: uppercase;
    padding: .28rem .75rem;
    border-radius: 50px;
}
.badge-on   { background: rgba(16,185,129,.1);  color: #059669; }
.badge-done { background: rgba(239,68,68,.1);   color: #dc2626; }
.badge-off  { background: rgba(245,158,11,.1);  color: #d97706; }

/* slim progress */
.slim-track {
    height: 7px;
    background: #f1f5f9;
    border-radius: 99px;
    overflow: hidden;
    margin-bottom: 1.4rem;
}
.slim-fill {
    height: 100%;
    border-radius: 99px;
    transition: width 1.1s ease;
}
.fill-indigo { background: linear-gradient(90deg,#6366f1,#8b5cf6); }
.fill-red    { background: linear-gradient(90deg,#ef4444,#dc2626); }
.fill-amber  { background: linear-gradient(90deg,#f59e0b,#d97706); }

.roi-stats {
    display: grid;
    grid-template-columns: repeat(3,1fr);
    gap: .75rem;
    padding-top: 1.1rem;
    border-top: 1px solid var(--line);
}
.rs-val { font-size: 1.15rem; font-weight: 800; color: var(--ink-1); letter-spacing: -.02em; }
.rs-lbl { font-size: .65rem; color: var(--ink-3); font-weight: 600; text-transform: uppercase; letter-spacing: .06em; margin-top: .2rem; }

.roi-notice {
    display: flex;
    align-items: center;
    gap: .6rem;
    margin-top: 1.25rem;
    padding: .75rem 1rem;
    border-radius: var(--r1);
    font-size: .78rem;
    font-weight: 600;
}
.notice-green  { background: rgba(16,185,129,.07);  color: #059669; }
.notice-red    { background: rgba(239,68,68,.07);   color: #dc2626; }
.notice-amber  { background: rgba(245,158,11,.07);  color: #d97706; }

/* ══════════════════════════════════════════════════════════════
   ANNOUNCEMENT BANNER
══════════════════════════════════════════════════════════════ */
.ann-banner {
    background: var(--h1);
    border-radius: var(--r3);
    padding: 2.5rem;
    margin-bottom: 1.6rem;
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(255,255,255,.07);
}
.ann-banner::before {
    content: '';
    position: absolute; inset: 0;
    background:
        radial-gradient(ellipse 600px 300px at 100% 50%, rgba(99,102,241,.2) 0%, transparent 60%),
        radial-gradient(ellipse 300px 300px at 0% 0%,    rgba(139,92,246,.15) 0%, transparent 55%);
    pointer-events: none;
}
.ann-banner::after {
    content: '🎯';
    position: absolute;
    right: 2.5rem; top: 50%;
    transform: translateY(-50%);
    font-size: 7rem;
    opacity: .09;
    pointer-events: none;
}
.ann-inner { position: relative; z-index: 1; }
.ann-label {
    font-size: .68rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase;
    color: #818cf8; margin-bottom: .6rem;
}
.ann-title { font-size: 1.5rem; font-weight: 800; color: #fff; letter-spacing: -.02em; margin-bottom: .5rem; }
.ann-desc  { font-size: .85rem; color: rgba(255,255,255,.55); margin-bottom: 1.75rem; max-width: 520px; line-height: 1.6; }

.ann-chips { display: flex; gap: 1rem; flex-wrap: wrap; }
.ann-chip {
    background: rgba(255,255,255,.07);
    border: 1px solid rgba(255,255,255,.12);
    border-radius: var(--r1);
    padding: 1rem 1.5rem;
    min-width: 120px;
    text-align: center;
    backdrop-filter: blur(10px);
    transition: background .2s;
}
.ann-chip:hover { background: rgba(255,255,255,.12); }
.ann-chip-val { font-size: 1.4rem; font-weight: 800; color: rgba(255,255,255,.9); letter-spacing: -.02em; }
.ann-chip-lbl { font-size: .65rem; color: rgba(255,255,255,.45); font-weight: 600; text-transform: uppercase; letter-spacing: .08em; margin-top: .3rem; }
.ann-chip.hi  .ann-chip-val { color: #fde68a; }

/* ══════════════════════════════════════════════════════════════
   CIRCULAR PROGRESS TARGETS
══════════════════════════════════════════════════════════════ */
.target-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.1rem;
    margin-bottom: 1.6rem;
}
.t-card {
    background: var(--card);
    border: 1px solid var(--line);
    border-radius: var(--r2);
    box-shadow: var(--s1);
    padding: 2rem;
    text-align: center;
    transition: transform .22s var(--ease2), box-shadow .22s;
}
.t-card:hover { transform: translateY(-3px); box-shadow: var(--s2); }
.t-card h3 { font-size: .95rem; font-weight: 700; color: var(--ink-1); margin-bottom: .3rem; letter-spacing: -.01em; }
.t-card p  { font-size: .77rem; color: var(--ink-3); margin-bottom: 1.75rem; line-height: 1.5; }

.ring-wrap {
    position: relative;
    width: 168px; height: 168px;
    margin: 0 auto 1.75rem;
}
.ring-wrap svg { width: 100%; height: 100%; transform: rotate(-90deg); }
.ring-bg   { fill: none; stroke: #f1f5f9; stroke-width: 9; }
.ring-fill { fill: none; stroke: var(--indigo); stroke-width: 9; stroke-linecap: round; transition: stroke-dasharray 1.3s ease; }
.ring-center {
    position: absolute; inset: 0;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
}
.ring-pct { font-size: 1.9rem; font-weight: 900; color: var(--ink-1); letter-spacing: -.04em; line-height: 1; }
.ring-sub { font-size: .65rem; font-weight: 600; color: var(--ink-3); text-transform: uppercase; letter-spacing: .08em; margin-top: .3rem; }

.t-btn {
    width: 100%;
    padding: .8rem;
    border: none;
    border-radius: var(--r1);
    font-family: var(--f);
    font-size: .82rem;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: .45rem;
    transition: opacity .2s, transform .2s;
}
.t-btn:hover { opacity: .88; transform: translateY(-1px); }
.t-btn-i { background: linear-gradient(135deg,#6366f1,#8b5cf6); color: #fff; }
.t-btn-v { background: linear-gradient(135deg,#8b5cf6,#6d28d9); color: #fff; }

/* ══════════════════════════════════════════════════════════════
   DETAIL PANELS  (expandable)
══════════════════════════════════════════════════════════════ */
.d-panel {
    background: var(--card);
    border: 1px solid var(--line);
    border-radius: var(--r3);
    box-shadow: var(--s1);
    margin-bottom: 1.6rem;
    display: none;
}
.d-panel.open { display: block; animation: fadeUp .3s var(--ease2); }
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(12px); }
    to   { opacity: 1; transform: translateY(0); }
}
.d-panel-head {
    padding: 1.5rem 2rem;
    border-bottom: 1px solid var(--line);
    display: flex;
    align-items: center;
    gap: .7rem;
    font-size: .95rem;
    font-weight: 700;
    color: var(--ink-1);
    letter-spacing: -.015em;
}
.d-panel-head .dph-icon {
    width: 36px; height: 36px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: .9rem;
}
.d-panel-body { padding: 2rem; }

/* Level rows */
.lv-row {
    display: grid;
    grid-template-columns: 46px 1fr 160px 90px;
    gap: 1rem;
    align-items: center;
    padding: 1rem 1.25rem;
    border-radius: var(--r1);
    border: 1px solid transparent;
    margin-bottom: .55rem;
    transition: background .18s, border-color .18s;
}
.lv-row:hover { background: #f8faff; border-color: #d4d8f7; }

.lv-num {
    width: 46px; height: 46px;
    border-radius: 12px;
    background: linear-gradient(135deg,#6366f1,#8b5cf6);
    color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-weight: 800; font-size: 1.05rem;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(99,102,241,.3);
}
.lv-info h6 { font-size: .85rem; font-weight: 700; color: var(--ink-1); margin-bottom: .15rem; }
.lv-info p  { font-size: .72rem; color: var(--ink-3); }
.lv-bar-wrap { }
.lv-track {
    height: 6px;
    background: #f1f5f9;
    border-radius: 99px;
    overflow: hidden;
    margin-bottom: .3rem;
}
.lv-fill {
    height: 100%;
    border-radius: 99px;
    background: linear-gradient(90deg, #6366f1, #8b5cf6);
}
.lv-pct { font-size: .65rem; color: var(--ink-3); font-weight: 600; }
.lv-count { font-size: .9rem; font-weight: 800; color: var(--ink-1); text-align: right; }
.lv-count span { font-size: .7rem; color: var(--ink-3); font-weight: 500; }

/* Rank cards */
.rank-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(145px,1fr));
    gap: 1rem;
}
.rank-item {
    text-align: center;
    padding: 1.5rem 1rem;
    border-radius: var(--r2);
    border: 1.5px solid var(--line);
    background: var(--page);
    transition: all .22s var(--ease2);
}
.rank-item:hover { transform: translateY(-3px); box-shadow: var(--s2); background: var(--card); }
.rank-item.won { border-color: #86efac; background: #f0fdf4; }
.rank-img {
    width: 56px; height: 56px;
    object-fit: contain;
    display: block;
    margin: 0 auto .75rem;
    filter: drop-shadow(0 4px 10px rgba(0,0,0,.12));
}
.rank-name { font-size: .82rem; font-weight: 700; color: var(--ink-1); margin-bottom: .2rem; }
.rank-req  { font-size: .67rem; color: var(--ink-3); margin-bottom: .75rem; line-height: 1.4; }
.rank-track {
    height: 5px; background: #e8ecf5; border-radius: 99px; overflow: hidden; margin-bottom: .45rem;
}
.rank-fill { height: 100%; border-radius: 99px; background: linear-gradient(90deg,#6366f1,#8b5cf6); }
.rank-count { font-size: .68rem; color: var(--ink-3); font-weight: 600; }
.rank-done  {
    display: inline-flex; align-items: center; gap: .25rem;
    background: #dcfce7; color: #059669;
    border-radius: 50px; padding: .18rem .6rem;
    font-size: .65rem; font-weight: 700;
    margin-top: .4rem;
}

/* ══════════════════════════════════════════════════════════════
   EID MUBARAK BANNER — Premium Islamic Design v2
══════════════════════════════════════════════════════════════ */
.eid-wrap {
    position: relative;
    border-radius: var(--r3);
    overflow: hidden;
    margin-bottom: 1.6rem;
    background: #020d06;
    border: 1px solid rgba(212,175,55,.12);
    box-shadow:
        0 20px 70px rgba(0,20,10,.6),
        0 0 0 1px rgba(212,175,55,.06),
        inset 0 1px 0 rgba(212,175,55,.1);
}

/* Layered background */
.eid-wrap::before {
    content: '';
    position: absolute; inset: 0;
    background:
        radial-gradient(ellipse 700px 500px at 18% 50%, rgba(5,80,35,.95) 0%, transparent 65%),
        radial-gradient(ellipse 500px 400px at 85% 5%,  rgba(3,60,28,.82) 0%, transparent 60%),
        radial-gradient(ellipse 350px 260px at 100% 95%,rgba(1,35,16,.90) 0%, transparent 52%);
    pointer-events: none;
}

/* Geometric lattice overlay */
.eid-wrap::after {
    content: '';
    position: absolute; inset: 0;
    background-image:
        repeating-linear-gradient( 60deg, rgba(212,175,55,.032) 0, rgba(212,175,55,.032) 1px, transparent 1px, transparent 26px),
        repeating-linear-gradient(-60deg, rgba(212,175,55,.032) 0, rgba(212,175,55,.032) 1px, transparent 1px, transparent 26px);
    pointer-events: none;
}

/* ── Layout ── */
.eid-inner {
    position: relative; z-index: 2;
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 2.5rem;
    align-items: center;
    padding: 2rem 2.5rem;
}

/* Top ornament line */
.eid-topline {
    display: flex; align-items: center; gap: .65rem;
    margin-bottom: .9rem;
}
.eid-topline-line {
    height: 1px; flex: 1; max-width: 52px;
    background: linear-gradient(90deg, rgba(212,175,55,.6), transparent);
}
.eid-topline-line.rev { background: linear-gradient(270deg, rgba(212,175,55,.6), transparent); }
.eid-topline-dot {
    width: 5px; height: 5px; border-radius: 50%;
    background: #d4af37; box-shadow: 0 0 7px rgba(212,175,55,.8);
}
.eid-topline-text {
    font-size: .6rem; font-weight: 700;
    letter-spacing: .16em; text-transform: uppercase;
    color: rgba(212,175,55,.82);
}

/* Text */
.eid-arabic {
    font-size: 2.6rem; font-weight: 900; color: #d4af37;
    letter-spacing: .05em; line-height: 1; margin-bottom: .35rem;
    text-shadow: 0 0 32px rgba(212,175,55,.5), 0 0 75px rgba(212,175,55,.18);
}
.eid-title {
    font-size: .95rem; font-weight: 700; color: rgba(255,255,255,.88);
    letter-spacing: .06em; text-transform: uppercase; margin-bottom: 1.1rem;
}
.eid-msg {
    font-size: .8rem; color: rgba(255,255,255,.5);
    line-height: 1.75; max-width: 470px; margin-bottom: 1.25rem;
    border-left: 2px solid rgba(212,175,55,.28); padding-left: .95rem;
}
.eid-dua {
    display: inline-flex; align-items: flex-start; gap: .4rem;
    font-size: .76rem; font-weight: 600; font-style: italic;
    color: rgba(212,175,55,.8);
    background: rgba(212,175,55,.065);
    border: 1px solid rgba(212,175,55,.18);
    border-radius: var(--r1); padding: .5rem 1rem; line-height: 1.5;
}

/* ── Right art column ── */
.eid-art {
    flex-shrink: 0;
    display: flex; flex-direction: column;
    align-items: center; gap: .75rem;
}

/* SVG moon — mask-based crescent, no background colour bleed */
.eid-moon-svg {
    width: 130px; height: 130px; overflow: visible;
    animation: eid-moonpulse 4s ease-in-out infinite;
}
@keyframes eid-moonpulse {
    0%,100% { filter: drop-shadow(0 0 16px rgba(212,175,55,.45)) drop-shadow(0 0 40px rgba(212,175,55,.18)); }
    50%      { filter: drop-shadow(0 0 26px rgba(212,175,55,.65)) drop-shadow(0 0 65px rgba(212,175,55,.28)); }
}

/* Stars row */
.eid-star-row { display: flex; gap: .45rem; align-items: center; justify-content: center; }
.eid-star-css {
    background: #d4af37;
    clip-path: polygon(50% 0%,61.8% 38.2%,100% 38.2%,69.1% 61.8%,80.9% 100%,50% 76.4%,19.1% 100%,30.9% 61.8%,0% 38.2%,38.2% 38.2%);
    animation: eid-startwinkle 2.4s ease-in-out infinite;
}
.eid-star-css:nth-child(2) { animation-delay: -.9s; }
.eid-star-css:nth-child(3) { animation-delay: -1.7s; }
@keyframes eid-startwinkle {
    0%,100% { opacity:.85; transform: scale(1) rotate(0deg); }
    50%      { opacity:.3;  transform: scale(.6) rotate(20deg); }
}
.eid-star-lg { width:22px; height:22px; }
.eid-star-md { width:14px; height:14px; opacity:.78; }
.eid-star-sm { width: 9px; height: 9px;  opacity:.55; }

/* Sparkles */
.eid-sparkles { position: absolute; inset: 0; pointer-events: none; z-index: 1; overflow: hidden; }
.eid-sparkle  {
    position: absolute; border-radius: 50%;
    background: #d4af37; opacity: 0;
    animation: eid-rise var(--dur,3s) ease-in-out var(--del,0s) infinite;
}
@keyframes eid-rise {
    0%   { opacity:0; transform: translateY(0) scale(1); }
    35%  { opacity:.7; }
    100% { opacity:0; transform: translateY(-52px) scale(.3); }
}

/* Bottom gold line */
.eid-divider {
    width: 100%; height: 2px;
    background: linear-gradient(90deg, transparent 0%, rgba(212,175,55,.42) 28%, rgba(212,175,55,.88) 50%, rgba(212,175,55,.42) 72%, transparent 100%);
    position: relative; z-index: 2;
}

/* Responsive */
@media (max-width: 700px) {
    .eid-inner  { grid-template-columns: 1fr; padding: 1.75rem 1.5rem; }
    .eid-art    { display: none; }
    .eid-arabic { font-size: 2rem; }
}


/* ══════════════════════════════════════════════════════════════
   RESPONSIVE
══════════════════════════════════════════════════════════════ */
@media (max-width: 900px) {
    .hero { padding: 2rem 0 5.5rem; }
    .hero-name { font-size: 1.7rem; }
    .kpi-strip { grid-template-columns: repeat(3,1fr); }
    .db-body { margin-top: -3.5rem; padding: 0 1rem; }
}
@media (max-width: 640px) {
    .kpi-strip { grid-template-columns: repeat(2,1fr); }
    .stat-grid  { grid-template-columns: repeat(2,1fr); }
    .lv-row { grid-template-columns: 40px 1fr; }
    .lv-bar-wrap, .lv-count { grid-column: 1/-1; }
    .ann-banner { padding: 1.75rem; }
    .ann-title { font-size: 1.2rem; }
}
@media (max-width: 420px) {
    .kpi-strip { grid-template-columns: 1fr 1fr; }
    .stat-grid  { grid-template-columns: 1fr; }
}
</style>

{{-- ══════════════════════════════════════════════════
     SUBHEADER (Metronic default)
══════════════════════════════════════════════════ --}}
<div class="py-2 subheader py-lg-4 subheader-solid" id="kt_subheader">
    <div class="flex-wrap container-fluid d-flex align-items-center justify-content-between flex-sm-nowrap">
        <div class="flex-wrap mr-2 d-flex align-items-center gap-2">
            <h5 class="mt-2 mb-2 mr-5 text-dark font-weight-bold">Dashboard</h5>
            <div class="mt-2 mb-2 mr-4 bg-gray-200 subheader-separator subheader-separator-ver"></div>
            <span class="mr-2 text-muted font-weight-bold font-size-sm">Available Balance</span>
            <strong class="font-size-sm">${{ Auth::user()->roi_eligible_investment_amount }}</strong>
            @role('admin')
            <div class="mt-2 mb-2 mx-4 bg-gray-200 subheader-separator subheader-separator-ver"></div>
            <span class="mr-2 text-muted font-weight-bold font-size-sm">Server Time</span>
            <strong class="font-size-sm">{{ now()->format('h:i A') }} · {{ config('app.timezone') }}</strong>
            @endrole
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════
     PAGE
══════════════════════════════════════════════════ --}}
<div class="db-page">

    {{-- ─── HERO ───────────────────────────────── --}}
    <div class="hero">
        <div class="hero-inner">

            {{-- top bar --}}
            <div class="hero-bar">
                <div>
                    <div class="hero-eyebrow">Global Visioners International</div>
                    <div class="hero-name">Welcome back, {{ Auth::user()->name }}</div>
                    @if($data['user_plan'] === 'vip')
                        <span class="pkg-badge gold"><i class="fas fa-crown"></i>&nbsp;VIP Gold Package</span>
                    @else
                        <span class="pkg-badge silver"><i class="fas fa-gem"></i>&nbsp;VIP Silver Package</span>
                    @endif
                </div>
                <div class="hero-right">
                    <div class="hero-date-label">Today</div>
                    <div class="hero-date-val">{{ now()->format('l, M d Y') }}</div>
                    @role('admin')
                    <div class="hero-tz">{{ now()->format('h:i A') }} · {{ config('app.timezone') }}</div>
                    @endrole
                </div>
            </div>

            {{-- KPI strip --}}
            <div class="kpi-strip">
                <div class="kpi-cell">
                    <div class="kpi-label">Total Earnings</div>
                    <div class="kpi-value">${{ number_format($data['total_earning'], 2) }}</div>
                    <div class="kpi-sub"><i class="fas fa-arrow-up"></i> All-time</div>
                </div>
                <div class="kpi-cell">
                    <div class="kpi-label">Online Wallet</div>
                    <div class="kpi-value">${{ number_format($data['online_wallet'], 2) }}</div>
                    <div class="kpi-sub"><i class="fas fa-circle" style="font-size:.5rem;color:#34d399"></i> Available</div>
                </div>
                <div class="kpi-cell">
                    <div class="kpi-label">ROI Earnings</div>
                    <div class="kpi-value">${{ number_format($data['roi'], 2) }}</div>
                    <div class="kpi-sub"><i class="fas fa-arrow-up"></i> Return</div>
                </div>
                <div class="kpi-cell">
                    <div class="kpi-label">Team Size</div>
                    <div class="kpi-value">{{ number_format($data['totalTeam']) }}</div>
                    <div class="kpi-sub"><i class="fas fa-users" style="color:#34d399;font-size:.65rem"></i> Members</div>
                </div>
                <div class="kpi-cell">
                    <div class="kpi-label">Your Rank</div>
                    <div class="kpi-value" style="font-size:1.2rem;letter-spacing:0">VISIONER</div>
                    <div class="kpi-sub"><i class="fas fa-star" style="color:#fbbf24;font-size:.65rem"></i> Active status</div>
                </div>
            </div>

        </div>
        <div class="hero-inner-deco"></div>
    </div>

    {{-- ─── BODY ───────────────────────────────── --}}
    <div class="db-body">

        {{-- Admin Alert --}}
        @role('admin')
        @if($data['missed_roi_count'] > 0)
        <a href="{{ route('roi.submission.monitoring') }}" class="admin-alert">
            <div class="aa-icon">⚠️</div>
            <div class="aa-msg">
                <strong>ROI Submissions Missing Today</strong>
                <span>Click to review users who have not received their ROI distribution</span>
            </div>
            <div class="aa-count">{{ $data['missed_roi_count'] }} Users</div>
        </a>
        @endif
        @endrole

        {{-- ─── EID MUBARAK BANNER ─────────────────────────── --}}
        <div class="eid-wrap">

            <div class="eid-sparkles">
                <div class="eid-sparkle" style="left:6%;   top:52%; width:3px; height:3px; --dur:3.2s; --del:.0s"></div>
                <div class="eid-sparkle" style="left:13%;  top:32%; width:2px; height:2px; --dur:2.6s; --del:.7s"></div>
                <div class="eid-sparkle" style="left:22%;  top:65%; width:4px; height:4px; --dur:3.8s; --del:1.2s"></div>
                <div class="eid-sparkle" style="left:34%;  top:22%; width:2px; height:2px; --dur:2.9s; --del:.4s"></div>
                <div class="eid-sparkle" style="left:47%;  top:72%; width:3px; height:3px; --dur:3.5s; --del:.9s"></div>
                <div class="eid-sparkle" style="left:58%;  top:38%; width:2px; height:2px; --dur:4.1s; --del:.2s"></div>
                <div class="eid-sparkle" style="left:70%;  top:55%; width:3px; height:3px; --dur:3.0s; --del:1.5s"></div>
                <div class="eid-sparkle" style="left:80%;  top:28%; width:4px; height:4px; --dur:2.7s; --del:.6s"></div>
                <div class="eid-sparkle" style="left:90%;  top:62%; width:2px; height:2px; --dur:3.6s; --del:1.0s"></div>
                <div class="eid-sparkle" style="left:18%;  top:80%; width:3px; height:3px; --dur:2.4s; --del:.3s"></div>
                <div class="eid-sparkle" style="left:64%;  top:18%; width:2px; height:2px; --dur:3.3s; --del:1.8s"></div>
                <div class="eid-sparkle" style="left:76%;  top:76%; width:3px; height:3px; --dur:2.8s; --del:.8s"></div>
            </div>

            <div class="eid-inner">

                {{-- Left: Text --}}
                <div>
                    <div class="eid-topline">
                        <div class="eid-topline-line"></div>
                        <div class="eid-topline-dot"></div>
                        <div class="eid-topline-text">Global Visioners International</div>
                        <div class="eid-topline-dot"></div>
                        <div class="eid-topline-line rev"></div>
                    </div>

                    <div class="eid-arabic">عيد مبارك</div>
                    <div class="eid-title">Eid Mubarak &mdash; Happy Eid al-Fitr 1446 AH</div>

                    <div class="eid-msg">
                        May the blessings of Allah fill your life with happiness, your heart with love, and your soul with peace. On this blessed occasion, Global Visioners International extends its warmest greetings to you and your entire family. May Allah accept your fasts, prayers, and every good deed.
                    </div>

                    <div class="eid-dua">
                        <span>&#x275D;</span>
                        Taqabbal Allahu minna wa minkum &mdash; May Allah accept from us and from you
                        <span>&#x275E;</span>
                    </div>
                </div>

                {{-- Right: SVG Moon Art (mask-based, no black-dot issue) --}}
                <div class="eid-art">
                    <svg class="eid-moon-svg" viewBox="0 0 130 130" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <radialGradient id="moonG" cx="36%" cy="33%" r="66%">
                                <stop offset="0%"   stop-color="#f9f0a0"/>
                                <stop offset="28%"  stop-color="#e8c84a"/>
                                <stop offset="58%"  stop-color="#d4af37"/>
                                <stop offset="84%"  stop-color="#b8860b"/>
                                <stop offset="100%" stop-color="#7a5800"/>
                            </radialGradient>
                            <mask id="moonM">
                                <circle cx="65" cy="65" r="52" fill="white"/>
                                <circle cx="86" cy="55" r="46" fill="black"/>
                            </mask>
                        </defs>
                        <!-- Soft glow halos (not masked, always visible) -->
                        <circle cx="65" cy="65" r="62" fill="rgba(212,175,55,.04)"/>
                        <circle cx="65" cy="65" r="56" fill="rgba(212,175,55,.07)"/>
                        <!-- The crescent: SVG mask clips a circle, no background-colour seam -->
                        <circle cx="65" cy="65" r="52" fill="url(#moonG)" mask="url(#moonM)"/>
                        <!-- Bright limb highlight -->
                        <path d="M 36,38 Q 24,65 36,92" stroke="rgba(255,252,200,.22)" stroke-width="3.5" fill="none" stroke-linecap="round"/>
                    </svg>

                    <div class="eid-star-row">
                        <div class="eid-star-css eid-star-lg"></div>
                        <div class="eid-star-css eid-star-md"></div>
                        <div class="eid-star-css eid-star-sm"></div>
                    </div>
                </div>

            </div>

            <div class="eid-divider"></div>
        </div>
        {{-- ─── END EID BANNER ─────────────────────────────── --}}

        {{-- Section: Wallets --}}
        <div class="sec-label">Wallet Overview</div>
        <div class="stat-grid">

            <div class="s-card sc--indigo">
                <div class="s-card-top">
                    <div class="s-label">Total Earnings</div>
                    <div class="s-icon"><i class="fas fa-coins"></i></div>
                </div>
                <div class="s-value">${{ number_format($data['total_earning'], 2) }}</div>
                <div class="s-note"><span class="up"><i class="fas fa-arrow-up"></i></span> All-time cumulative</div>
            </div>

            <div class="s-card sc--green">
                <div class="s-card-top">
                    <div class="s-label">Online Wallet</div>
                    <div class="s-icon"><i class="fas fa-wallet"></i></div>
                </div>
                <div class="s-value">${{ number_format($data['online_wallet'], 2) }}</div>
                <div class="s-note"><i class="fas fa-circle" style="font-size:.5rem;color:#10b981"></i> Available balance</div>
            </div>

            <div class="s-card sc--indigo">
                <div class="s-card-top">
                    <div class="s-label">ROI Earnings</div>
                    <div class="s-icon"><i class="fas fa-chart-line"></i></div>
                </div>
                <div class="s-value">${{ number_format($data['roi'], 2) }}</div>
                <div class="s-note"><span class="up"><i class="fas fa-arrow-up"></i></span> Return on investment</div>
            </div>

            <div class="s-card sc--amber">
                <div class="s-card-top">
                    <div class="s-label">Direct / Indirect</div>
                    <div class="s-icon"><i class="fas fa-users"></i></div>
                </div>
                <div class="s-value">${{ number_format($data['direct_indirect'], 2) }}</div>
                <div class="s-note"><span class="up"><i class="fas fa-arrow-up"></i></span> Commission income</div>
            </div>

            <div class="s-card sc--cyan">
                <div class="s-card-top">
                    <div class="s-label">Team Size</div>
                    <div class="s-icon"><i class="fas fa-network-wired"></i></div>
                </div>
                <div class="s-value">{{ number_format($data['totalTeam']) }}</div>
                <div class="s-note"><i class="fas fa-user-plus" style="color:#06b6d4"></i> Active network</div>
            </div>

            <div class="s-card sc--red">
                <div class="s-card-top">
                    <div class="s-label">Profit Sharing</div>
                    <div class="s-icon"><i class="fas fa-chart-pie"></i></div>
                </div>
                <div class="s-value">${{ number_format($data['profit_share'], 2) }}</div>
                <div class="s-note"><i class="fas fa-calendar" style="color:#ef4444"></i> Monthly distribution</div>
            </div>

            <div class="s-card sc--violet">
                <div class="s-card-top">
                    <div class="s-label">Rewards Earned</div>
                    <div class="s-icon"><i class="fas fa-gift"></i></div>
                </div>
                <div class="s-value">${{ number_format($data['rewardWallet'], 2) }}</div>
                <div class="s-note"><i class="fas fa-trophy" style="color:#8b5cf6"></i> Achievement bonuses</div>
            </div>

            <a href="{{ route('wallets.incentive') }}" class="s-card sc--gold" style="text-decoration:none">
                <div class="s-card-top">
                    <div class="s-label">Designation Incentive</div>
                    <div class="s-icon"><i class="fas fa-star"></i></div>
                </div>
                <div class="s-value">${{ number_format($data['designation_incentive'], 2) }}</div>
                <div class="s-note"><i class="fas fa-external-link-alt" style="color:#f59e0b;font-size:.6rem"></i> View details</div>
            </a>

            <div class="s-card sc--green">
                <div class="s-card-top">
                    <div class="s-label">Your Rank</div>
                    <div class="s-icon"><i class="fas fa-crown"></i></div>
                </div>
                <div class="s-value" style="font-size:1.3rem;letter-spacing:0">VISIONER</div>
                <div class="s-note"><i class="fas fa-star" style="color:#f59e0b"></i> Active member status</div>
            </div>

        </div>

        {{-- Section: Chart --}}
        <div class="sec-label">Business Analytics</div>
        <div class="panel">
            <div class="panel-head">
                <div class="panel-title">Performance Overview</div>
                <div class="ftabs">
                    <button class="ftab active" data-period="7d">7D</button>
                    <button class="ftab" data-period="30d">30D</button>
                    <button class="ftab" data-period="90d">3M</button>
                    <button class="ftab" data-period="1y">1Y</button>
                </div>
            </div>
            <div class="panel-body">
                <div id="businessChart" style="min-height:360px;"></div>
            </div>
        </div>

        {{-- Section: ROI --}}
        <div class="sec-label">ROI &amp; Withdrawal Control</div>
        <div class="roi-grid">

            {{-- 2X --}}
            <div class="roi-card">
                <div class="roi-top">
                    <div class="roi-label">
                        <div class="ri" style="background:rgba(99,102,241,.1);color:#6366f1"><i class="fas fa-chart-bar"></i></div>
                        2X ROI Progress
                    </div>
                    <span class="roi-badge {{ $data['roi_stats']['has_reached_2x'] ? 'badge-done' : 'badge-on' }}">
                        {{ $data['roi_stats']['has_reached_2x'] ? 'Completed' : 'Active' }}
                    </span>
                </div>
                <div class="slim-track">
                    <div class="slim-fill {{ $data['roi_stats']['has_reached_2x'] ? 'fill-red' : 'fill-indigo' }}"
                         style="width:{{ min($data['roi_stats']['completion_percentage'],100) }}%"></div>
                </div>
                <div style="text-align:right;font-size:.72rem;font-weight:700;color:var(--ink-2);margin-top:-.9rem;margin-bottom:.9rem;">
                    {{ number_format(min($data['roi_stats']['completion_percentage'],100),1) }}%
                </div>
                <div class="roi-stats">
                    <div>
                        <div class="rs-val">${{ number_format($data['roi_stats']['invested_amount'],2) }}</div>
                        <div class="rs-lbl">Investment</div>
                    </div>
                    <div>
                        <div class="rs-val">${{ number_format($data['roi_stats']['total_roi_paid'],2) }}</div>
                        <div class="rs-lbl">Earned</div>
                    </div>
                    <div>
                        <div class="rs-val">${{ number_format($data['roi_stats']['remaining_amount'],2) }}</div>
                        <div class="rs-lbl">Remaining</div>
                    </div>
                </div>
                @if($data['roi_stats']['has_reached_2x'])
                <div class="roi-notice notice-green"><i class="fas fa-check-circle"></i> 2X ROI Target Achieved!</div>
                @endif
            </div>

            {{-- 7X --}}
            <div class="roi-card">
                <div class="roi-top">
                    <div class="roi-label">
                        <div class="ri" style="background:rgba(245,158,11,.1);color:#f59e0b"><i class="fas fa-shield-alt"></i></div>
                        7X Withdrawal Control
                    </div>
                    <span class="roi-badge {{ $data['roi_stats']['has_reached_7x'] ? 'badge-done' : ($data['roi_stats']['withdrawal_enabled'] ? 'badge-on' : 'badge-off') }}">
                        {{ $data['roi_stats']['withdrawal_enabled'] ? 'Enabled' : 'Suspended' }}
                    </span>
                </div>
                <div class="slim-track">
                    <div class="slim-fill {{ $data['roi_stats']['has_reached_7x'] ? 'fill-red' : 'fill-amber' }}"
                         style="width:{{ min($data['roi_stats']['completion_7x_percentage'],100) }}%"></div>
                </div>
                <div style="text-align:right;font-size:.72rem;font-weight:700;color:var(--ink-2);margin-top:-.9rem;margin-bottom:.9rem;">
                    {{ number_format(min($data['roi_stats']['completion_7x_percentage'],100),1) }}%
                </div>
                <div class="roi-stats">
                    <div>
                        <div class="rs-val">${{ number_format($data['roi_stats']['seven_x_limit'],2) }}</div>
                        <div class="rs-lbl">7X Limit</div>
                    </div>
                    <div>
                        <div class="rs-val">${{ number_format($data['roi_stats']['total_roi_paid'],2) }}</div>
                        <div class="rs-lbl">Earned</div>
                    </div>
                    <div>
                        <div class="rs-val">${{ number_format($data['roi_stats']['remaining_7x_amount'],2) }}</div>
                        <div class="rs-lbl">Until Limit</div>
                    </div>
                </div>
                @if(!$data['roi_stats']['withdrawal_enabled'])
                <div class="roi-notice notice-amber"><i class="fas fa-ban"></i> Withdrawals suspended — top-up required</div>
                @endif
            </div>
        </div>

        {{-- Announcement Banner --}}
        <div class="ann-banner">
            <div class="ann-inner">
                <div class="ann-label">Announcement</div>
                <div class="ann-title">Second Level Reward Increased!</div>
                <div class="ann-desc">We've upgraded the Second Level Reward to boost your earnings potential. This enhanced structure helps you grow your network and maximize returns.</div>
                <div class="ann-chips">
                    <div class="ann-chip">
                        <div class="ann-chip-val">$260</div>
                        <div class="ann-chip-lbl">Previous</div>
                    </div>
                    <div class="ann-chip hi">
                        <div class="ann-chip-val">$350</div>
                        <div class="ann-chip-lbl">New Reward</div>
                    </div>
                    <div class="ann-chip">
                        <div class="ann-chip-val">+35%</div>
                        <div class="ann-chip-lbl">Increase</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section: Targets --}}
        <div class="sec-label">Targets &amp; Progress</div>
        <div class="target-grid">
            @php $r = 74; $c = 2 * M_PI * $r; @endphp

            <div class="t-card">
                <h3>Reward Target</h3>
                <p>Track your progress towards the next reward milestone</p>
                <div class="ring-wrap">
                    <svg viewBox="0 0 168 168">
                        <circle class="ring-bg"   cx="84" cy="84" r="{{ $r }}"/>
                        <circle class="ring-fill" cx="84" cy="84" r="{{ $r }}"
                            stroke-dasharray="{{ $c }}"
                            stroke-dashoffset="{{ $c - ($c * $data['reward'] / 100) }}"/>
                    </svg>
                    <div class="ring-center">
                        <div class="ring-pct">{{ number_format($data['reward'],1) }}%</div>
                        <div class="ring-sub">Complete</div>
                    </div>
                </div>
                <button class="t-btn t-btn-i" id="btnReward">
                    <i class="fas fa-trophy"></i> View Reward Details
                </button>
            </div>

            <div class="t-card">
                <h3>Rank Target</h3>
                <p>Advance to the next leadership level in the network</p>
                <div class="ring-wrap">
                    <svg viewBox="0 0 168 168">
                        <circle class="ring-bg"   cx="84" cy="84" r="{{ $r }}"/>
                        <circle class="ring-fill" cx="84" cy="84" r="{{ $r }}"
                            stroke-dasharray="{{ $c }}"
                            stroke-dashoffset="{{ $c }}"
                            style="stroke:#8b5cf6"/>
                    </svg>
                    <div class="ring-center">
                        <div class="ring-pct">0%</div>
                        <div class="ring-sub">Complete</div>
                    </div>
                </div>
                <button class="t-btn t-btn-v" id="btnRank">
                    <i class="fas fa-crown"></i> View Rank Progress
                </button>
            </div>
        </div>

        {{-- Reward Detail Panel --}}
        <div class="d-panel" id="panelReward">
            <div class="d-panel-head">
                <div class="dph-icon" style="background:rgba(99,102,241,.1);color:#6366f1"><i class="fas fa-trophy"></i></div>
                Reward Level Progress
            </div>
            <div class="d-panel-body">
                @foreach($data['levelCount'] as $level => $count)
                @php
                    $mx  = [1=>10,2=>50,3=>150,4=>400,5=>1000,6=>2000,7=>4000][$level] ?? 1;
                    $rwd = [1=>130,2=>350,3=>1050,4=>3450,5=>8650,6=>26000,7=>41500][$level] ?? 0;
                    $pct = min(($count/$mx)*100,100);
                @endphp
                <div class="lv-row">
                    <div class="lv-num">{{ $level }}</div>
                    <div class="lv-info">
                        <h6>Level {{ $level }} Reward</h6>
                        <p>${{ number_format($rwd) }} achievement bonus</p>
                    </div>
                    <div class="lv-bar-wrap">
                        <div class="lv-track"><div class="lv-fill" style="width:{{ $pct }}%"></div></div>
                        <div class="lv-pct">{{ number_format($pct,1) }}% complete</div>
                    </div>
                    <div class="lv-count">{{ $count }}<br><span>/ {{ $mx }}</span></div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Rank Detail Panel --}}
        <div class="d-panel" id="panelRank">
            <div class="d-panel-head">
                <div class="dph-icon" style="background:rgba(139,92,246,.1);color:#8b5cf6"><i class="fas fa-crown"></i></div>
                Rank Advancement Progress
            </div>
            <div class="d-panel-body">
                @php
                    $ranks=[
                        ['name'=>'Bronze',  'req'=>'Start your journey',  'size'=>5,    'file'=>'bronze.png',   'hex'=>'CD7F32'],
                        ['name'=>'Silver',  'req'=>'Build active team',   'size'=>15,   'file'=>'silver.png',   'hex'=>'C0C0C0'],
                        ['name'=>'Gold',    'req'=>'Achieve leadership',  'size'=>50,   'file'=>'gold.png',     'hex'=>'FFD700'],
                        ['name'=>'Platinum','req'=>'Master networker',    'size'=>150,  'file'=>'platinum.png', 'hex'=>'E5E4E2'],
                        ['name'=>'Diamond', 'req'=>'Elite performer',     'size'=>500,  'file'=>'diamond.png',  'hex'=>'B9F2FF'],
                        ['name'=>'Master',  'req'=>'Industry expert',     'size'=>1500, 'file'=>'master.png',   'hex'=>'800080'],
                        ['name'=>'Champion','req'=>'Global leader',       'size'=>5000, 'file'=>'champion.png', 'hex'=>'FF6B6B'],
                    ];
                @endphp
                <div class="rank-grid">
                    @foreach($ranks as $rk)
                    @php
                        $tm  = $data['totalTeam'] ?? 0;
                        $rp  = min(($tm/$rk['size'])*100,100);
                        $won = $tm >= $rk['size'];
                        $ip  = public_path('assets/images/ranks/'.$rk['file']);
                        $iu  = file_exists($ip)
                             ? asset('assets/images/ranks/'.$rk['file'])
                             : 'https://placehold.co/56x56/'.$rk['hex'].'/FFFFFF?text='.substr($rk['name'],0,1);
                    @endphp
                    <div class="rank-item {{ $won ? 'won' : '' }}">
                        <img src="{{ $iu }}" alt="{{ $rk['name'] }}" class="rank-img"
                             onerror="this.src='https://placehold.co/56x56/{{ $rk['hex'] }}/FFFFFF?text={{ substr($rk['name'],0,1) }}'">
                        <div class="rank-name">{{ $rk['name'] }}</div>
                        <div class="rank-req">{{ $rk['req'] }}</div>
                        <div class="rank-track"><div class="rank-fill" style="width:{{ $rp }}%"></div></div>
                        <div class="rank-count">{{ $tm }} / {{ $rk['size'] }}</div>
                        @if($won)
                        <div class="rank-done"><i class="fas fa-check"></i> Achieved</div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>{{-- /db-body --}}
</div>{{-- /db-page --}}
@endsection

@section('page_js')
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ── ApexChart ──────────────────────────────────────────── */
    const ow = {{ $data['online_wallet'] ?? 0 }};
    const tm = {{ $data['totalTeam']     ?? 0 }};

    const sets = {
        '7d' : { x:['Mon','Tue','Wed','Thu','Fri','Sat','Sun'],
                 e:[.10,.15,.12,.18,.14,.16,.15].map(f=>+(ow*f).toFixed(2)||10),
                 t:[.85,.87,.90,.92,.95,.98,1].map(f=>+(tm*f).toFixed(0)||5) },
        '30d': { x:['Week 1','Week 2','Week 3','Week 4'],
                 e:[.20,.30,.25,.25].map(f=>+(ow*f).toFixed(2)||20),
                 t:[.70,.80,.90,1].map(f=>+(tm*f).toFixed(0)||7) },
        '90d': { x:['Month 1','Month 2','Month 3'],
                 e:[.40,.30,.30].map(f=>+(ow*f).toFixed(2)||40),
                 t:[.60,.80,1].map(f=>+(tm*f).toFixed(0)||6) },
        '1y' : { x:['Q1','Q2','Q3','Q4'],
                 e:[.15,.25,.35,.25].map(f=>+(ow*f).toFixed(2)||15),
                 t:[.40,.60,.85,1].map(f=>+(tm*f).toFixed(0)||4) }
    };

    let ch = null;
    function draw(p) {
        const d = sets[p];
        const o = {
            series: [
                { name:'Earnings ($)',  type:'column', data: d.e },
                { name:'Team Growth',   type:'line',   data: d.t }
            ],
            chart: {
                height: 360, type:'line',
                fontFamily:"'Poppins',sans-serif",
                toolbar:{ show:true, tools:{ download:true, zoom:true, reset:true, selection:false, zoomin:false, zoomout:false, pan:false } },
                animations:{ enabled:true, easing:'easeinout', speed:600 }
            },
            colors: ['#6366f1','#10b981'],
            stroke: { width:[0,3], curve:'smooth' },
            plotOptions:{ bar:{ columnWidth:'45%', borderRadius:6, borderRadiusApplication:'end' } },
            fill:{
                type:['gradient','solid'],
                gradient:{ shade:'light', type:'vertical', opacityFrom:.85, opacityTo:.45, gradientToColors:['#8b5cf6'] }
            },
            dataLabels:{ enabled:false },
            markers:{ size:[0,5], strokeWidth:2, strokeColors:'#fff', colors:['#6366f1','#10b981'] },
            xaxis:{
                categories: d.x,
                labels:{ style:{ fontSize:'11px', fontWeight:600, colors:'#9ba4ba' } },
                axisBorder:{ show:false }, axisTicks:{ show:false }
            },
            yaxis:[
                { title:{ text:'Earnings ($)', style:{ color:'#6366f1', fontWeight:600, fontSize:'12px' } },
                  labels:{ formatter:v=>'$'+(v||0).toFixed(0), style:{ colors:'#6366f1', fontSize:'11px' } } },
                { opposite:true,
                  title:{ text:'Team Members', style:{ color:'#10b981', fontWeight:600, fontSize:'12px' } },
                  labels:{ formatter:v=>Math.round(v||0), style:{ colors:'#10b981', fontSize:'11px' } } }
            ],
            tooltip:{ shared:true, intersect:false,
                y:[{formatter:y=>y!=null?'$'+y.toFixed(2):y},{formatter:y=>y!=null?Math.round(y)+' members':y}] },
            grid:{ borderColor:'#f1f5f9', strokeDashArray:5, padding:{ left:10, right:10 } },
            legend:{ position:'top', horizontalAlign:'right', fontSize:'12px', fontWeight:600,
                     markers:{ width:9, height:9, radius:3 }, itemMargin:{ horizontal:10 } }
        };
        const el = document.getElementById('businessChart');
        if (!el || typeof ApexCharts === 'undefined') return;
        if (ch) { ch.destroy(); ch = null; }
        ch = new ApexCharts(el, o);
        ch.render();
    }

    setTimeout(() => draw('7d'), 500);

    document.querySelectorAll('.ftab').forEach(b => {
        b.addEventListener('click', function () {
            document.querySelectorAll('.ftab').forEach(x => x.classList.remove('active'));
            this.classList.add('active');
            draw(this.dataset.period);
        });
    });

    /* ── Detail Panels ──────────────────────────────────────── */
    function toggle(show, hide) {
        document.getElementById(hide).classList.remove('open');
        const p = document.getElementById(show);
        const wasOpen = p.classList.contains('open');
        p.classList.toggle('open', !wasOpen);
        if (!wasOpen) setTimeout(() => p.scrollIntoView({ behavior:'smooth', block:'nearest' }), 60);
    }

    document.getElementById('btnReward').addEventListener('click', () => toggle('panelReward','panelRank'));
    document.getElementById('btnRank').addEventListener('click',   () => toggle('panelRank','panelReward'));

    /* ── Animated number counters ────────────────────────── */
    function countUp(el, target, prefix, decimals) {
        const dur = 1600, start = performance.now();
        const from = 0;
        function step(now) {
            const p = Math.min((now - start) / dur, 1);
            const ease = 1 - Math.pow(1 - p, 3);
            const val = from + (target - from) * ease;
            el.textContent = prefix + val.toFixed(decimals);
            if (p < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }

    function initCounters() {
        document.querySelectorAll('.s-value, .kpi-value').forEach(el => {
            const raw = el.textContent.trim();
            const prefix = raw.startsWith('$') ? '$' : '';
            const num = parseFloat(raw.replace(/[^0-9.]/g, ''));
            if (!isNaN(num) && num > 0) {
                const decimals = raw.includes('.') ? 2 : 0;
                el.textContent = prefix + '0' + (decimals ? '.00' : '');
                countUp(el, num, prefix, decimals);
            }
        });
    }

    /* ── Card entrance animations ────────────────────────── */
    const cards = document.querySelectorAll('.s-card, .roi-card, .t-card, .panel');
    cards.forEach((c, i) => {
        c.style.opacity = '0';
        c.style.transform = 'translateY(18px)';
        c.style.transition = 'opacity .45s ease, transform .45s ease';
        c.style.transitionDelay = (i * 55) + 'ms';
    });
    const observer = new IntersectionObserver(entries => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                e.target.style.opacity = '1';
                e.target.style.transform = 'translateY(0)';
                observer.unobserve(e.target);
            }
        });
    }, { threshold: 0.08 });
    cards.forEach(c => observer.observe(c));

    setTimeout(initCounters, 300);
});
</script>
@endsection
