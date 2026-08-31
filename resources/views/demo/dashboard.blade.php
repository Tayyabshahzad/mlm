@extends('demo.layout.app')
@section('content')

<style>
/* ═══════════════════════════════════════════════════════
   GVI DASHBOARD v6 — Modern Light
═══════════════════════════════════════════════════════ */
:root {
    --f:  'Poppins', system-ui, sans-serif;
    --bg: #f4f6fb;
    --t1: #0f172a;
    --t2: #475569;
    --t3: #94a3b8;
    --br: rgba(15,23,42,.08);
    --card-r: 16px;
    --ease: cubic-bezier(.25,.46,.45,.94);
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

/* ── Page shell ─────────────────────────────────────── */
.db { font-family: var(--f); background: var(--bg); min-height: 100vh; color: var(--t1); -webkit-font-smoothing: antialiased; }

/* ─────────────────────────────────────────────────────
   TICKER
───────────────────────────────────────────────────── */
.ticker { overflow: hidden; background: linear-gradient(90deg,#4f46e5,#0ea5e9); padding: .48rem 0; }
.ticker-track { display: flex; width: max-content; animation: ticker-go 35s linear infinite; }
.ticker-track:hover { animation-play-state: paused; }
@keyframes ticker-go { to { transform: translateX(-50%); } }
.t-item { display: inline-flex; align-items: center; gap: .5rem; padding: 0 2.2rem; font-size: .74rem; font-weight: 600; color: #fff; white-space: nowrap; }
.t-dot  { width: 4px; height: 4px; border-radius: 50%; background: rgba(255,255,255,.45); }

/* ─────────────────────────────────────────────────────
   HERO
───────────────────────────────────────────────────── */
.hero {
    background: linear-gradient(135deg, #1e1b4b 0%, #2e1065 45%, #1e3a8a 100%);
    padding: 2rem 2rem 1.8rem; position: relative; overflow: hidden;
}
.hero::before {
    content: ''; position: absolute; inset: 0;
    background-image: radial-gradient(circle at 20% 50%, rgba(139,92,246,.20) 0%, transparent 50%),
                      radial-gradient(circle at 80% 20%, rgba(59,130,246,.18) 0%, transparent 45%);
    pointer-events: none;
}
/* subtle grid dots */
.hero::after {
    content: ''; position: absolute; inset: 0;
    background-image: radial-gradient(circle, rgba(255,255,255,.08) 1px, transparent 1px);
    background-size: 28px 28px; pointer-events: none;
}
.hero-in {
    max-width: 1340px; margin: 0 auto; position: relative; z-index: 1;
    display: flex; align-items: center; justify-content: space-between; gap: 1.5rem; flex-wrap: wrap;
}
.hero-brand { font-size: .6rem; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: rgba(255,255,255,.45); margin-bottom: .45rem; }
.hero-name  { font-size: 2rem; font-weight: 900; color: #fff; letter-spacing: -.03em; line-height: 1.15; margin-bottom: .7rem; }
.hero-name em { font-style: normal; background: linear-gradient(90deg,#a78bfa,#67e8f9); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
.hero-pkg { display: inline-flex; align-items: center; gap: .36rem; padding: .26rem .82rem; border-radius: 50px; font-size: .66rem; font-weight: 700; letter-spacing: .07em; text-transform: uppercase; border: 1px solid rgba(255,255,255,.2); color: rgba(255,255,255,.85); background: rgba(255,255,255,.1); }
.hero-pkg.vip { color: #fde68a; border-color: rgba(253,230,138,.35); background: rgba(253,230,138,.1); }

.hero-bal {
    flex-shrink: 0; min-width: 220px; text-align: right;
    background: rgba(255,255,255,.09); border: 1px solid rgba(255,255,255,.16);
    border-radius: 14px; padding: 1.2rem 1.7rem; backdrop-filter: blur(12px);
}
.hero-bal-lbl { font-size: .6rem; font-weight: 700; letter-spacing: .13em; text-transform: uppercase; color: rgba(255,255,255,.5); margin-bottom: .28rem; }
.hero-bal-val { font-size: 2.1rem; font-weight: 900; color: #fff; letter-spacing: -.04em; line-height: 1; margin-bottom: .22rem; }
.hero-bal-sub { font-size: .68rem; color: rgba(255,255,255,.5); display: flex; align-items: center; gap: .25rem; justify-content: flex-end; }
.hero-bal-sub .live { width: 6px; height: 6px; border-radius: 50%; background: #34d399; box-shadow: 0 0 6px #34d399; }

.hero-date { text-align: right; flex-shrink: 0; }
.hero-date-t { font-size: .6rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: rgba(255,255,255,.38); margin-bottom: .16rem; }
.hero-date-v { font-size: .84rem; font-weight: 700; color: rgba(255,255,255,.82); }
.hero-date-s { font-size: .68rem; color: rgba(255,255,255,.35); margin-top: .08rem; }

/* ─────────────────────────────────────────────────────
   BODY
───────────────────────────────────────────────────── */
.db-body { max-width: 1340px; margin: 0 auto; padding: 1.8rem 1.5rem 4rem; }

/* Section label */
.sec { display: flex; align-items: center; gap: .6rem; margin-bottom: .9rem; margin-top: 1.6rem; }
.sec:first-child { margin-top: 0; }
.sec-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
.sec-txt { font-size: .65rem; font-weight: 700; letter-spacing: .13em; text-transform: uppercase; color: var(--t2); }
.sec::after { content: ''; flex: 1; height: 1px; background: var(--br); }

/* ─────────────────────────────────────────────────────
   STAT CARDS GRID
───────────────────────────────────────────────────── */
.sg {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem; margin-bottom: 1.4rem;
}

/* Stat card base */
.sc {
    border-radius: var(--card-r); padding: 1.3rem 1.35rem;
    position: relative; overflow: hidden;
    text-decoration: none; color: inherit;
    transition: transform .2s var(--ease), box-shadow .2s var(--ease);
    border: 1px solid transparent;
}
.sc:hover { transform: translateY(-3px); }

/* Card bg/border per variant */
.sc-purple { background: linear-gradient(145deg,#faf5ff,#f3e8ff); border-color: rgba(167,139,250,.25); box-shadow: 0 2px 12px rgba(124,58,237,.1); }
.sc-green  { background: linear-gradient(145deg,#f0fdf4,#dcfce7); border-color: rgba(74,222,128,.25);  box-shadow: 0 2px 12px rgba(5,150,105,.1); }
.sc-blue   { background: linear-gradient(145deg,#eff6ff,#dbeafe); border-color: rgba(96,165,250,.25);  box-shadow: 0 2px 12px rgba(37,99,235,.1); }
.sc-amber  { background: linear-gradient(145deg,#fffbeb,#fde68a40); border-color: rgba(251,191,36,.3); box-shadow: 0 2px 12px rgba(217,119,6,.1); }
.sc-pink   { background: linear-gradient(145deg,#fdf2f8,#fce7f3); border-color: rgba(249,168,212,.28); box-shadow: 0 2px 12px rgba(219,39,119,.1); }
.sc-sky    { background: linear-gradient(145deg,#f0f9ff,#e0f2fe); border-color: rgba(56,189,248,.25);  box-shadow: 0 2px 12px rgba(8,145,178,.1); }
.sc-yellow { background: linear-gradient(145deg,#fefce8,#fef08a40); border-color: rgba(253,224,71,.3); box-shadow: 0 2px 12px rgba(202,138,4,.1); }
.sc-teal   { background: linear-gradient(145deg,#f0fdfa,#ccfbf1); border-color: rgba(45,212,191,.25);  box-shadow: 0 2px 12px rgba(13,148,136,.1); }
.sc-indigo { background: linear-gradient(145deg,#eef2ff,#e0e7ff); border-color: rgba(129,140,248,.25); box-shadow: 0 2px 12px rgba(79,70,229,.1); }
.sc-rose   { background: linear-gradient(145deg,#fff1f2,#ffe4e6); border-color: rgba(251,113,133,.25);  box-shadow: 0 2px 12px rgba(220,38,38,.1); }
.sc-emerald{ background: linear-gradient(145deg,#ecfdf5,#d1fae5); border-color: rgba(52,211,153,.25);  box-shadow: 0 2px 12px rgba(5,150,105,.1); }
.sc-violet { background: linear-gradient(145deg,#f5f3ff,#ede9fe); border-color: rgba(196,181,253,.3);  box-shadow: 0 2px 12px rgba(124,58,237,.1); }

.sc:hover.sc-purple { box-shadow: 0 8px 28px rgba(124,58,237,.18); }
.sc:hover.sc-green  { box-shadow: 0 8px 28px rgba(5,150,105,.18); }
.sc:hover.sc-blue   { box-shadow: 0 8px 28px rgba(37,99,235,.18); }
.sc:hover.sc-amber  { box-shadow: 0 8px 28px rgba(217,119,6,.18); }
.sc:hover.sc-pink   { box-shadow: 0 8px 28px rgba(219,39,119,.18); }
.sc:hover.sc-sky    { box-shadow: 0 8px 28px rgba(8,145,178,.18); }
.sc:hover.sc-teal   { box-shadow: 0 8px 28px rgba(13,148,136,.18); }
.sc:hover.sc-indigo { box-shadow: 0 8px 28px rgba(79,70,229,.18); }

/* Card layout */
.sc-top { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: .85rem; }
.sc-icon {
    width: 46px; height: 46px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.05rem; flex-shrink: 0;
}
.sc-arrow { font-size: .7rem; color: rgba(15,23,42,.2); transition: color .18s, transform .18s; }
.sc:hover .sc-arrow { color: rgba(15,23,42,.45); transform: translateX(2px); }

.sc-lbl { font-size: .62rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; margin-bottom: .32rem; }
.sc-val { font-size: 1.55rem; font-weight: 900; letter-spacing: -.025em; line-height: 1; margin-bottom: .3rem; }
.sc-sub { font-size: .66rem; display: flex; align-items: center; gap: .24rem; }

/* Per-variant icon bg + colors */
.sc-purple .sc-icon { background: rgba(124,58,237,.14); color: #7c3aed; }
.sc-purple .sc-lbl  { color: #7c3aed; }
.sc-purple .sc-val  { color: #4c1d95; }
.sc-purple .sc-sub  { color: #8b5cf6; }

.sc-green  .sc-icon { background: rgba(5,150,105,.14); color: #059669; }
.sc-green  .sc-lbl  { color: #059669; }
.sc-green  .sc-val  { color: #064e3b; }
.sc-green  .sc-sub  { color: #10b981; }

.sc-blue   .sc-icon { background: rgba(37,99,235,.14); color: #2563eb; }
.sc-blue   .sc-lbl  { color: #2563eb; }
.sc-blue   .sc-val  { color: #1e3a8a; }
.sc-blue   .sc-sub  { color: #3b82f6; }

.sc-amber  .sc-icon { background: rgba(217,119,6,.14); color: #d97706; }
.sc-amber  .sc-lbl  { color: #b45309; }
.sc-amber  .sc-val  { color: #78350f; }
.sc-amber  .sc-sub  { color: #f59e0b; }

.sc-pink   .sc-icon { background: rgba(219,39,119,.14); color: #db2777; }
.sc-pink   .sc-lbl  { color: #be185d; }
.sc-pink   .sc-val  { color: #831843; }
.sc-pink   .sc-sub  { color: #ec4899; }

.sc-sky    .sc-icon { background: rgba(8,145,178,.14); color: #0891b2; }
.sc-sky    .sc-lbl  { color: #0891b2; }
.sc-sky    .sc-val  { color: #164e63; }
.sc-sky    .sc-sub  { color: #06b6d4; }

.sc-yellow .sc-icon { background: rgba(202,138,4,.14); color: #ca8a04; }
.sc-yellow .sc-lbl  { color: #a16207; }
.sc-yellow .sc-val  { color: #713f12; }
.sc-yellow .sc-sub  { color: #eab308; }

.sc-teal   .sc-icon { background: rgba(13,148,136,.14); color: #0d9488; }
.sc-teal   .sc-lbl  { color: #0d9488; }
.sc-teal   .sc-val  { color: #134e4a; }
.sc-teal   .sc-sub  { color: #14b8a6; }

.sc-indigo .sc-icon { background: rgba(79,70,229,.14); color: #4f46e5; }
.sc-indigo .sc-lbl  { color: #4338ca; }
.sc-indigo .sc-val  { color: #312e81; }
.sc-indigo .sc-sub  { color: #6366f1; }

.sc-rose   .sc-icon { background: rgba(220,38,38,.14); color: #dc2626; }
.sc-rose   .sc-lbl  { color: #b91c1c; }
.sc-rose   .sc-val  { color: #7f1d1d; }
.sc-rose   .sc-sub  { color: #ef4444; }

.sc-emerald .sc-icon { background: rgba(16,185,129,.14); color: #10b981; }
.sc-emerald .sc-lbl  { color: #047857; }
.sc-emerald .sc-val  { color: #064e3b; }
.sc-emerald .sc-sub  { color: #34d399; }

.sc-violet .sc-icon { background: rgba(139,92,246,.14); color: #8b5cf6; }
.sc-violet .sc-lbl  { color: #7c3aed; }
.sc-violet .sc-val  { color: #4c1d95; }
.sc-violet .sc-sub  { color: #a78bfa; }

/* ─────────────────────────────────────────────────────
   ROI METER CARDS
───────────────────────────────────────────────────── */
.roi-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.4rem; }

.roi-card {
    background: #fff; border-radius: var(--card-r);
    border: 1px solid var(--br); padding: 1.4rem 1.5rem;
    box-shadow: 0 1px 6px rgba(15,23,42,.06);
    position: relative; overflow: hidden;
}
.roi-card::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0;
    height: 3px; background: var(--rc-top, #4f46e5); border-radius: var(--card-r) var(--card-r) 0 0;
}
.roi-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; }
.roi-icon-wrap { display: flex; align-items: center; gap: .55rem; }
.roi-icon { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: .88rem; }
.roi-title { font-size: .84rem; font-weight: 700; color: var(--t1); }
.roi-sub   { font-size: .64rem; color: var(--t2); margin-top: .07rem; }

.badge { font-size: .6rem; font-weight: 700; letter-spacing: .07em; text-transform: uppercase; padding: .2rem .64rem; border-radius: 50px; }
.badge-green  { background: #dcfce7; color: #166534; }
.badge-red    { background: #fee2e2; color: #991b1b; }
.badge-amber  { background: #fef3c7; color: #92400e; }

.track { height: 8px; background: #f1f5f9; border-radius: 99px; overflow: hidden; margin-bottom: .4rem; }
.track-fill { height: 100%; border-radius: 99px; transition: width 1.2s ease; }
.track-pct  { text-align: right; font-size: .67rem; font-weight: 700; color: var(--t3); margin-top: -.3rem; margin-bottom: .8rem; }

.roi-stats { display: grid; grid-template-columns: repeat(3,1fr); gap: .4rem; padding-top: .8rem; border-top: 1px solid var(--br); }
.rs-val { font-size: .97rem; font-weight: 800; color: var(--t1); }
.rs-lbl { font-size: .58rem; color: var(--t3); font-weight: 600; text-transform: uppercase; letter-spacing: .06em; margin-top: .08rem; }

.roi-notice { display: flex; align-items: center; gap: .42rem; margin-top: .85rem; padding: .5rem .8rem; border-radius: 8px; font-size: .72rem; font-weight: 600; }
.rn-g { background: #dcfce7; color: #166534; }
.rn-a { background: #fef3c7; color: #92400e; }

/* ─────────────────────────────────────────────────────
   TARGET RINGS
───────────────────────────────────────────────────── */
.tg-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1.4rem; }
.tg-card {
    background: #fff; border: 1px solid var(--br); border-radius: var(--card-r);
    box-shadow: 0 1px 6px rgba(15,23,42,.06); padding: 1.6rem; text-align: center;
    transition: transform .2s var(--ease), box-shadow .2s;
}
.tg-card:hover { transform: translateY(-3px); box-shadow: 0 6px 22px rgba(15,23,42,.1); }
.tg-card h3 { font-size: .88rem; font-weight: 800; color: var(--t1); margin-bottom: .16rem; }
.tg-card p  { font-size: .72rem; color: var(--t2); margin-bottom: 1.25rem; line-height: 1.5; }

.ring-w { position: relative; width: 146px; height: 146px; margin: 0 auto 1.25rem; }
.ring-w svg { width: 100%; height: 100%; transform: rotate(-90deg); }
.ring-bg   { fill: none; stroke: #f1f5f9; stroke-width: 9; }
.ring-fill { fill: none; stroke-width: 9; stroke-linecap: round; }
.ring-c { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; }
.ring-pct { font-size: 1.7rem; font-weight: 900; color: var(--t1); letter-spacing: -.04em; line-height: 1; }
.ring-s   { font-size: .6rem; font-weight: 600; color: var(--t2); text-transform: uppercase; letter-spacing: .08em; margin-top: .26rem; }

.tg-btn { width: 100%; padding: .68rem; border: none; border-radius: 10px; font-family: var(--f); font-size: .78rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: .36rem; transition: opacity .18s, transform .18s; }
.tg-btn:hover { opacity: .86; transform: translateY(-1px); }
.tg-btn-a { background: linear-gradient(135deg,#7c3aed,#6d28d9); color: #fff; box-shadow: 0 4px 14px rgba(124,58,237,.3); }
.tg-btn-b { background: linear-gradient(135deg,#2563eb,#1d4ed8); color: #fff; box-shadow: 0 4px 14px rgba(37,99,235,.3); }

/* ─────────────────────────────────────────────────────
   EXPANDABLE PANELS
───────────────────────────────────────────────────── */
.xpanel { background: #fff; border: 1px solid var(--br); border-radius: var(--card-r); box-shadow: 0 1px 6px rgba(15,23,42,.06); margin-bottom: 1.2rem; display: none; }
.xpanel.open { display: block; animation: fadeup .28s var(--ease); }
@keyframes fadeup { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.xp-head { padding: 1.1rem 1.4rem; border-bottom: 1px solid var(--br); display: flex; align-items: center; gap: .6rem; font-size: .88rem; font-weight: 800; color: var(--t1); }
.xp-ico  { width: 32px; height: 32px; border-radius: 9px; display: flex; align-items: center; justify-content: center; font-size: .78rem; }
.xp-body { padding: 1.4rem; }

.lv-row { display: grid; grid-template-columns: 40px 1fr 150px 72px; gap: .8rem; align-items: center; padding: .75rem .9rem; border-radius: 10px; border: 1px solid transparent; margin-bottom: .38rem; transition: background .15s, border-color .15s; }
.lv-row:hover { background: #f8fafc; border-color: var(--br); }
.lv-num { width: 40px; height: 40px; border-radius: 10px; background: linear-gradient(135deg,#7c3aed,#6d28d9); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: .95rem; flex-shrink: 0; box-shadow: 0 3px 9px rgba(124,58,237,.22); }
.lv-info h6 { font-size: .78rem; font-weight: 700; color: var(--t1); margin-bottom: .05rem; }
.lv-info p  { font-size: .66rem; color: var(--t2); }
.lv-bar { height: 5px; background: #f1f5f9; border-radius: 99px; overflow: hidden; margin-bottom: .18rem; }
.lv-fill{ height: 100%; border-radius: 99px; background: linear-gradient(90deg,#7c3aed,#06b6d4); }
.lv-pct { font-size: .6rem; color: var(--t3); font-weight: 600; }
.lv-cnt { font-size: .84rem; font-weight: 800; color: var(--t1); text-align: right; }
.lv-cnt span { font-size: .65rem; color: var(--t3); font-weight: 500; }

.rank-g { display: grid; grid-template-columns: repeat(auto-fill,minmax(120px,1fr)); gap: .75rem; }
.rank-i { text-align: center; padding: 1.15rem .85rem; border-radius: 12px; border: 1.5px solid var(--br); background: #fafafa; transition: all .2s var(--ease); }
.rank-i:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(15,23,42,.1); background: #fff; }
.rank-i.won { border-color: rgba(5,150,105,.28); background: #f0fdf4; }
.rank-img { width: 46px; height: 46px; object-fit: contain; display: block; margin: 0 auto .55rem; }
.rank-n  { font-size: .75rem; font-weight: 700; color: var(--t1); margin-bottom: .12rem; }
.rank-r  { font-size: .61rem; color: var(--t2); margin-bottom: .6rem; line-height: 1.4; }
.rank-tr { height: 4px; background: #f1f5f9; border-radius: 99px; overflow: hidden; margin-bottom: .32rem; }
.rank-br { height: 100%; border-radius: 99px; background: linear-gradient(90deg,#7c3aed,#0891b2); }
.rank-ct { font-size: .61rem; color: var(--t3); font-weight: 600; }
.rank-dn { display: inline-flex; align-items: center; gap: .17rem; background: #dcfce7; color: #166534; border-radius: 50px; padding: .12rem .46rem; font-size: .6rem; font-weight: 700; margin-top: .25rem; }

/* ─────────────────────────────────────────────────────
   ADMIN ALERT
───────────────────────────────────────────────────── */
.aa { display: flex; align-items: center; gap: .9rem; padding: .9rem 1.25rem; border-radius: var(--card-r); margin-bottom: 1.2rem; text-decoration: none; background: #fef2f2; border: 1px solid #fecaca; box-shadow: 0 1px 4px rgba(220,38,38,.1); }
.aa-ico { font-size: 1.35rem; flex-shrink: 0; }
.aa strong { display: block; color: #991b1b; font-size: .87rem; font-weight: 700; }
.aa span   { color: #ef4444; font-size: .74rem; }
.aa-n { margin-left: auto; background: #dc2626; color: #fff; border-radius: 50px; padding: .24rem .82rem; font-size: .82rem; font-weight: 800; flex-shrink: 0; }

/* ─────────────────────────────────────────────────────
   RESPONSIVE — single column on mobile
───────────────────────────────────────────────────── */
@media (max-width: 700px) {
    .sg        { grid-template-columns: 1fr; }
    .roi-grid  { grid-template-columns: 1fr; }
    .tg-grid   { grid-template-columns: 1fr; }
    .hero-bal  { display: none; }
    .hero-date { display: none; }
    .hero-name { font-size: 1.65rem; }
    .hero      { padding: 1.5rem 1.1rem 1.3rem; }
    .db-body   { padding: 1.2rem .9rem 3rem; }
    .lv-row    { grid-template-columns: 38px 1fr; gap: .5rem; }
    .lv-row > :nth-child(3), .lv-row > :nth-child(4) { display: none; }
}
@media (min-width: 701px) and (max-width: 1024px) {
    .sg { grid-template-columns: repeat(2, 1fr); }
}
@media (min-width: 1025px) {
    .sg { grid-template-columns: repeat(3, 1fr); }
}
</style>

<div class="db">
@php $isSavingOnly = ($data['account_type'] ?? null) === 'saving'; @endphp

{{-- ─── HERO ───────────────────────────────────────────── --}}
<div class="hero">
    <div class="hero-in">
        <div>
            <div class="hero-brand">Global Visioners International</div>
            <div class="hero-name">Salam, <em>{{ Auth::user()->name }}</em> 👋</div>
            @if($data['user_plan'] === 'vip')
                <span class="hero-pkg vip"><i class="fas fa-crown"></i>&nbsp;VIP Gold Package</span>
            @elseif($data['user_plan'] === 'saving')
                <span class="hero-pkg" style="color:#34d399;border-color:rgba(52,211,153,.35);background:rgba(52,211,153,.1)"><i class="fas fa-piggy-bank"></i>&nbsp;Saving Plan</span>
            @else
                <span class="hero-pkg"><i class="fas fa-gem"></i>&nbsp;Standard Package</span>
            @endif
        </div>

        <div class="hero-bal">
            <div class="hero-bal-lbl">Total Earnings</div>
            <div class="hero-bal-val" id="heroBalance">${{ number_format($data['total_earning'], 2) }}</div>
            <div class="hero-bal-sub">
                <span class="live"></span>
                {{ $isSavingOnly ? 'Saving' : 'Online' }}: ${{ number_format($data['online_wallet'], 2) }}
            </div>
        </div>

        <div class="hero-date">
            <div class="hero-date-t">Today</div>
            <div class="hero-date-v">{{ now()->format('l, M d Y') }}</div>
            @role('admin')
            <div class="hero-date-s">{{ now()->format('h:i A') }} · {{ config('app.timezone') }}</div>
            @endrole
        </div>
    </div>
</div>

{{-- ─── TICKER ──────────────────────────────────────────── --}}
<div class="ticker">
    <div class="ticker-track">
        <span class="t-item">☪&nbsp; Eid Milad-un-Nabi ﷺ Mubarak! — GVI family ki taraf se tamam members ko dil ki gehraiyon se mubarakbaad <span class="t-dot"></span></span>
        <span class="t-item">🌙&nbsp; 12 Rabi-ul-Awwal — Huzoor Nabi Kareem ﷺ ki seerat hamein mehnat, ikhlas aur umeed ka raasta dikhati hai <span class="t-dot"></span></span>
        <span class="t-item">☪&nbsp; Rehmat-ul-Alameen ﷺ — Milad Mubarak! GVI ke sath apna aur apnon ka mustaqbil roshan karen <span class="t-dot"></span></span>
        <span class="t-item">🕌&nbsp; عید میلاد النبی ﷺ مبارک — 12 ربیع الاول — GVI ki poori team ki taraf se khushamdeed <span class="t-dot"></span></span>
        {{-- duplicate for seamless loop --}}
        <span class="t-item">☪&nbsp; Eid Milad-un-Nabi ﷺ Mubarak! — GVI family ki taraf se tamam members ko dil ki gehraiyon se mubarakbaad <span class="t-dot"></span></span>
        <span class="t-item">🌙&nbsp; 12 Rabi-ul-Awwal — Huzoor Nabi Kareem ﷺ ki seerat hamein mehnat, ikhlas aur umeed ka raasta dikhati hai <span class="t-dot"></span></span>
        <span class="t-item">☪&nbsp; Rehmat-ul-Alameen ﷺ — Milad Mubarak! GVI ke sath apna aur apnon ka mustaqbil roshan karen <span class="t-dot"></span></span>
        <span class="t-item">🕌&nbsp; عید میلاد النبی ﷺ مبارک — 12 ربیع الاول — GVI ki poori team ki taraf se khushamdeed <span class="t-dot"></span></span>
    </div>
</div>

{{-- ─── BODY ────────────────────────────────────────────── --}}
<div class="db-body">

    {{-- Admin Alert --}}
    @role('admin')
    {{-- @if($data['missed_roi_count'] > 0)
    <a href="{{ route('roi.submission.monitoring') }}" class="aa">
        <div class="aa-ico">⚠️</div>
        <div><strong>ROI Submissions Missing Today</strong><span>Click to review users who have not received their ROI distribution</span></div>
        <div class="aa-n">{{ $data['missed_roi_count'] }} Users</div>
    </a>
    @endif --}}
    @endrole

    {{-- ── WALLET OVERVIEW (standard + both users only) ───── --}}
    @if(!$isSavingOnly)
    <div class="sec" style="margin-top:0">
        <div class="sec-dot" style="background:#7c3aed"></div>
        <div class="sec-txt">Wallet Overview</div>
    </div>

    <div class="sg">
        {{-- Total Earnings --}}
        <div class="sc sc-purple">
            <div class="sc-top">
                <div class="sc-icon"><i class="fas fa-coins"></i></div>
                <i class="fas fa-chevron-right sc-arrow"></i>
            </div>
            <div class="sc-lbl">Total Earnings</div>
            <div class="sc-val">${{ number_format($data['total_earning'], 2) }}</div>
            <div class="sc-sub"><i class="fas fa-arrow-trend-up"></i> All-time cumulative</div>
        </div>

        {{-- Online Wallet --}}
        <div class="sc sc-green">
            <div class="sc-top">
                <div class="sc-icon"><i class="fas fa-wallet"></i></div>
                <i class="fas fa-chevron-right sc-arrow"></i>
            </div>
            <div class="sc-lbl">Online Wallet</div>
            <div class="sc-val">${{ number_format($data['online_wallet'], 2) }}</div>
            <div class="sc-sub"><i class="fas fa-circle" style="font-size:.4rem"></i> Available balance</div>
        </div>

        {{-- ROI Earnings --}}
        <div class="sc sc-blue">
            <div class="sc-top">
                <div class="sc-icon"><i class="fas fa-chart-line"></i></div>
                <i class="fas fa-chevron-right sc-arrow"></i>
            </div>
            <div class="sc-lbl">ROI Earnings</div>
            <div class="sc-val">${{ number_format($data['roi'], 2) }}</div>
            <div class="sc-sub"><i class="fas fa-arrow-up"></i> Return on investment</div>
        </div>

        {{-- Direct / Indirect --}}
        <div class="sc sc-amber">
            <div class="sc-top">
                <div class="sc-icon"><i class="fas fa-users"></i></div>
                <i class="fas fa-chevron-right sc-arrow"></i>
            </div>
            <div class="sc-lbl">Direct / Indirect</div>
            <div class="sc-val">${{ number_format($data['direct_indirect'], 2) }}</div>
            <div class="sc-sub"><i class="fas fa-arrow-up"></i> Commission income</div>
        </div>

        {{-- Profit Sharing --}}
        <div class="sc sc-pink">
            <div class="sc-top">
                <div class="sc-icon"><i class="fas fa-chart-pie"></i></div>
                <i class="fas fa-chevron-right sc-arrow"></i>
            </div>
            <div class="sc-lbl">Profit Sharing</div>
            <div class="sc-val">${{ number_format($data['profit_share'], 2) }}</div>
            <div class="sc-sub"><i class="fas fa-calendar"></i> Monthly distribution</div>
        </div>

        {{-- Rewards --}}
        <div class="sc sc-sky">
            <div class="sc-top">
                <div class="sc-icon"><i class="fas fa-gift"></i></div>
                <i class="fas fa-chevron-right sc-arrow"></i>
            </div>
            <div class="sc-lbl">Rewards Earned</div>
            <div class="sc-val">${{ number_format($data['rewardWallet'], 2) }}</div>
            <div class="sc-sub"><i class="fas fa-trophy"></i> Achievement bonuses</div>
        </div>

        {{-- Designation Incentive --}}
        <a href="{{ route('wallets.incentive') }}" class="sc sc-yellow" style="cursor:pointer">
            <div class="sc-top">
                <div class="sc-icon"><i class="fas fa-star"></i></div>
                <i class="fas fa-external-link-alt sc-arrow" style="font-size:.6rem"></i>
            </div>
            <div class="sc-lbl">Designation Incentive</div>
            <div class="sc-val">${{ number_format($data['designation_incentive'], 2) }}</div>
            <div class="sc-sub"><i class="fas fa-arrow-right"></i> View details</div>
        </a>

        {{-- Team Size --}}
        <div class="sc sc-teal">
            <div class="sc-top">
                <div class="sc-icon"><i class="fas fa-network-wired"></i></div>
                <i class="fas fa-chevron-right sc-arrow"></i>
            </div>
            <div class="sc-lbl">Team Size</div>
            <div class="sc-val" style="font-size:1.4rem;letter-spacing:0">{{ number_format($data['totalTeam']) }}</div>
            <div class="sc-sub"><i class="fas fa-user-plus"></i> Active network members</div>
        </div>

        {{-- Rank --}}
        <div class="sc sc-indigo">
            <div class="sc-top">
                <div class="sc-icon"><i class="fas fa-crown"></i></div>
                <i class="fas fa-chevron-right sc-arrow"></i>
            </div>
            <div class="sc-lbl">Your Rank</div>
            <div class="sc-val" style="font-size:1.2rem;letter-spacing:0">VISIONER</div>
            <div class="sc-sub"><i class="fas fa-star"></i> Active member status</div>
        </div>
    </div>
    @endif

    {{-- ── SAVING PLAN (saving-only + enrolled standard users) ── --}}
    @if(!empty($data['saving_enrolled']))
    <div class="sec">
        <div class="sec-dot" style="background:#059669"></div>
        <div class="sec-txt">Welfare Smart Savings Plan</div>
    </div>

    <div class="sg">
        <div class="sc sc-emerald">
            <div class="sc-top"><div class="sc-icon"><i class="fas fa-wallet"></i></div><i class="fas fa-chevron-right sc-arrow"></i></div>
            <div class="sc-lbl">Saving Investments</div>
            <div class="sc-val">${{ number_format($data['saving_deposit'] ?? 0, 2) }}</div>
            <div class="sc-sub"><i class="fas fa-calendar-check"></i> Total deposited</div>
        </div>

        <div class="sc sc-blue">
            <div class="sc-top"><div class="sc-icon"><i class="fas fa-chart-line"></i></div><i class="fas fa-chevron-right sc-arrow"></i></div>
            <div class="sc-lbl">Saving ROI's</div>
            <div class="sc-val">${{ number_format($data['saving_roi'] ?? 0, 2) }}</div>
            <div class="sc-sub"><i class="fas fa-arrow-trend-up"></i> Daily appreciation</div>
        </div>

        <div class="sc sc-violet">
            <div class="sc-top"><div class="sc-icon"><i class="fas fa-users"></i></div><i class="fas fa-chevron-right sc-arrow"></i></div>
            <div class="sc-lbl">Saving Direct &amp; Indirect</div>
            <div class="sc-val">${{ number_format(($data['saving_direct'] ?? 0) + ($data['saving_indirect'] ?? 0), 2) }}</div>
            <div class="sc-sub">D: ${{ number_format($data['saving_direct'] ?? 0, 2) }} &nbsp;·&nbsp; I: ${{ number_format($data['saving_indirect'] ?? 0, 2) }}</div>
        </div>

        @if(!empty($data['instalment_summary']['next_due']))
        <div class="sc sc-rose">
            <div class="sc-top"><div class="sc-icon"><i class="fas fa-calendar-exclamation"></i></div><i class="fas fa-chevron-right sc-arrow"></i></div>
            <div class="sc-lbl">Next Due Date</div>
            <div class="sc-val" data-no-counter style="font-size:1.1rem;letter-spacing:0">{{ $data['instalment_summary']['next_due']->due_date->format('d-m-Y') }}</div>
            <div class="sc-sub"><i class="fas fa-receipt"></i> #{{ $data['instalment_summary']['next_due']->instalment_number }} · ${{ number_format($data['instalment_summary']['next_due']->amount, 2) }}</div>
        </div>
        @endif

        @if(isset($data['saving_direct_team_count']))
        <div class="sc sc-green">
            <div class="sc-top"><div class="sc-icon"><i class="fas fa-user-plus"></i></div><i class="fas fa-chevron-right sc-arrow"></i></div>
            <div class="sc-lbl">Direct Team Members</div>
            <div class="sc-val" style="font-size:1.4rem;letter-spacing:0">{{ number_format($data['saving_direct_team_count']) }}</div>
            <div class="sc-sub"><i class="fas fa-circle-check"></i> Your direct saving referrals</div>
        </div>
        @endif

        @if(isset($data['user_saving_team_count']))
        <div class="sc sc-amber">
            <div class="sc-top"><div class="sc-icon"><i class="fas fa-users-rectangle"></i></div><i class="fas fa-chevron-right sc-arrow"></i></div>
            <div class="sc-lbl">My Saving Members</div>
            <div class="sc-val" style="font-size:1.4rem;letter-spacing:0">{{ number_format($data['user_saving_team_count']) }}</div>
            <div class="sc-sub"><i class="fas fa-circle-check"></i> In your saving network</div>
        </div>
        @endif
    </div>
    @endif

    {{-- ── ADMIN SAVING OVERVIEW ─────────────────────── --}}
    @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('super-admin'))
    @if(isset($data['admin_saving_total_invested']))
    <div class="sec">
        <div class="sec-dot" style="background:#d97706"></div>
        <div class="sec-txt">Saving Plan — System Overview</div>
    </div>

    <div class="sg">
        <div class="sc sc-amber">
            <div class="sc-top"><div class="sc-icon"><i class="fas fa-users-rectangle"></i></div><i class="fas fa-chevron-right sc-arrow"></i></div>
            <div class="sc-lbl">Total Saving Members</div>
            <div class="sc-val" style="font-size:1.4rem;letter-spacing:0">{{ number_format($data['admin_saving_total_users']) }}</div>
            <div class="sc-sub"><i class="fas fa-circle-check"></i> Active plan participants</div>
        </div>

        <div class="sc sc-emerald">
            <div class="sc-top"><div class="sc-icon"><i class="fas fa-sack-dollar"></i></div><i class="fas fa-chevron-right sc-arrow"></i></div>
            <div class="sc-lbl">Total Saving Invested</div>
            <div class="sc-val">${{ number_format($data['admin_saving_total_invested'], 2) }}</div>
            <div class="sc-sub"><i class="fas fa-arrow-up"></i> All members combined</div>
        </div>

        <div class="sc sc-blue">
            <div class="sc-top"><div class="sc-icon"><i class="fas fa-chart-line"></i></div><i class="fas fa-chevron-right sc-arrow"></i></div>
            <div class="sc-lbl">Total Saving ROI Paid</div>
            <div class="sc-val">${{ number_format($data['admin_saving_total_roi'], 2) }}</div>
            <div class="sc-sub"><i class="fas fa-calendar-days"></i> Distributed to date</div>
        </div>

        <div class="sc sc-violet">
            <div class="sc-top"><div class="sc-icon"><i class="fas fa-diagram-project"></i></div><i class="fas fa-chevron-right sc-arrow"></i></div>
            <div class="sc-lbl">Total Direct &amp; Indirect</div>
            <div class="sc-val">${{ number_format($data['admin_saving_total_direct'] + $data['admin_saving_total_indirect'], 2) }}</div>
            <div class="sc-sub">D: ${{ number_format($data['admin_saving_total_direct'], 2) }} &nbsp;·&nbsp; I: ${{ number_format($data['admin_saving_total_indirect'], 2) }}</div>
        </div>
    </div>
    @endif
    @endif

    {{-- ── ROI CONTROL (standard + both users only) ───── --}}
    @if(!$isSavingOnly)
    <div class="sec">
        <div class="sec-dot" style="background:#0891b2"></div>
        <div class="sec-txt">Analytics &amp; ROI Control</div>
    </div>

    <div class="roi-grid">
        {{-- 2X --}}
        <div class="roi-card" style="--rc-top:linear-gradient(90deg,#7c3aed,#06b6d4)">
            <div class="roi-head">
                <div class="roi-icon-wrap">
                    <div class="roi-icon" style="background:#f3f0ff;color:#7c3aed"><i class="fas fa-chart-bar"></i></div>
                    <div>
                        <div class="roi-title">2X ROI Progress</div>
                        <div class="roi-sub">Investment return tracker</div>
                    </div>
                </div>
                <span class="badge {{ $data['roi_stats']['has_reached_2x'] ? 'badge-red' : 'badge-green' }}">
                    {{ $data['roi_stats']['has_reached_2x'] ? 'Completed' : 'Active' }}
                </span>
            </div>
            <div class="track">
                <div class="track-fill" style="width:{{ min($data['roi_stats']['completion_percentage'],100) }}%;background:linear-gradient(90deg,#7c3aed,#06b6d4)"></div>
            </div>
            <div class="track-pct">{{ number_format(min($data['roi_stats']['completion_percentage'],100),1) }}%</div>
            <div class="roi-stats">
                <div><div class="rs-val">${{ number_format($data['roi_stats']['invested_amount'],2) }}</div><div class="rs-lbl">Invested</div></div>
                <div><div class="rs-val">${{ number_format($data['roi_stats']['total_roi_paid'],2) }}</div><div class="rs-lbl">Earned</div></div>
                <div><div class="rs-val">${{ number_format($data['roi_stats']['remaining_amount'],2) }}</div><div class="rs-lbl">Left</div></div>
            </div>
            @if($data['roi_stats']['has_reached_2x'])
            <div class="roi-notice rn-g"><i class="fas fa-check-circle"></i> 2X ROI Target Achieved!</div>
            @endif
        </div>

        {{-- 7X --}}
        <div class="roi-card" style="--rc-top:linear-gradient(90deg,#d97706,#db2777)">
            <div class="roi-head">
                <div class="roi-icon-wrap">
                    <div class="roi-icon" style="background:#fffbeb;color:#d97706"><i class="fas fa-shield-alt"></i></div>
                    <div>
                        <div class="roi-title">7X Withdrawal Control</div>
                        <div class="roi-sub">Withdrawal eligibility</div>
                    </div>
                </div>
                <span class="badge {{ $data['roi_stats']['withdrawal_enabled'] ? 'badge-green' : 'badge-amber' }}">
                    {{ $data['roi_stats']['withdrawal_enabled'] ? 'Enabled' : 'Suspended' }}
                </span>
            </div>
            <div class="track">
                <div class="track-fill" style="width:{{ min($data['roi_stats']['completion_7x_percentage'],100) }}%;background:linear-gradient(90deg,#d97706,#db2777)"></div>
            </div>
            <div class="track-pct">{{ number_format(min($data['roi_stats']['completion_7x_percentage'],100),1) }}%</div>
            <div class="roi-stats">
                <div><div class="rs-val">${{ number_format($data['roi_stats']['seven_x_limit'],2) }}</div><div class="rs-lbl">7X Limit</div></div>
                <div><div class="rs-val">${{ number_format($data['roi_stats']['total_roi_paid'],2) }}</div><div class="rs-lbl">Earned</div></div>
                <div><div class="rs-val">${{ number_format($data['roi_stats']['remaining_7x_amount'],2) }}</div><div class="rs-lbl">Until Limit</div></div>
            </div>
            @if(!$data['roi_stats']['withdrawal_enabled'])
            <div class="roi-notice rn-a"><i class="fas fa-ban"></i> Withdrawals suspended — top-up required</div>
            @endif
        </div>
    </div>

    @endif

    {{-- ── TARGETS (standard + both users only) ───────── --}}
    @if(!$isSavingOnly)
    <div class="sec">
        <div class="sec-dot" style="background:#059669"></div>
        <div class="sec-txt">Targets &amp; Progress</div>
    </div>

    <div class="tg-grid">
        @php $r = 70; $c = 2 * M_PI * $r; @endphp

        <div class="tg-card">
            <h3>Reward Target</h3>
            <p>Track your progress towards the next reward milestone</p>
            <div class="ring-w">
                <svg viewBox="0 0 168 168">
                    <circle class="ring-bg" cx="84" cy="84" r="{{ $r }}"/>
                    <circle class="ring-fill" cx="84" cy="84" r="{{ $r }}"
                        stroke="url(#rg1)"
                        stroke-dasharray="{{ $c }}"
                        stroke-dashoffset="{{ $c - ($c * $data['reward'] / 100) }}"/>
                    <defs>
                        <linearGradient id="rg1" x1="0%" y1="0%" x2="100%" y2="0%">
                            <stop offset="0%" stop-color="#7c3aed"/><stop offset="100%" stop-color="#06b6d4"/>
                        </linearGradient>
                    </defs>
                </svg>
                <div class="ring-c">
                    <div class="ring-pct">{{ number_format($data['reward'],1) }}%</div>
                    <div class="ring-s">Complete</div>
                </div>
            </div>
            <button class="tg-btn tg-btn-a" id="btnReward"><i class="fas fa-trophy"></i> View Reward Details</button>
        </div>

        <div class="tg-card">
            <h3>Rank Target</h3>
            <p>Advance to the next leadership level in the network</p>
            <div class="ring-w">
                <svg viewBox="0 0 168 168">
                    <circle class="ring-bg" cx="84" cy="84" r="{{ $r }}"/>
                    <circle class="ring-fill" cx="84" cy="84" r="{{ $r }}"
                        stroke="url(#rg2)"
                        stroke-dasharray="{{ $c }}"
                        stroke-dashoffset="{{ $c }}"/>
                    <defs>
                        <linearGradient id="rg2" x1="0%" y1="0%" x2="100%" y2="0%">
                            <stop offset="0%" stop-color="#2563eb"/><stop offset="100%" stop-color="#7c3aed"/>
                        </linearGradient>
                    </defs>
                </svg>
                <div class="ring-c">
                    <div class="ring-pct">0%</div>
                    <div class="ring-s">Complete</div>
                </div>
            </div>
            <button class="tg-btn tg-btn-b" id="btnRank"><i class="fas fa-crown"></i> View Rank Progress</button>
        </div>
    </div>

    {{-- Reward Panel --}}
    <div class="xpanel" id="panelReward">
        <div class="xp-head">
            <div class="xp-ico" style="background:#f3f0ff;color:#7c3aed"><i class="fas fa-trophy"></i></div>
            Reward Level Progress
        </div>
        <div class="xp-body">
            @foreach($data['levelCount'] as $level => $count)
            @php
                $mx  = [1=>10,2=>50,3=>150,4=>400,5=>1000,6=>2000,7=>4000][$level] ?? 1;
                $rwd = [1=>130,2=>350,3=>1050,4=>3450,5=>8650,6=>26000,7=>41500][$level] ?? 0;
                $pct = min(($count/$mx)*100,100);
            @endphp
            <div class="lv-row">
                <div class="lv-num">{{ $level }}</div>
                <div class="lv-info"><h6>Level {{ $level }} Reward</h6><p>${{ number_format($rwd) }} bonus</p></div>
                <div>
                    <div class="lv-bar"><div class="lv-fill" style="width:{{ $pct }}%"></div></div>
                    <div class="lv-pct">{{ number_format($pct,1) }}% complete</div>
                </div>
                <div class="lv-cnt">{{ $count }}<br><span>/ {{ $mx }}</span></div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Rank Panel --}}
    <div class="xpanel" id="panelRank">
        <div class="xp-head">
            <div class="xp-ico" style="background:#eff6ff;color:#2563eb"><i class="fas fa-crown"></i></div>
            Rank Advancement Progress
        </div>
        <div class="xp-body">
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
            <div class="rank-g">
                @foreach($ranks as $rk)
                @php
                    $tm  = $data['totalTeam'] ?? 0;
                    $rp  = min(($tm/$rk['size'])*100,100);
                    $won = $tm >= $rk['size'];
                    $ip  = public_path('assets/images/ranks/'.$rk['file']);
                    $iu  = file_exists($ip) ? asset('assets/images/ranks/'.$rk['file'])
                         : 'https://placehold.co/52x52/'.$rk['hex'].'/FFFFFF?text='.substr($rk['name'],0,1);
                @endphp
                <div class="rank-i {{ $won ? 'won' : '' }}">
                    <img src="{{ $iu }}" alt="{{ $rk['name'] }}" class="rank-img"
                         onerror="this.src='https://placehold.co/52x52/{{ $rk['hex'] }}/FFFFFF?text={{ substr($rk['name'],0,1) }}'">
                    <div class="rank-n">{{ $rk['name'] }}</div>
                    <div class="rank-r">{{ $rk['req'] }}</div>
                    <div class="rank-tr"><div class="rank-br" style="width:{{ $rp }}%"></div></div>
                    <div class="rank-ct">{{ $tm }} / {{ $rk['size'] }}</div>
                    @if($won)<div class="rank-dn"><i class="fas fa-check"></i> Achieved</div>@endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

</div>{{-- /db-body --}}
</div>{{-- /db --}}
@endsection

@section('page_js')
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ── Panel toggles ───────────────────────────────────── */
    function toggle(show, hide) {
        document.getElementById(hide).classList.remove('open');
        const p = document.getElementById(show);
        const was = p.classList.contains('open');
        p.classList.toggle('open', !was);
        if (!was) setTimeout(() => p.scrollIntoView({ behavior:'smooth', block:'nearest' }), 55);
    }
    document.getElementById('btnReward')?.addEventListener('click', () => toggle('panelReward','panelRank'));
    document.getElementById('btnRank')?.addEventListener('click',   () => toggle('panelRank',  'panelReward'));

    /* ── Counter animation ───────────────────────────────── */
    function countUp(el, target, prefix, dec) {
        const dur = 1500, t0 = performance.now();
        (function step(now) {
            const p = Math.min((now - t0) / dur, 1);
            const e = 1 - Math.pow(1 - p, 3);
            el.textContent = prefix + (target * e).toFixed(dec);
            if (p < 1) requestAnimationFrame(step);
        })(t0);
    }
    function runCounters() {
        document.querySelectorAll('.sc-val, #heroBalance').forEach(el => {
            if (el.hasAttribute('data-no-counter')) return;
            const raw = el.textContent.trim();
            const pfx = raw.startsWith('$') ? '$' : '';
            const num = parseFloat(raw.replace(/[^0-9.]/g,''));
            if (!isNaN(num) && num > 0) {
                const dec = raw.includes('.') ? 2 : 0;
                el.textContent = pfx + (0).toFixed(dec);
                countUp(el, num, pfx, dec);
            }
        });
    }

    /* ── Entrance fade-up ────────────────────────────────── */
    const els = document.querySelectorAll('.sc, .roi-card, .tg-card');
    els.forEach((el, i) => {
        el.style.cssText += `opacity:0;transform:translateY(16px);transition:opacity .4s ease ${i*38}ms,transform .4s ease ${i*38}ms`;
    });
    new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                e.target.style.opacity = '1';
                e.target.style.transform = 'translateY(0)';
            }
        });
    }, { threshold: 0.06 }).observe(document.querySelector('.db-body'));

    /* trigger on all observed --  use a single root observer */
    const io = new IntersectionObserver(entries => {
        entries.forEach(e => {
            if (e.isIntersecting) { e.target.style.opacity='1'; e.target.style.transform='translateY(0)'; io.unobserve(e.target); }
        });
    }, { threshold: 0.06 });
    els.forEach(el => io.observe(el));

    setTimeout(runCounters, 150);
});
</script>
@endsection
