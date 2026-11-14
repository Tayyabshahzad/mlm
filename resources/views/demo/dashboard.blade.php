@extends('demo.layout.app')
@section('content')

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
@import url('https://fonts.googleapis.com/css2?family=Noto+Nastaliq+Urdu:wght@400;600;700&display=swap');

/* ═══════════════════════════════════════════
   GVI DASHBOARD — CareOps Style
═══════════════════════════════════════════ */
:root {
    --bg:      #EEEDF8;
    --card:    #FFFFFF;
    --accent:  #5B5BD6;
    --green:   #10B981;
    --red:     #EF4444;
    --amber:   #F59E0B;
    --t1:      #111827;
    --t2:      #6B7280;
    --t3:      #9CA3AF;
    --bdr:     rgba(0,0,0,.06);
    --r:       16px;
    --sh:      0 2px 8px rgba(0,0,0,.06);
    --sh-h:    0 8px 24px rgba(0,0,0,.10);
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

.db {
    font-family: 'Inter', system-ui, sans-serif;
    background: var(--bg);
    min-height: 100vh;
    color: var(--t1);
    -webkit-font-smoothing: antialiased;
}

/* ── Ticker ─────────────────────────────── */
.ticker { overflow: hidden; background: var(--accent); padding: .32rem 0; }
.ticker-track { display: flex; width: max-content; animation: tick 40s linear infinite; }
.ticker-track:hover { animation-play-state: paused; }
@keyframes tick { to { transform: translateX(-50%); } }
.t-item { display: inline-flex; align-items: center; gap: .5rem; padding: 0 2rem; font-size: .7rem; font-weight: 500; color: rgba(255,255,255,.88); white-space: nowrap; }
.t-sep { width: 3px; height: 3px; border-radius: 50%; background: rgba(255,255,255,.35); }

/* ── Page header ────────────────────────── */
.ph {
    background: var(--card);
    border-bottom: 1px solid var(--bdr);
    padding: 1.1rem 2rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
}
.ph-title { font-size: 1.15rem; font-weight: 800; color: var(--t1); letter-spacing: -.025em; }
.ph-sub   { font-size: .74rem; color: var(--t3); margin-top: .1rem; }
.ph-right { display: flex; align-items: center; gap: .65rem; flex-shrink: 0; }
.ph-chip {
    display: inline-flex; align-items: center; gap: .35rem;
    padding: .26rem .72rem; border-radius: 50px; font-size: .68rem; font-weight: 600;
    border: 1.5px solid;
}
.ph-chip.saving { color: #059669; border-color: rgba(5,150,105,.3); background: #D1FAE5; }
.ph-chip.vip    { color: #D97706; border-color: rgba(217,119,6,.3);  background: #FEF3C7; }
.ph-chip.std    { color: #4F46E5; border-color: rgba(79,70,229,.3);  background: #EDE9FE; }
.ph-date { font-size: .78rem; font-weight: 600; color: var(--t2); }
.ph-date small { display: block; font-size: .68rem; color: var(--t3); font-weight: 400; }

/* ── Body ───────────────────────────────── */
.db-body { max-width: 1320px; margin: 0 auto; padding: 1.75rem 1.75rem 4rem; }

/* ── Alert banners ──────────────────────── */
.alert-bar {
    display: flex; align-items: center; gap: .85rem;
    padding: .85rem 1.15rem; border-radius: var(--r); margin-bottom: .85rem;
    border: 1.5px solid; text-decoration: none; color: inherit;
}
.alert-bar.warn { background: #FFFBEB; border-color: rgba(245,158,11,.25); }
.alert-bar.danger { background: #FEF2F2; border-color: rgba(239,68,68,.25); }
.alert-bar-icon { font-size: 1.15rem; flex-shrink: 0; }
.alert-bar strong { font-size: .82rem; font-weight: 700; display: block; margin-bottom: .06rem; }
.alert-bar small  { font-size: .72rem; color: var(--t2); }
.alert-bar-cta {
    margin-left: auto; flex-shrink: 0;
    font-size: .72rem; font-weight: 600;
    padding: .28rem .72rem; border-radius: 7px;
    background: rgba(0,0,0,.06);
    color: var(--t1);
}

/* ── Section head ───────────────────────── */
.sec-h {
    font-size: .68rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .1em; color: var(--t3);
    margin-bottom: .85rem; margin-top: 1.6rem;
    display: flex; align-items: center; gap: .5rem;
}
.sec-h:first-child { margin-top: 0; }
.sec-h::after { content: ''; flex: 1; height: 1px; background: rgba(0,0,0,.08); }

/* ══════════════════════════════════════════
   TOP KPI ROW  (like CareOps top metrics)
══════════════════════════════════════════ */
.kpi-row { display: grid; gap: .85rem; margin-bottom: .85rem; }
.kpi-5   { grid-template-columns: repeat(5,1fr); }
.kpi-4   { grid-template-columns: repeat(4,1fr); }
.kpi-3   { grid-template-columns: repeat(3,1fr); }

.kpi {
    background: var(--card);
    border-radius: var(--r);
    padding: 1.25rem 1.3rem;
    box-shadow: var(--sh);
    border: 1px solid var(--bdr);
    transition: box-shadow .18s, transform .18s;
}
.kpi:hover { box-shadow: var(--sh-h); transform: translateY(-2px); }

.kpi-lbl {
    font-size: .68rem; font-weight: 600;
    color: var(--t3); text-transform: uppercase; letter-spacing: .06em;
    margin-bottom: .55rem;
}
.kpi-val {
    font-size: 1.7rem; font-weight: 800; letter-spacing: -.035em;
    color: var(--t1); line-height: 1; margin-bottom: .4rem;
}
.kpi-val.md { font-size: 1.35rem; letter-spacing: -.02em; }
.kpi-trend {
    display: inline-flex; align-items: center; gap: .25rem;
    font-size: .72rem; font-weight: 600;
}
.kpi-trend.up   { color: var(--green); }
.kpi-trend.down { color: var(--red);   }
.kpi-trend.neu  { color: var(--t3);    }

/* ══════════════════════════════════════════
   CONTENT GRID
══════════════════════════════════════════ */
.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: .85rem; margin-bottom: .85rem; }
.grid-3 { display: grid; grid-template-columns: repeat(3,1fr); gap: .85rem; margin-bottom: .85rem; }

/* ── Card base ──────────────────────────── */
.card {
    background: var(--card);
    border-radius: var(--r);
    box-shadow: var(--sh);
    border: 1px solid var(--bdr);
    overflow: hidden;
}
.card-body { padding: 1.4rem 1.5rem; }
.card-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 1rem 1.5rem; border-bottom: 1px solid var(--bdr);
}
.card-head h4 {
    font-size: .88rem; font-weight: 700; color: var(--t1);
    display: flex; align-items: center; gap: .5rem;
}
.card-head .chip {
    font-size: .62rem; font-weight: 700; padding: .15rem .55rem;
    border-radius: 50px; text-transform: uppercase; letter-spacing: .04em;
}
.chip-green  { background: #D1FAE5; color: #059669; }
.chip-amber  { background: #FEF3C7; color: #D97706; }
.chip-red    { background: #FEE2E2; color: #DC2626; }
.chip-blue   { background: #DBEAFE; color: #2563EB; }
.chip-purple { background: #EDE9FE; color: #6D28D9; }

/* ── Wallet list ────────────────────────── */
.wallet-list { display: flex; flex-direction: column; gap: 0; }
.wl-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: .8rem 0; border-bottom: 1px solid rgba(0,0,0,.05);
}
.wl-row:last-child { border-bottom: none; }
.wl-left { display: flex; align-items: center; gap: .7rem; }
.wl-dot  { width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0; }
.wl-lbl  { font-size: .8rem; font-weight: 500; color: var(--t2); }
.wl-val  { font-size: .9rem; font-weight: 700; color: var(--t1); }
.wl-sub  { font-size: .68rem; color: var(--t3); margin-top: .04rem; }
.wl-bar-wrap { padding: .35rem 0; }
.wl-bar  { height: 5px; background: rgba(0,0,0,.06); border-radius: 99px; overflow: hidden; }
.wl-fill { height: 100%; border-radius: 99px; }

/* ── Big stat (for total earnings) ─────── */
.big-stat-card {
    background: var(--accent);
    border-radius: var(--r);
    padding: 1.75rem 1.75rem;
    color: #fff;
    box-shadow: 0 4px 20px rgba(91,91,214,.35);
    position: relative; overflow: hidden;
}
.big-stat-card::before {
    content: '';
    position: absolute; top: -40px; right: -40px;
    width: 160px; height: 160px;
    border-radius: 50%;
    background: rgba(255,255,255,.08);
}
.big-stat-card::after {
    content: '';
    position: absolute; bottom: -60px; right: 40px;
    width: 120px; height: 120px;
    border-radius: 50%;
    background: rgba(255,255,255,.05);
}
.bsc-lbl { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; opacity: .7; margin-bottom: .6rem; }
.bsc-val { font-size: 2.8rem; font-weight: 900; letter-spacing: -.05em; line-height: 1; margin-bottom: .5rem; }
.bsc-sub { font-size: .76rem; opacity: .75; display: flex; align-items: center; gap: .35rem; }
.bsc-live { width: 7px; height: 7px; border-radius: 50%; background: #34D399; box-shadow: 0 0 6px #34D399; }

/* ── Progress bar row ───────────────────── */
.prog-row { padding: .9rem 0; border-bottom: 1px solid rgba(0,0,0,.05); }
.prog-row:last-child { border-bottom: none; }
.prog-row-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: .45rem; }
.prog-row-lbl { font-size: .78rem; font-weight: 500; color: var(--t2); }
.prog-row-val { font-size: .78rem; font-weight: 700; color: var(--t1); }
.prog-track { height: 5px; background: rgba(0,0,0,.07); border-radius: 99px; overflow: hidden; }
.prog-fill  { height: 100%; border-radius: 99px; }

/* ── ROI cards ──────────────────────────── */
.roi-c {
    background: var(--card);
    border-radius: var(--r);
    border: 1px solid var(--bdr);
    box-shadow: var(--sh);
    overflow: hidden;
}
.roi-c-top {
    padding: 1.2rem 1.4rem;
    border-bottom: 1px solid var(--bdr);
    display: flex; align-items: flex-start; justify-content: space-between;
}
.roi-c-title { font-size: .88rem; font-weight: 700; color: var(--t1); margin-bottom: .1rem; }
.roi-c-sub   { font-size: .68rem; color: var(--t3); }
.roi-c-body  { padding: 1.2rem 1.4rem; }
.roi-nums {
    display: grid; grid-template-columns: repeat(3,1fr);
    gap: .5rem; margin-top: 1rem; padding-top: 1rem;
    border-top: 1px solid rgba(0,0,0,.06);
}
.rn-v { font-size: .95rem; font-weight: 800; color: var(--t1); }
.rn-l { font-size: .62rem; color: var(--t3); text-transform: uppercase; letter-spacing: .05em; margin-top: .05rem; }
.roi-notice {
    display: flex; align-items: center; gap: .4rem;
    margin-top: .85rem; padding: .42rem .75rem;
    border-radius: 8px; font-size: .72rem; font-weight: 600;
}
.rn-g { background: #D1FAE5; color: #059669; }
.rn-a { background: #FEF3C7; color: #D97706; }

/* ── Ring ───────────────────────────────── */
.ring-card {
    background: var(--card); border-radius: var(--r);
    border: 1px solid var(--bdr); box-shadow: var(--sh);
    padding: 1.5rem; text-align: center;
    transition: box-shadow .18s, transform .18s;
}
.ring-card:hover { box-shadow: var(--sh-h); transform: translateY(-2px); }
.ring-card h3 { font-size: .88rem; font-weight: 700; color: var(--t1); margin-bottom: .15rem; }
.ring-card p  { font-size: .72rem; color: var(--t3); margin-bottom: 1.2rem; }
.ring-w { position: relative; width: 128px; height: 128px; margin: 0 auto 1.2rem; }
.ring-w svg { width: 100%; height: 100%; transform: rotate(-90deg); }
.ring-bg   { fill: none; stroke: rgba(0,0,0,.07); stroke-width: 8; }
.ring-fill-el { fill: none; stroke-width: 8; stroke-linecap: round; }
.ring-c { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; }
.ring-pct { font-size: 1.5rem; font-weight: 900; color: var(--t1); letter-spacing: -.04em; line-height: 1; }
.ring-s   { font-size: .6rem; font-weight: 600; color: var(--t3); text-transform: uppercase; letter-spacing: .07em; margin-top: .2rem; }
.ring-btn {
    width: 100%; padding: .6rem; border: 1.5px solid rgba(0,0,0,.1);
    border-radius: 9px; font-family: inherit; font-size: .77rem; font-weight: 600;
    cursor: pointer; background: transparent; color: var(--t2);
    display: inline-flex; align-items: center; justify-content: center; gap: .4rem;
    transition: all .15s;
}
.ring-btn:hover { background: var(--accent); border-color: var(--accent); color: #fff; }

/* ── Expandable ─────────────────────────── */
.xpanel { background: var(--card); border: 1px solid var(--bdr); border-radius: var(--r); box-shadow: var(--sh); margin-bottom: .85rem; display: none; }
.xpanel.open { display: block; animation: fup .2s ease; }
@keyframes fup { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:none; } }
.xp-head { padding: .9rem 1.4rem; border-bottom: 1px solid var(--bdr); font-size: .85rem; font-weight: 700; color: var(--t1); display: flex; align-items: center; gap: .5rem; }
.xp-ico  { width: 28px; height: 28px; border-radius: 7px; display: flex; align-items: center; justify-content: center; font-size: .72rem; }
.xp-body { padding: 1.15rem 1.4rem; }

.lv-row { display: grid; grid-template-columns: 36px 1fr 140px 60px; gap: .75rem; align-items: center; padding: .6rem .75rem; border-radius: 9px; margin-bottom: .28rem; transition: background .12s; }
.lv-row:hover { background: var(--bg); }
.lv-num { width: 36px; height: 36px; border-radius: 9px; background: var(--accent); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: .88rem; flex-shrink: 0; }
.lv-info h6 { font-size: .77rem; font-weight: 700; color: var(--t1); margin-bottom: .03rem; }
.lv-info p  { font-size: .66rem; color: var(--t3); }
.lv-bar { height: 4px; background: rgba(0,0,0,.07); border-radius: 99px; overflow: hidden; margin-bottom: .14rem; }
.lv-fill{ height: 100%; border-radius: 99px; background: var(--accent); }
.lv-pct { font-size: .6rem; color: var(--t3); font-weight: 600; }
.lv-cnt { font-size: .84rem; font-weight: 800; color: var(--t1); text-align: right; }
.lv-cnt span { font-size: .63rem; color: var(--t3); font-weight: 500; }

.rank-g { display: grid; grid-template-columns: repeat(auto-fill,minmax(108px,1fr)); gap: .7rem; }
.rank-i { text-align: center; padding: 1rem .7rem; border-radius: 12px; border: 1.5px solid rgba(0,0,0,.08); background: var(--bg); transition: all .18s; }
.rank-i:hover { transform: translateY(-2px); box-shadow: var(--sh-h); background: var(--card); }
.rank-i.won { border-color: rgba(16,185,129,.3); background: #D1FAE5; }
.rank-img { width: 42px; height: 42px; object-fit: contain; display: block; margin: 0 auto .45rem; }
.rank-n  { font-size: .73rem; font-weight: 700; color: var(--t1); margin-bottom: .1rem; }
.rank-r  { font-size: .61rem; color: var(--t3); margin-bottom: .5rem; line-height: 1.4; }
.rank-tr { height: 3px; background: rgba(0,0,0,.08); border-radius: 99px; overflow: hidden; margin-bottom: .25rem; }
.rank-br { height: 100%; border-radius: 99px; background: var(--accent); }
.rank-ct { font-size: .61rem; color: var(--t3); font-weight: 600; }
.rank-won-b { display: inline-flex; align-items: center; gap: .15rem; background: #D1FAE5; color: #059669; border-radius: 50px; padding: .1rem .42rem; font-size: .59rem; font-weight: 700; margin-top: .22rem; }

/* ── Responsive ─────────────────────────── */
@media (max-width: 700px) {
    .kpi-5,.kpi-4,.kpi-3 { grid-template-columns: 1fr 1fr; }
    .grid-2,.grid-3 { grid-template-columns: 1fr; }
    .ph { padding: .9rem 1rem; }
    .db-body { padding: 1rem 1rem 3rem; }
    .lv-row { grid-template-columns: 34px 1fr; }
    .lv-row > :nth-child(3), .lv-row > :nth-child(4) { display: none; }
}
@media (min-width: 701px) and (max-width: 960px) {
    .kpi-5 { grid-template-columns: repeat(3,1fr); }
    .kpi-4 { grid-template-columns: repeat(2,1fr); }
    .grid-2 { grid-template-columns: 1fr; }
    .grid-3 { grid-template-columns: repeat(2,1fr); }
}
/* ── Event banner (launch ceremony + anniversary) ─────────── */
.ev {
    position: relative; overflow: hidden; border-radius: 20px; margin-bottom: 1.25rem;
    background: radial-gradient(120% 140% at 0% 0%, #2B2B8F 0%, #1A1A5E 45%, #0F1238 100%);
    color: #fff; box-shadow: 0 18px 40px -18px rgba(27, 27, 94, .55);
    border: 1px solid rgba(245, 196, 81, .35);
}
.ev::before, .ev::after {
    content: ''; position: absolute; border-radius: 50%; pointer-events: none; filter: blur(2px);
}
.ev::before { width: 340px; height: 340px; right: -110px; top: -150px; background: radial-gradient(circle, rgba(245,196,81,.30), transparent 65%); }
.ev::after  { width: 280px; height: 280px; left: -90px; bottom: -150px; background: radial-gradient(circle, rgba(91,91,214,.55), transparent 65%); }
.ev-confetti { position: absolute; inset: 0; pointer-events: none; opacity: .55;
    background-image:
        radial-gradient(circle, #F5C451 1.6px, transparent 2px),
        radial-gradient(circle, #ffffff 1.2px, transparent 1.6px),
        radial-gradient(circle, #10B981 1.4px, transparent 1.8px);
    background-size: 90px 90px, 130px 130px, 170px 170px;
    background-position: 10px 20px, 60px 70px, 30px 110px;
}
.ev-inner { position: relative; z-index: 1; padding: 1.6rem 1.75rem 1.4rem; }
.ev-top { display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; }
.ev-badge { display: inline-flex; align-items: center; gap: .45rem; padding: .35rem .8rem; border-radius: 999px;
    background: rgba(245,196,81,.15); border: 1px solid rgba(245,196,81,.45); color: #F5C451;
    font-size: .72rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
.ev-badge .dot { width: 7px; height: 7px; border-radius: 50%; background: #F5C451; box-shadow: 0 0 0 4px rgba(245,196,81,.2); animation: evPulse 1.8s ease-in-out infinite; }
@keyframes evPulse { 50% { box-shadow: 0 0 0 7px rgba(245,196,81,0); } }
.ev-date { font-size: .78rem; color: rgba(255,255,255,.7); font-weight: 600; }
.ev-title { margin-top: .9rem; font-family: 'Noto Nastaliq Urdu', serif; direction: rtl; text-align: center;
    font-size: clamp(1.45rem, 2.6vw, 2.1rem); line-height: 2.1; font-weight: 700;
    background: linear-gradient(90deg, #FFE7A3, #F5C451, #FFE7A3); -webkit-background-clip: text; background-clip: text; color: transparent; }
.ev-sub { text-align: center; color: rgba(255,255,255,.75); font-size: .85rem; margin-top: -.2rem; letter-spacing: .02em; }
.ev-cards { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; margin-top: 1.2rem; }
.ev-card { direction: rtl; text-align: right; border-radius: 16px; padding: 1.1rem 1.25rem 1rem;
    background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.14); backdrop-filter: blur(4px); }
.ev-card-head { display: flex; align-items: center; gap: .75rem; }
.ev-ico { flex: none; width: 46px; height: 46px; border-radius: 14px; display: grid; place-items: center; font-size: 1.45rem;
    background: linear-gradient(135deg, rgba(245,196,81,.35), rgba(245,196,81,.08)); border: 1px solid rgba(245,196,81,.4); }
.ev-card h3 { font-family: 'Noto Nastaliq Urdu', serif; font-size: 1.08rem; line-height: 2; font-weight: 700; color: #fff; }
.ev-card .en { display: block; direction: ltr; text-align: right; font-family: 'Inter', sans-serif; font-size: .7rem; font-weight: 700;
    letter-spacing: .08em; text-transform: uppercase; color: #F5C451; margin-top: -.25rem; }
.ev-card p { font-family: 'Noto Nastaliq Urdu', serif; font-size: .92rem; line-height: 2.2; color: rgba(255,255,255,.85); margin-top: .55rem; }
.ev-foot { direction: rtl; margin-top: 1.1rem; padding-top: .9rem; border-top: 1px dashed rgba(255,255,255,.18);
    display: flex; align-items: center; justify-content: center; gap: .6rem; flex-wrap: wrap; text-align: center;
    font-family: 'Noto Nastaliq Urdu', serif; font-size: .95rem; line-height: 2.1; color: #fff; }
.ev-foot b { color: #F5C451; font-weight: 700; }
@media (max-width: 768px) {
    .ev-inner { padding: 1.2rem 1rem 1.1rem; }
    .ev-cards { grid-template-columns: 1fr; }
    .ev-top { justify-content: center; }
}
@media (prefers-reduced-motion: reduce) { .ev-badge .dot { animation: none; } }
</style>

<div class="db">
@php $isSavingOnly = ($data['account_type'] ?? null) === 'saving'; @endphp

{{-- ── TICKER ───────────────────────────── --}}
<div class="ticker">
    <div class="ticker-track">
        @for ($i = 0; $i < 2; $i++){{-- duplicated once so the scrolling loop is seamless --}}
        <span class="t-item">🚀 Aaj Global Visioners Tradings &amp; Educations ki Launching Ceremony hai — ek naye safar ka aaghaz! <span class="t-sep"></span></span>
        <span class="t-item">🎂 Al-Awan ki Doosri Saalgirah Mubarak — do saal ka kamyaab safar, aap sab ke saath <span class="t-sep"></span></span>
        <span class="t-item">✨ آج دوہری خوشی کا دن — گلوبل وژنرز ٹریڈنگز اینڈ ایجوکیشنز کی لانچنگ اور الاعوان کی دوسری سالگرہ <span class="t-sep"></span></span>
        <span class="t-item">💐 GVI family ki taraf se tamam members ko dil ki gehraiyon se mubarakbaad <span class="t-sep"></span></span>
        @endfor
    </div>
</div>

{{-- ── PAGE HEADER ──────────────────────── --}}
<div class="ph">
    <div>
        <div class="ph-title">Dashboard</div>
        <div class="ph-sub">Welcome back, {{ Auth::user()->name }}</div>
    </div>
    <div class="ph-right">
        @if($data['user_plan'] === 'vip')
            <span class="ph-chip vip"><i class="fas fa-crown"></i> VIP Gold</span>
        @elseif($data['user_plan'] === 'saving')
            <span class="ph-chip saving"><i class="fas fa-piggy-bank"></i> Saving Plan</span>
        @else
            <span class="ph-chip std"><i class="fas fa-gem"></i> Standard</span>
        @endif
        <div class="ph-date">
            {{ now()->format('d M Y') }}
            <small>{{ now()->format('l') }}</small>
        </div>
    </div>
</div>

{{-- ── BODY ─────────────────────────────── --}}
<div class="db-body">

    {{-- ── EVENT BANNER: launch ceremony + Al-Awan 2nd anniversary (shows until end of 14 Nov 2026) ── --}}
    @if(now()->lte(\Carbon\Carbon::parse('2026-11-14 23:59:59', config('app.timezone'))))
    <section class="ev" aria-label="Today's celebrations">
        <div class="ev-confetti"></div>
        <div class="ev-inner">
            <div class="ev-top">
                <span class="ev-badge"><span class="dot"></span> Today's Celebration</span>
                <span class="ev-date">{{ now()->format('l, d F Y') }}</span>
            </div>

            <h2 class="ev-title">آج دوہری خوشی کا دن ہے — مبارک ہو!</h2>
            <div class="ev-sub">A double celebration for the entire GVI family</div>

            <div class="ev-cards">
                <div class="ev-card">
                    <div class="ev-card-head">
                        <div class="ev-ico">🚀</div>
                        <div>
                            <h3>گلوبل وژنرز ٹریڈنگز اینڈ ایجوکیشنز</h3>
                            <span class="en">Launching Ceremony</span>
                        </div>
                    </div>
                    <p>آج ہم ایک نئے سفر کا آغاز کر رہے ہیں۔ فاریکس ٹریڈنگ کی معیاری تعلیم اور عملی رہنمائی اب آپ کی دسترس میں ہے — سیکھیں، آگے بڑھیں اور کامیابی کی نئی منزلیں طے کریں۔</p>
                </div>

                <div class="ev-card">
                    <div class="ev-card-head">
                        <div class="ev-ico">🎂</div>
                        <div>
                            <h3>الاعوان کی دوسری سالگرہ</h3>
                            <span class="en">2nd Anniversary · Cake Cutting</span>
                        </div>
                    </div>
                    <p>دو سال کا یہ کامیاب سفر آپ سب کے اعتماد، محنت اور ساتھ کا ثمر ہے۔ آئیے کیک کٹنگ کی اس خوشی میں مل کر شریک ہوں اور آنے والے سالوں کو مزید روشن بنائیں۔</p>
                </div>
            </div>

            <div class="ev-foot">
                <span>💐</span>
                <span><b>GVI فیملی</b> کی جانب سے تمام ممبران کو دلی مبارکباد — آپ کا ساتھ ہی ہماری اصل طاقت ہے</span>
                <span>💐</span>
            </div>
        </div>
    </section>
    @endif

    {{-- Alerts --}}
    @php $kycUser = auth()->user(); @endphp
    @if(in_array($kycUser->kyc_status, ['pending', 'rejected']))
    <a href="{{ route('kyc.show') }}" class="alert-bar {{ $kycUser->kyc_status === 'rejected' ? 'danger' : 'warn' }}">
        <div class="alert-bar-icon">{{ $kycUser->kyc_status === 'rejected' ? '❌' : '🪪' }}</div>
        <div>
            <strong style="color:{{ $kycUser->kyc_status === 'rejected' ? '#DC2626' : '#D97706' }}">
                {{ $kycUser->kyc_status === 'rejected' ? 'KYC Rejected — Re-upload Required' : 'KYC Verification Required' }}
            </strong>
            <small>
                @if($kycUser->kyc_status === 'rejected' && $kycUser->kyc_rejection_reason)
                    Reason: {{ $kycUser->kyc_rejection_reason }}. Please re-upload your CNIC photos.
                @else
                    Please upload CNIC front & back to complete verification.
                @endif
            </small>
        </div>
        <div class="alert-bar-cta">Upload Now →</div>
    </a>
    @endif

    @role('admin')
    {{-- @if($data['missed_roi_count'] > 0)
    <a href="{{ route('roi.submission.monitoring') }}" class="alert-bar danger">
        <div class="alert-bar-icon">⚠️</div>
        <div>
            <strong style="color:#DC2626">ROI Submissions Missing</strong>
            <small>{{ $data['missed_roi_count'] }} users have not received ROI today.</small>
        </div>
        <div class="alert-bar-cta">Review →</div>
    </a>
    @endif --}}
    @endrole

    {{-- ═══════════════════════════════════════
         STANDARD / BOTH — WALLET OVERVIEW
    ═══════════════════════════════════════ --}}
    @if(!$isSavingOnly)

    {{-- Top KPI row (like CareOps top metrics) --}}
    <div class="sec-h">Overview</div>

    <div class="kpi-row kpi-5" style="margin-bottom:1.5rem">
        <div class="kpi">
            <div class="kpi-lbl">Total Earnings</div>
            <div class="kpi-val">${{ number_format($data['total_earning'], 2) }}</div>
            <div class="kpi-trend up"><i class="fas fa-arrow-up"></i> All-time cumulative</div>
        </div>
        <div class="kpi">
            <div class="kpi-lbl">Online Wallet</div>
            <div class="kpi-val">${{ number_format($data['online_wallet'], 2) }}</div>
            <div class="kpi-trend neu">Available balance</div>
        </div>
        <div class="kpi">
            <div class="kpi-lbl">ROI Earnings</div>
            <div class="kpi-val">${{ number_format($data['roi'], 2) }}</div>
            <div class="kpi-trend up"><i class="fas fa-arrow-up"></i> Return on investment</div>
        </div>
        <div class="kpi">
            <div class="kpi-lbl">Team Size</div>
            <div class="kpi-val md">{{ number_format($data['totalTeam']) }}</div>
            <div class="kpi-trend neu">Active members</div>
        </div>
        <div class="kpi">
            <div class="kpi-lbl">Your Rank</div>
            <div class="kpi-val md">VISIONER</div>
            <div class="kpi-trend neu">Active status</div>
        </div>
    </div>

    {{-- Second row: big earnings card + wallet breakdown --}}
    <div class="grid-2" style="margin-bottom:1.5rem">

        {{-- Left: Total Earnings hero card --}}
        <div class="big-stat-card">
            <div class="bsc-lbl">Total Portfolio Value</div>
            <div class="bsc-val" id="heroBalance">${{ number_format($data['total_earning'], 2) }}</div>
            <div class="bsc-sub">
                <span class="bsc-live"></span>
                Online Wallet: ${{ number_format($data['online_wallet'], 2) }}
            </div>

            <div style="margin-top:1.5rem;display:grid;grid-template-columns:1fr 1fr;gap:.75rem">
                <div style="background:rgba(255,255,255,.12);border-radius:10px;padding:.9rem 1rem">
                    <div style="font-size:.65rem;font-weight:600;opacity:.7;text-transform:uppercase;letter-spacing:.07em;margin-bottom:.3rem">Direct / Indirect</div>
                    <div style="font-size:1.15rem;font-weight:800">${{ number_format($data['direct_indirect'], 2) }}</div>
                </div>
                <div style="background:rgba(255,255,255,.12);border-radius:10px;padding:.9rem 1rem">
                    <div style="font-size:.65rem;font-weight:600;opacity:.7;text-transform:uppercase;letter-spacing:.07em;margin-bottom:.3rem">Profit Sharing</div>
                    <div style="font-size:1.15rem;font-weight:800">${{ number_format($data['profit_share'], 2) }}</div>
                </div>
                <div style="background:rgba(255,255,255,.12);border-radius:10px;padding:.9rem 1rem">
                    <div style="font-size:.65rem;font-weight:600;opacity:.7;text-transform:uppercase;letter-spacing:.07em;margin-bottom:.3rem">Rewards</div>
                    <div style="font-size:1.15rem;font-weight:800">${{ number_format($data['rewardWallet'], 2) }}</div>
                </div>
                <div style="background:rgba(255,255,255,.12);border-radius:10px;padding:.9rem 1rem">
                    <div style="font-size:.65rem;font-weight:600;opacity:.7;text-transform:uppercase;letter-spacing:.07em;margin-bottom:.3rem">Incentive</div>
                    <div style="font-size:1.15rem;font-weight:800">${{ number_format($data['designation_incentive'], 2) }}</div>
                </div>
            </div>
        </div>

        {{-- Right: Wallet breakdown list --}}
        <div class="card">
            <div class="card-head">
                <h4><i class="fas fa-chart-pie" style="color:var(--accent)"></i> Earnings Breakdown</h4>
            </div>
            <div class="card-body">
                @php
                    $total = max($data['total_earning'], 0.01);
                    $wallets = [
                        ['label' => 'ROI Earnings',           'val' => $data['roi'],                    'color' => '#5B5BD6'],
                        ['label' => 'Direct / Indirect',      'val' => $data['direct_indirect'],        'color' => '#10B981'],
                        ['label' => 'Profit Sharing',         'val' => $data['profit_share'],           'color' => '#F59E0B'],
                        ['label' => 'Rewards',                'val' => $data['rewardWallet'],           'color' => '#EC4899'],
                        ['label' => 'Designation Incentive',  'val' => $data['designation_incentive'], 'color' => '#06B6D4'],
                    ];
                @endphp
                <div class="wallet-list">
                    @foreach($wallets as $w)
                    <div class="wl-row">
                        <div class="wl-left">
                            <span class="wl-dot" style="background:{{ $w['color'] }}"></span>
                            <div>
                                <div class="wl-lbl">{{ $w['label'] }}</div>
                                <div class="wl-bar-wrap" style="width:100px">
                                    <div class="wl-bar">
                                        <div class="wl-fill" style="width:{{ min(($w['val']/$total)*100,100) }}%;background:{{ $w['color'] }}"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="wl-val">${{ number_format($w['val'], 2) }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════
         ROI CONTROL
    ═══════════════════════════════════════ --}}
    <div class="sec-h">Analytics &amp; ROI Control</div>

    <div class="grid-2" style="margin-bottom:1.5rem">
        {{-- 2X --}}
        <div class="roi-c">
            <div class="roi-c-top">
                <div>
                    <div class="roi-c-title">2X ROI Progress</div>
                    <div class="roi-c-sub">Investment return tracker</div>
                </div>
                <span class="chip {{ $data['roi_stats']['has_reached_2x'] ? 'chip-red' : 'chip-green' }}">
                    {{ $data['roi_stats']['has_reached_2x'] ? 'Completed' : 'Active' }}
                </span>
            </div>
            <div class="roi-c-body">
                <div style="display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:.45rem">
                    <span style="font-size:.72rem;color:var(--t3)">Progress</span>
                    <span style="font-size:.88rem;font-weight:700;color:var(--t1)">{{ number_format(min($data['roi_stats']['completion_percentage'],100),1) }}%</span>
                </div>
                <div class="prog-track">
                    <div class="prog-fill" style="width:{{ min($data['roi_stats']['completion_percentage'],100) }}%;background:var(--accent)"></div>
                </div>
                <div class="roi-nums">
                    <div><div class="rn-v">${{ number_format($data['roi_stats']['invested_amount'],2) }}</div><div class="rn-l">Invested</div></div>
                    <div><div class="rn-v">${{ number_format($data['roi_stats']['total_roi_paid'],2) }}</div><div class="rn-l">Earned</div></div>
                    <div><div class="rn-v">${{ number_format($data['roi_stats']['remaining_amount'],2) }}</div><div class="rn-l">Remaining</div></div>
                </div>
                @if($data['roi_stats']['has_reached_2x'])
                <div class="roi-notice rn-g"><i class="fas fa-check-circle"></i> 2X ROI Target Achieved!</div>
                @endif
            </div>
        </div>

        {{-- 7X --}}
        <div class="roi-c">
            <div class="roi-c-top">
                <div>
                    <div class="roi-c-title">7X Withdrawal Control</div>
                    <div class="roi-c-sub">Withdrawal eligibility status</div>
                </div>
                <span class="chip {{ $data['roi_stats']['withdrawal_enabled'] ? 'chip-green' : 'chip-amber' }}">
                    {{ $data['roi_stats']['withdrawal_enabled'] ? 'Enabled' : 'Suspended' }}
                </span>
            </div>
            <div class="roi-c-body">
                <div style="display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:.45rem">
                    <span style="font-size:.72rem;color:var(--t3)">Progress to 7X limit</span>
                    <span style="font-size:.88rem;font-weight:700;color:var(--t1)">{{ number_format(min($data['roi_stats']['completion_7x_percentage'],100),1) }}%</span>
                </div>
                <div class="prog-track">
                    <div class="prog-fill" style="width:{{ min($data['roi_stats']['completion_7x_percentage'],100) }}%;background:#F59E0B"></div>
                </div>
                <div class="roi-nums">
                    <div><div class="rn-v">${{ number_format($data['roi_stats']['seven_x_limit'],2) }}</div><div class="rn-l">7X Limit</div></div>
                    <div><div class="rn-v">${{ number_format($data['roi_stats']['total_roi_paid'],2) }}</div><div class="rn-l">Earned</div></div>
                    <div><div class="rn-v">${{ number_format($data['roi_stats']['remaining_7x_amount'],2) }}</div><div class="rn-l">Until Limit</div></div>
                </div>
                @if(!$data['roi_stats']['withdrawal_enabled'])
                <div class="roi-notice rn-a"><i class="fas fa-ban"></i> Withdrawals suspended — top-up required</div>
                @endif
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════
         TARGETS
    ═══════════════════════════════════════ --}}
    <div class="sec-h">Targets &amp; Progress</div>

    <div class="grid-2" style="margin-bottom:1.5rem">
        @php $r = 60; $c = 2 * M_PI * $r; @endphp

        <div class="ring-card">
            <h3>Reward Target</h3>
            <p>Track progress towards next reward milestone</p>
            <div class="ring-w">
                <svg viewBox="0 0 144 144">
                    <circle class="ring-bg" cx="72" cy="72" r="{{ $r }}"/>
                    <circle class="ring-fill-el" cx="72" cy="72" r="{{ $r }}"
                        stroke="var(--accent)"
                        stroke-dasharray="{{ $c }}"
                        stroke-dashoffset="{{ $c - ($c * $data['reward'] / 100) }}"/>
                </svg>
                <div class="ring-c">
                    <div class="ring-pct">{{ number_format($data['reward'],1) }}%</div>
                    <div class="ring-s">Complete</div>
                </div>
            </div>
            <button class="ring-btn" id="btnReward"><i class="fas fa-trophy"></i> View Reward Details</button>
        </div>

        <div class="ring-card">
            <h3>Rank Target</h3>
            <p>Advance to the next leadership level in the network</p>
            <div class="ring-w">
                <svg viewBox="0 0 144 144">
                    <circle class="ring-bg" cx="72" cy="72" r="{{ $r }}"/>
                    <circle class="ring-fill-el" cx="72" cy="72" r="{{ $r }}"
                        stroke="#10B981"
                        stroke-dasharray="{{ $c }}"
                        stroke-dashoffset="{{ $c }}"/>
                </svg>
                <div class="ring-c">
                    <div class="ring-pct">0%</div>
                    <div class="ring-s">Complete</div>
                </div>
            </div>
            <button class="ring-btn" id="btnRank"><i class="fas fa-crown"></i> View Rank Progress</button>
        </div>
    </div>

    {{-- Reward panel --}}
    <div class="xpanel" id="panelReward">
        <div class="xp-head">
            <div class="xp-ico" style="background:#EDE9FE;color:var(--accent)"><i class="fas fa-trophy"></i></div>
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

    {{-- Rank panel --}}
    <div class="xpanel" id="panelRank">
        <div class="xp-head">
            <div class="xp-ico" style="background:#D1FAE5;color:#059669"><i class="fas fa-crown"></i></div>
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
                    @if($won)<div class="rank-won-b"><i class="fas fa-check"></i> Achieved</div>@endif
                </div>
                @endforeach
            </div>
        </div>
    </div>

    @endif {{-- /!isSavingOnly --}}

    {{-- ═══════════════════════════════════════
         SAVING PLAN
    ═══════════════════════════════════════ --}}
    @if(!empty($data['saving_enrolled']))
    <div class="sec-h">Welfare Smart Savings Plan</div>

    <div class="kpi-row kpi-3" style="margin-bottom:1rem">
        <div class="kpi">
            <div class="kpi-lbl">Total Deposited</div>
            <div class="kpi-val">${{ number_format($data['saving_deposit'] ?? 0, 2) }}</div>
            <div class="kpi-trend neu">Saving investments</div>
        </div>
        <div class="kpi">
            <div class="kpi-lbl">Saving ROI</div>
            <div class="kpi-val">${{ number_format($data['saving_roi'] ?? 0, 2) }}</div>
            <div class="kpi-trend up"><i class="fas fa-arrow-up"></i> Daily appreciation</div>
        </div>
        <div class="kpi">
            <div class="kpi-lbl">Direct &amp; Indirect</div>
            <div class="kpi-val">${{ number_format(($data['saving_direct'] ?? 0) + ($data['saving_indirect'] ?? 0), 2) }}</div>
            <div class="kpi-trend neu">D: ${{ number_format($data['saving_direct'] ?? 0,2) }} · I: ${{ number_format($data['saving_indirect'] ?? 0,2) }}</div>
        </div>
    </div>

    @if(!empty($data['instalment_summary']['next_due']) || isset($data['saving_direct_team_count']) || isset($data['user_saving_team_count']))
    <div class="kpi-row kpi-3" style="margin-bottom:1.5rem">
        @if(!empty($data['instalment_summary']['next_due']))
        <div class="kpi" style="border-left:3px solid var(--red)">
            <div class="kpi-lbl">Next Due Date</div>
            <div class="kpi-val md" data-no-counter>{{ $data['instalment_summary']['next_due']->due_date->format('d M Y') }}</div>
            <div class="kpi-trend down"><i class="fas fa-calendar-exclamation"></i> #{{ $data['instalment_summary']['next_due']->instalment_number }} · ${{ number_format($data['instalment_summary']['next_due']->amount, 2) }}</div>
        </div>
        @endif
        @if(isset($data['saving_direct_team_count']))
        <div class="kpi">
            <div class="kpi-lbl">Direct Members</div>
            <div class="kpi-val md">{{ number_format($data['saving_direct_team_count']) }}</div>
            <div class="kpi-trend neu">Direct saving referrals</div>
        </div>
        @endif
        @if(isset($data['user_saving_team_count']))
        <div class="kpi">
            <div class="kpi-lbl">Saving Network</div>
            <div class="kpi-val md">{{ number_format($data['user_saving_team_count']) }}</div>
            <div class="kpi-trend neu">In your saving network</div>
        </div>
        @endif
    </div>
    @endif

    @if(!empty($data['saving_income']))
        @include('wallets.partials.saving-monthly-income', ['savingIncome' => $data['saving_income'], 'smiId' => 'smi-dash'])
    @endif
    @endif

    {{-- ═══════════════════════════════════════
         ADMIN SAVING OVERVIEW
    ═══════════════════════════════════════ --}}
    @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('super-admin'))
    @if(isset($data['admin_saving_total_invested']))
    <div class="sec-h">Saving Plan — System Overview</div>

    <div class="kpi-row kpi-4" style="margin-bottom:1.5rem">
        <div class="kpi">
            <div class="kpi-lbl">Total Members</div>
            <div class="kpi-val md">{{ number_format($data['admin_saving_total_users']) }}</div>
            <div class="kpi-trend neu">Active participants</div>
        </div>
        <div class="kpi">
            <div class="kpi-lbl">Total Invested</div>
            <div class="kpi-val">${{ number_format($data['admin_saving_total_invested'], 2) }}</div>
            <div class="kpi-trend up"><i class="fas fa-arrow-up"></i> All members combined</div>
        </div>
        <div class="kpi">
            <div class="kpi-lbl">Total ROI Paid</div>
            <div class="kpi-val">${{ number_format($data['admin_saving_total_roi'], 2) }}</div>
            <div class="kpi-trend neu">Distributed to date</div>
        </div>
        <div class="kpi">
            <div class="kpi-lbl">Direct &amp; Indirect</div>
            <div class="kpi-val">${{ number_format($data['admin_saving_total_direct'] + $data['admin_saving_total_indirect'], 2) }}</div>
            <div class="kpi-trend neu">D: ${{ number_format($data['admin_saving_total_direct'],2) }}</div>
        </div>
    </div>
    @endif
    @endif

</div>{{-- /db-body --}}
</div>{{-- /db --}}
@endsection

@section('page_js')
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* Panel toggles */
    function toggle(show, hide) {
        document.getElementById(hide).classList.remove('open');
        const p = document.getElementById(show);
        const was = p.classList.contains('open');
        p.classList.toggle('open', !was);
        if (!was) setTimeout(() => p.scrollIntoView({ behavior:'smooth', block:'nearest' }), 50);
    }
    document.getElementById('btnReward')?.addEventListener('click', () => toggle('panelReward','panelRank'));
    document.getElementById('btnRank')?.addEventListener('click',   () => toggle('panelRank',  'panelReward'));

    /* Counter animation */
    function countUp(el, target, prefix, dec) {
        const dur = 1400, t0 = performance.now();
        (function step(now) {
            const p = Math.min((now - t0) / dur, 1);
            const e = 1 - Math.pow(1 - p, 3);
            el.textContent = prefix + (target * e).toFixed(dec);
            if (p < 1) requestAnimationFrame(step);
        })(t0);
    }
    setTimeout(() => {
        document.querySelectorAll('.kpi-val, .bsc-val').forEach(el => {
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
    }, 80);

    /* Subtle fade-in */
    const io = new IntersectionObserver(entries => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                e.target.style.opacity = '1';
                e.target.style.transform = 'translateY(0)';
                io.unobserve(e.target);
            }
        });
    }, { threshold: 0.04 });

    document.querySelectorAll('.kpi, .roi-c, .ring-card, .big-stat-card, .card').forEach((el, i) => {
        el.style.cssText += `opacity:0;transform:translateY(10px);transition:opacity .3s ease ${i*25}ms,transform .3s ease ${i*25}ms`;
        io.observe(el);
    });
});
</script>
@endsection
