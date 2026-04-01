@extends('demo.layout.app')
@section('content')

{{-- ═══════════════════════════════════════════════════════════
     GVI AURORA DASHBOARD  · Full Dark Redesign v4
     A completely new aesthetic: dark-glass, neon accents, vivid
     ═══════════════════════════════════════════════════════════ --}}

<style>
/* ── DESIGN TOKENS ───────────────────────────────────────── */
:root {
    --f: 'Poppins', system-ui, sans-serif;

    /* Backgrounds */
    --bg:        #070d1f;
    --surface:   #0e1628;
    --card:      #111c32;
    --card-2:    #162040;
    --card-h:    #192448;

    /* Text */
    --t1: #f0f4ff;
    --t2: #8896b3;
    --t3: #4f617a;

    /* Borders */
    --br:  rgba(255,255,255,.07);
    --br2: rgba(255,255,255,.13);

    /* Neon Accents */
    --purple: #9b59ff;
    --blue:   #4f8fff;
    --cyan:   #00d2ff;
    --green:  #00e698;
    --amber:  #ffb600;
    --pink:   #ff4fa3;
    --red:    #ff4466;
    --gold:   #ffd560;

    /* Glows */
    --glow-purple: 0 0 32px rgba(155,89,255,.30);
    --glow-blue:   0 0 32px rgba(79,143,255,.30);
    --glow-green:  0 0 32px rgba(0,230,152,.30);
    --glow-amber:  0 0 32px rgba(255,182,0,.30);
    --glow-pink:   0 0 32px rgba(255,79,163,.30);
    --glow-cyan:   0 0 32px rgba(0,210,255,.30);

    /* Shadows */
    --s1: 0 2px 8px  rgba(0,0,10,.5);
    --s2: 0 8px 32px rgba(0,0,10,.55);
    --s3: 0 20px 60px rgba(0,0,10,.65);

    /* Radii */
    --r1: 10px; --r2: 16px; --r3: 22px; --r4: 32px;

    /* Easing */
    --ease: cubic-bezier(.22,.68,0,1.2);
    --ease2: cubic-bezier(.25,.46,.45,.94);
}

/* ── RESET ───────────────────────────────────────────────── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

/* ── PAGE ────────────────────────────────────────────────── */
.adb {
    font-family: var(--f);
    background: var(--bg);
    min-height: 100vh;
    color: var(--t1);
    -webkit-font-smoothing: antialiased;
    position: relative;
}

/* Ambient background blobs */
.adb-blobs {
    position: fixed; inset: 0;
    pointer-events: none; z-index: 0; overflow: hidden;
}
.adb-blob {
    position: absolute; border-radius: 50%;
    filter: blur(80px); opacity: .18;
}
.adb-blob-1 { width:600px; height:600px; background:#9b59ff; top:-160px; right:-100px; }
.adb-blob-2 { width:500px; height:500px; background:#00d2ff; bottom: 0; left:-150px; }
.adb-blob-3 { width:400px; height:400px; background:#ff4fa3; top:50%; left:40%; transform:translate(-50%,-50%); }

/* ═══════════════════════════════════════════════════════════
   HERO STRIP
═══════════════════════════════════════════════════════════ */
.hero-strip {
    position: relative; z-index: 2;
    background: linear-gradient(135deg, #0d1535 0%, #12173f 40%, #0d1535 100%);
    border-bottom: 1px solid var(--br);
    padding: 2.5rem 2rem 2rem;
    overflow: hidden;
}
/* grid lines */
.hero-strip::before {
    content: '';
    position: absolute; inset: 0;
    background-image:
        linear-gradient(rgba(255,255,255,.025) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,.025) 1px, transparent 1px);
    background-size: 40px 40px;
    pointer-events: none;
}
/* glow dots */
.hero-strip::after {
    content: '';
    position: absolute; inset: 0;
    background:
        radial-gradient(ellipse 800px 400px at 80% 50%, rgba(155,89,255,.12) 0%, transparent 65%),
        radial-gradient(ellipse 400px 300px at 5%  80%, rgba(0,210,255,.10) 0%, transparent 55%);
    pointer-events: none;
}

.hero-inner {
    max-width: 1360px; margin: 0 auto;
    position: relative; z-index: 1;
    display: flex; align-items: center;
    justify-content: space-between; gap: 2rem;
    flex-wrap: wrap;
}

.hero-left .eyebrow {
    font-size: .65rem; font-weight: 700;
    letter-spacing: .14em; text-transform: uppercase;
    color: var(--purple); margin-bottom: .55rem;
    display: flex; align-items: center; gap: .4rem;
}
.hero-left .eyebrow::before {
    content: ''; display: inline-block;
    width: 20px; height: 2px;
    background: var(--purple); border-radius: 2px;
}

.hero-welcome {
    font-size: 2.4rem; font-weight: 900;
    letter-spacing: -.04em; line-height: 1.1;
    color: var(--t1);
    margin-bottom: .9rem;
}
.hero-welcome span {
    background: linear-gradient(135deg, var(--purple), var(--cyan));
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    background-clip: text;
}

.pkg-pill {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .3rem 1rem; border-radius: 50px;
    font-size: .7rem; font-weight: 700;
    letter-spacing: .06em; text-transform: uppercase;
}
.pkg-pill.vip {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: #fff; box-shadow: 0 4px 18px rgba(245,158,11,.38);
}
.pkg-pill.std {
    background: linear-gradient(135deg, var(--purple), #7c3aed);
    color: #fff; box-shadow: var(--glow-purple);
}

/* Balance spotlight */
.balance-spotlight {
    background: linear-gradient(135deg, rgba(155,89,255,.15), rgba(0,210,255,.10));
    border: 1px solid rgba(155,89,255,.25);
    border-radius: var(--r3);
    padding: 1.6rem 2.2rem;
    text-align: right;
    position: relative; overflow: hidden;
    flex-shrink: 0;
    backdrop-filter: blur(20px);
    box-shadow: var(--glow-purple), var(--s2);
    min-width: 260px;
}
.balance-spotlight::before {
    content: '';
    position: absolute; top: -40px; right: -40px;
    width: 160px; height: 160px; border-radius: 50%;
    background: radial-gradient(circle, rgba(155,89,255,.25), transparent 70%);
    pointer-events: none;
}
.bal-label {
    font-size: .65rem; font-weight: 700;
    letter-spacing: .12em; text-transform: uppercase;
    color: var(--purple); margin-bottom: .4rem;
}
.bal-value {
    font-size: 2.6rem; font-weight: 900;
    letter-spacing: -.04em; color: var(--t1); line-height: 1;
    margin-bottom: .35rem;
}
.bal-note {
    font-size: .72rem; color: var(--t2); display: flex;
    align-items: center; gap: .3rem; justify-content: flex-end;
}
.bal-note .dot {
    width: 7px; height: 7px; border-radius: 50%;
    background: var(--green);
    box-shadow: 0 0 8px var(--green);
}

/* ── Date chip ── */
.hero-date {
    text-align: right; flex-shrink: 0;
}
.hero-date-top {
    font-size: .65rem; font-weight: 700;
    letter-spacing: .1em; text-transform: uppercase;
    color: var(--cyan); margin-bottom: .2rem;
}
.hero-date-val {
    font-size: .92rem; font-weight: 700; color: var(--t1);
}
.hero-date-tz { font-size: .72rem; color: var(--t3); margin-top: .1rem; }

/* ═══════════════════════════════════════════════════════════
   CONTENT WRAPPER
═══════════════════════════════════════════════════════════ */
.adb-body {
    max-width: 1360px; margin: 0 auto;
    padding: 2rem 2rem 4rem;
    position: relative; z-index: 2;
}

/* ── Section heading ── */
.sec-head {
    display: flex; align-items: center; gap: .75rem;
    margin-bottom: 1.1rem; margin-top: .5rem;
}
.sec-head-dot {
    width: 8px; height: 8px; border-radius: 50%;
    flex-shrink: 0;
}
.sec-head-label {
    font-size: .68rem; font-weight: 700;
    letter-spacing: .12em; text-transform: uppercase; color: var(--t2);
}
.sec-head::after {
    content: ''; flex: 1; height: 1px;
    background: var(--br);
}

/* ═══════════════════════════════════════════════════════════
   ADMIN ALERT
═══════════════════════════════════════════════════════════ */
.admin-alert {
    display: flex; align-items: center; gap: 1.1rem;
    padding: 1.1rem 1.6rem; border-radius: var(--r2);
    margin-bottom: 1.6rem; text-decoration: none;
    background: linear-gradient(135deg, #7f0000, #dc2626);
    border: 1px solid rgba(255,68,68,.3);
    box-shadow: 0 8px 32px rgba(239,68,68,.28);
    animation: alert-pulse 2.4s ease infinite;
}
@keyframes alert-pulse {
    0%,100% { box-shadow: 0 8px 32px rgba(239,68,68,.28); }
    50%      { box-shadow: 0 8px 48px rgba(239,68,68,.5); }
}
.aa-icon { font-size: 1.6rem; flex-shrink: 0; }
.aa-msg strong { display: block; color: #fff; font-size: .95rem; font-weight: 700; }
.aa-msg span   { color: rgba(255,255,255,.7); font-size: .8rem; }
.aa-count {
    margin-left: auto; background: rgba(255,255,255,.18);
    color: #fff; border-radius: 50px; padding: .3rem 1rem;
    font-size: .88rem; font-weight: 800; flex-shrink: 0;
}

/* ═══════════════════════════════════════════════════════════
   KPI BAR  (5 stat tiles in a row)
═══════════════════════════════════════════════════════════ */
.kpi-bar {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 1px;
    background: var(--br);
    border-radius: var(--r2);
    overflow: hidden;
    border: 1px solid var(--br);
    margin-bottom: 1.8rem;
}
.kpi-tile {
    background: var(--card);
    padding: 1.4rem 1.5rem;
    cursor: default;
    transition: background .2s;
    position: relative; overflow: hidden;
}
.kpi-tile::after {
    content: '';
    position: absolute; bottom: 0; left: 0; right: 0;
    height: 2px; background: var(--accent, var(--purple));
    opacity: 0; transition: opacity .2s;
}
.kpi-tile:hover { background: var(--card-h); }
.kpi-tile:hover::after { opacity: 1; }
.kpi-t-label {
    font-size: .62rem; font-weight: 700; letter-spacing: .1em;
    text-transform: uppercase; color: var(--t3); margin-bottom: .55rem;
}
.kpi-t-val {
    font-size: 1.55rem; font-weight: 900; letter-spacing: -.03em;
    color: var(--t1); line-height: 1; margin-bottom: .35rem;
}
.kpi-t-sub {
    font-size: .65rem; color: var(--t3); font-weight: 500;
    display: flex; align-items: center; gap: .3rem;
}
.kpi-t-sub .dot {
    width: 6px; height: 6px; border-radius: 50%;
    background: var(--accent, var(--purple));
    box-shadow: 0 0 6px var(--accent, var(--purple));
}

/* ═══════════════════════════════════════════════════════════
   EID BANNER  (dark gold glass version)
═══════════════════════════════════════════════════════════ */
.eid-banner {
    position: relative; overflow: hidden;
    border-radius: var(--r3);
    margin-bottom: 1.8rem;
    background: #040d08;
    border: 1px solid rgba(212,175,55,.15);
    box-shadow: 0 0 0 1px rgba(212,175,55,.05),
                0 24px 64px rgba(0,0,0,.65);
}
.eid-banner::before {
    content: '';
    position: absolute; inset: 0;
    background:
        radial-gradient(ellipse 700px 400px at 15% 55%, rgba(4,70,28,.95) 0%, transparent 65%),
        radial-gradient(ellipse 450px 300px at 90% 0%,  rgba(2,50,20,.85) 0%, transparent 60%);
    pointer-events: none;
}
.eid-banner::after {
    content: '';
    position: absolute; inset: 0;
    background-image:
        repeating-linear-gradient( 55deg, rgba(212,175,55,.028) 0, rgba(212,175,55,.028) 1px, transparent 1px, transparent 28px),
        repeating-linear-gradient(-55deg, rgba(212,175,55,.028) 0, rgba(212,175,55,.028) 1px, transparent 1px, transparent 28px);
    pointer-events: none;
}
.eid-inner-wrap {
    position: relative; z-index: 2;
    display: grid; grid-template-columns: 1fr auto;
    gap: 2rem; align-items: center;
    padding: 2rem 2.5rem;
}
.eid-toprow {
    display: flex; align-items: center; gap: .6rem; margin-bottom: .9rem;
}
.eid-line { height: 1px; width: 44px; background: linear-gradient(90deg, rgba(212,175,55,.6), transparent); }
.eid-line.rev { background: linear-gradient(270deg, rgba(212,175,55,.6), transparent); }
.eid-dot { width: 5px; height: 5px; border-radius: 50%; background: #d4af37; box-shadow: 0 0 7px rgba(212,175,55,.8); }
.eid-org { font-size: .6rem; font-weight: 700; letter-spacing: .15em; text-transform: uppercase; color: rgba(212,175,55,.8); }
.eid-arabic {
    font-size: 2.8rem; font-weight: 900; color: #d4af37;
    letter-spacing: .05em; line-height: 1; margin-bottom: .3rem;
    text-shadow: 0 0 40px rgba(212,175,55,.5);
}
.eid-title {
    font-size: .9rem; font-weight: 700; color: rgba(255,255,255,.85);
    letter-spacing: .06em; text-transform: uppercase; margin-bottom: 1rem;
}
.eid-msg {
    font-size: .8rem; color: rgba(255,255,255,.5);
    line-height: 1.75; max-width: 450px; margin-bottom: 1.2rem;
    border-left: 2px solid rgba(212,175,55,.28); padding-left: .9rem;
}
.eid-dua {
    display: inline-flex; align-items: flex-start; gap: .4rem;
    font-size: .75rem; font-weight: 600; font-style: italic;
    color: rgba(212,175,55,.8);
    background: rgba(212,175,55,.06);
    border: 1px solid rgba(212,175,55,.18);
    border-radius: var(--r1); padding: .45rem .95rem; line-height: 1.5;
}
/* Moon art */
.eid-art { display: flex; flex-direction: column; align-items: center; gap: .7rem; flex-shrink: 0; }
.eid-moon-svg {
    width: 120px; height: 120px; overflow: visible;
    animation: moon-glow 4s ease-in-out infinite;
}
@keyframes moon-glow {
    0%,100% { filter: drop-shadow(0 0 14px rgba(212,175,55,.4)) drop-shadow(0 0 40px rgba(212,175,55,.15)); }
    50%      { filter: drop-shadow(0 0 24px rgba(212,175,55,.65)) drop-shadow(0 0 65px rgba(212,175,55,.28)); }
}
.eid-stars { display: flex; gap: .45rem; align-items: center; justify-content: center; }
.eid-star {
    background: #d4af37;
    clip-path: polygon(50% 0%,61.8% 38.2%,100% 38.2%,69.1% 61.8%,80.9% 100%,50% 76.4%,19.1% 100%,30.9% 61.8%,0% 38.2%,38.2% 38.2%);
    animation: star-twinkle 2.4s ease-in-out infinite;
}
.eid-star:nth-child(2) { animation-delay: -.9s; }
.eid-star:nth-child(3) { animation-delay: -1.7s; }
@keyframes star-twinkle {
    0%,100% { opacity: .85; transform: scale(1); }
    50%      { opacity: .3;  transform: scale(.6); }
}
.eid-star-lg { width:22px; height:22px; }
.eid-star-md { width:14px; height:14px; opacity:.78; }
.eid-star-sm { width: 9px; height: 9px;  opacity:.55; }
.eid-gold-line {
    width:100%; height: 2px; position: relative; z-index: 2;
    background: linear-gradient(90deg, transparent, rgba(212,175,55,.42) 28%, rgba(212,175,55,.88) 50%, rgba(212,175,55,.42) 72%, transparent);
}
/* Sparkles */
.eid-sparkles { position: absolute; inset: 0; pointer-events: none; z-index: 1; overflow: hidden; }
.eid-sparkle {
    position: absolute; border-radius: 50%;
    background: #d4af37; opacity: 0;
    animation: sparkle-rise var(--dur,3s) ease-in-out var(--del,0s) infinite;
}
@keyframes sparkle-rise {
    0%   { opacity: 0; transform: translateY(0) scale(1); }
    35%  { opacity: .65; }
    100% { opacity: 0; transform: translateY(-50px) scale(.3); }
}
@media (max-width: 700px) {
    .eid-inner-wrap { grid-template-columns: 1fr; padding: 1.5rem; }
    .eid-art { display: none; }
    .eid-arabic { font-size: 2rem; }
}

/* ═══════════════════════════════════════════════════════════
   WALLET CARDS  (colored gradient cards)
═══════════════════════════════════════════════════════════ */
.wallet-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 1.1rem;
    margin-bottom: 1.8rem;
}

.w-card {
    border-radius: var(--r2);
    padding: 1.5rem;
    position: relative; overflow: hidden;
    text-decoration: none; color: inherit;
    border: 1px solid transparent;
    transition: transform .22s var(--ease2), box-shadow .22s;
    cursor: default;
}
.w-card:hover { transform: translateY(-4px); }

/* per-card themes */
.wc-purple {
    background: linear-gradient(135deg, #1a0d3b, #2a1260);
    border-color: rgba(155,89,255,.25);
    box-shadow: var(--glow-purple), var(--s2);
}
.wc-cyan {
    background: linear-gradient(135deg, #041f2e, #082f45);
    border-color: rgba(0,210,255,.2);
    box-shadow: var(--glow-cyan), var(--s2);
}
.wc-green {
    background: linear-gradient(135deg, #041e12, #082e1e);
    border-color: rgba(0,230,152,.2);
    box-shadow: var(--glow-green), var(--s2);
}
.wc-amber {
    background: linear-gradient(135deg, #1f1200, #2e1a00);
    border-color: rgba(255,182,0,.2);
    box-shadow: var(--glow-amber), var(--s2);
}
.wc-pink {
    background: linear-gradient(135deg, #1f0520, #2e0a2e);
    border-color: rgba(255,79,163,.2);
    box-shadow: var(--glow-pink), var(--s2);
}
.wc-blue {
    background: linear-gradient(135deg, #050c2b, #0b1440);
    border-color: rgba(79,143,255,.2);
    box-shadow: var(--glow-blue), var(--s2);
}
.wc-gold {
    background: linear-gradient(135deg, #1a1000, #2b1c00);
    border-color: rgba(255,213,96,.2);
    box-shadow: 0 0 32px rgba(255,213,96,.15), var(--s2);
}

.wc-purple:hover { box-shadow: 0 0 48px rgba(155,89,255,.45), var(--s3); }
.wc-cyan:hover   { box-shadow: 0 0 48px rgba(0,210,255,.35), var(--s3); }
.wc-green:hover  { box-shadow: 0 0 48px rgba(0,230,152,.35), var(--s3); }
.wc-amber:hover  { box-shadow: 0 0 48px rgba(255,182,0,.35), var(--s3); }
.wc-pink:hover   { box-shadow: 0 0 48px rgba(255,79,163,.40), var(--s3); }
.wc-blue:hover   { box-shadow: 0 0 48px rgba(79,143,255,.35), var(--s3); }
.wc-gold:hover   { box-shadow: 0 0 48px rgba(255,213,96,.30), var(--s3); }

/* Corner glow orb */
.w-card::before {
    content: '';
    position: absolute; top: -30px; right: -30px;
    width: 120px; height: 120px; border-radius: 50%;
    background: radial-gradient(circle, var(--wc-accent, rgba(155,89,255,.3)), transparent 70%);
    pointer-events: none;
}
.wc-purple { --wc-accent: rgba(155,89,255,.3); }
.wc-cyan   { --wc-accent: rgba(0,210,255,.25); }
.wc-green  { --wc-accent: rgba(0,230,152,.25); }
.wc-amber  { --wc-accent: rgba(255,182,0,.25); }
.wc-pink   { --wc-accent: rgba(255,79,163,.28); }
.wc-blue   { --wc-accent: rgba(79,143,255,.25); }
.wc-gold   { --wc-accent: rgba(255,213,96,.2); }

.w-card-top {
    display: flex; align-items: center;
    justify-content: space-between; margin-bottom: 1.1rem;
}
.w-icon {
    width: 38px; height: 38px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: .9rem; flex-shrink: 0;
    background: rgba(255,255,255,.08);
    color: var(--wc-color, #fff);
}
.wc-purple .w-icon { color: var(--purple); background: rgba(155,89,255,.15); }
.wc-cyan   .w-icon { color: var(--cyan);   background: rgba(0,210,255,.12); }
.wc-green  .w-icon { color: var(--green);  background: rgba(0,230,152,.12); }
.wc-amber  .w-icon { color: var(--amber);  background: rgba(255,182,0,.12); }
.wc-pink   .w-icon { color: var(--pink);   background: rgba(255,79,163,.12); }
.wc-blue   .w-icon { color: var(--blue);   background: rgba(79,143,255,.12); }
.wc-gold   .w-icon { color: var(--gold);   background: rgba(255,213,96,.12); }

.w-arrow { font-size: .8rem; color: rgba(255,255,255,.2); transition: color .2s, transform .2s; }
.w-card:hover .w-arrow { color: rgba(255,255,255,.55); transform: translateX(3px); }

.w-label { font-size: .63rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: rgba(255,255,255,.42); margin-bottom: .45rem; }
.w-value { font-size: 1.7rem; font-weight: 900; letter-spacing: -.03em; color: #fff; line-height: 1; margin-bottom: .4rem; }
.w-note  { font-size: .68rem; color: rgba(255,255,255,.4); display: flex; align-items: center; gap: .3rem; }

/* ═══════════════════════════════════════════════════════════
   2-COLUMN MAIN GRID
═══════════════════════════════════════════════════════════ */
.main-grid {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 1.4rem;
    margin-bottom: 1.8rem;
}
@media (max-width: 1100px) { .main-grid { grid-template-columns: 1fr; } }

/* ── Glass Card (reusable wrapper) ── */
.glass-card {
    background: var(--card);
    border: 1px solid var(--br);
    border-radius: var(--r3);
    box-shadow: var(--s2);
}
.glass-card-head {
    padding: 1.5rem 1.8rem 0;
    display: flex; align-items: center;
    justify-content: space-between; gap: .75rem;
    flex-wrap: wrap;
}
.gc-title {
    font-size: .95rem; font-weight: 800;
    color: var(--t1); letter-spacing: -.015em;
}
.gc-subtitle { font-size: .72rem; color: var(--t2); margin-top: .15rem; }
.glass-card-body { padding: 1.4rem 1.8rem 1.8rem; }

/* Filter tabs */
.ftabs {
    display: flex; gap: .25rem;
    background: rgba(255,255,255,.05);
    padding: .22rem; border-radius: 8px;
    border: 1px solid var(--br);
}
.ftab {
    padding: .28rem .75rem; border-radius: 6px;
    border: none; background: transparent;
    font-size: .72rem; font-weight: 600;
    color: var(--t2); cursor: pointer;
    font-family: var(--f); transition: all .18s;
}
.ftab.active, .ftab:hover {
    background: var(--purple); color: #fff;
    box-shadow: 0 2px 8px rgba(155,89,255,.35);
}

/* ═══════════════════════════════════════════════════════════
   ROI METERS  (right panel)
═══════════════════════════════════════════════════════════ */
.roi-side { display: flex; flex-direction: column; gap: 1.1rem; }

.roi-meter-card {
    background: var(--card);
    border: 1px solid var(--br);
    border-radius: var(--r2);
    box-shadow: var(--s1);
    padding: 1.4rem 1.6rem;
    position: relative; overflow: hidden;
    flex: 1;
}
.roi-meter-card::before {
    content: '';
    position: absolute; top: 0; left: 0; right: 0;
    height: 2px; background: var(--roi-accent, var(--purple));
}

.rm-head {
    display: flex; align-items: center;
    justify-content: space-between; margin-bottom: 1.2rem;
}
.rm-icon-wrap {
    display: flex; align-items: center; gap: .6rem;
}
.rm-icon {
    width: 32px; height: 32px; border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    font-size: .8rem;
}
.rm-title { font-size: .82rem; font-weight: 700; color: var(--t1); }
.rm-title small { display: block; font-size: .65rem; font-weight: 500; color: var(--t2); margin-top: .1rem; }

.rm-badge {
    font-size: .62rem; font-weight: 700; letter-spacing: .06em;
    text-transform: uppercase; padding: .22rem .7rem; border-radius: 50px;
}
.rm-active { background: rgba(0,230,152,.1); color: var(--green); }
.rm-done   { background: rgba(255,68,102,.1); color: var(--red); }
.rm-warn   { background: rgba(255,182,0,.1);  color: var(--amber); }

/* Track */
.rm-track {
    height: 8px; background: rgba(255,255,255,.06);
    border-radius: 99px; overflow: hidden; margin-bottom: .6rem;
}
.rm-fill {
    height: 100%; border-radius: 99px;
    transition: width 1.2s ease;
}

.rm-pct {
    text-align: right; font-size: .7rem;
    font-weight: 700; color: var(--t2);
    margin-top: -.4rem; margin-bottom: .9rem;
}

.rm-stats {
    display: grid; grid-template-columns: repeat(3,1fr);
    gap: .5rem; padding-top: .9rem;
    border-top: 1px solid var(--br);
}
.rm-stat-val { font-size: 1rem; font-weight: 800; color: var(--t1); letter-spacing: -.02em; }
.rm-stat-lbl { font-size: .6rem; color: var(--t3); font-weight: 600; text-transform: uppercase; letter-spacing: .06em; margin-top: .15rem; }

.rm-notice {
    display: flex; align-items: center; gap: .5rem;
    margin-top: 1rem; padding: .6rem .9rem;
    border-radius: var(--r1); font-size: .75rem; font-weight: 600;
}
.rn-green { background: rgba(0,230,152,.07); color: var(--green); }
.rn-amber { background: rgba(255,182,0,.07);  color: var(--amber); }

/* ═══════════════════════════════════════════════════════════
   ANNOUNCEMENT CARD
═══════════════════════════════════════════════════════════ */
.ann-card {
    position: relative; overflow: hidden;
    border-radius: var(--r3); padding: 2.2rem 2.5rem;
    margin-bottom: 1.8rem;
    background: linear-gradient(135deg, #0e0028, #180040, #0a002e);
    border: 1px solid rgba(155,89,255,.25);
    box-shadow: var(--glow-purple), var(--s3);
}
.ann-card::before {
    content: '';
    position: absolute; inset: 0;
    background:
        radial-gradient(ellipse 600px 300px at 100% 50%, rgba(155,89,255,.2) 0%, transparent 65%),
        radial-gradient(ellipse 300px 250px at 0% 0%,    rgba(0,210,255,.12) 0%, transparent 55%);
    pointer-events: none;
}
.ann-card::after {
    content: '🎯';
    position: absolute; right: 2.5rem; top: 50%;
    transform: translateY(-50%);
    font-size: 7rem; opacity: .07; pointer-events: none;
}
.ann-inner { position: relative; z-index: 1; }
.ann-eyebrow {
    font-size: .62rem; font-weight: 700; letter-spacing: .14em;
    text-transform: uppercase; color: var(--purple);
    margin-bottom: .6rem;
    display: flex; align-items: center; gap: .4rem;
}
.ann-eyebrow::before {
    content: ''; width: 16px; height: 2px;
    background: var(--purple); border-radius: 2px;
}
.ann-title {
    font-size: 1.55rem; font-weight: 900; color: var(--t1);
    letter-spacing: -.025em; margin-bottom: .5rem;
}
.ann-desc {
    font-size: .83rem; color: var(--t2); margin-bottom: 1.75rem;
    max-width: 520px; line-height: 1.65;
}
.ann-chips { display: flex; gap: .9rem; flex-wrap: wrap; }
.ann-chip {
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.1);
    border-radius: var(--r1); padding: .9rem 1.4rem;
    min-width: 110px; text-align: center;
    backdrop-filter: blur(12px);
    transition: background .2s;
}
.ann-chip:hover { background: rgba(255,255,255,.1); }
.ann-chip-val { font-size: 1.35rem; font-weight: 900; color: var(--t1); letter-spacing: -.02em; }
.ann-chip-lbl { font-size: .62rem; color: var(--t2); font-weight: 600; text-transform: uppercase; letter-spacing: .08em; margin-top: .25rem; }
.ann-chip.hi .ann-chip-val { color: var(--gold); text-shadow: 0 0 16px rgba(255,213,96,.4); }

/* ═══════════════════════════════════════════════════════════
   TARGETS GRID  (circular progress)
═══════════════════════════════════════════════════════════ */
.target-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px,1fr));
    gap: 1.1rem; margin-bottom: 1.8rem;
}
.t-card {
    background: var(--card);
    border: 1px solid var(--br); border-radius: var(--r2);
    box-shadow: var(--s1); padding: 2rem;
    text-align: center; transition: transform .22s var(--ease2), box-shadow .22s;
}
.t-card:hover { transform: translateY(-4px); box-shadow: var(--s2); }
.t-card h3 { font-size: .92rem; font-weight: 800; color: var(--t1); margin-bottom: .25rem; }
.t-card p  { font-size: .75rem; color: var(--t2); margin-bottom: 1.6rem; line-height: 1.5; }

.ring-wrap {
    position: relative; width: 160px; height: 160px;
    margin: 0 auto 1.6rem;
}
.ring-wrap svg { width: 100%; height: 100%; transform: rotate(-90deg); }
.ring-bg   { fill: none; stroke: rgba(255,255,255,.07); stroke-width: 9; }
.ring-fill { fill: none; stroke-width: 9; stroke-linecap: round; transition: stroke-dasharray 1.3s ease; }
.ring-center {
    position: absolute; inset: 0;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
}
.ring-pct {
    font-size: 1.9rem; font-weight: 900; color: var(--t1);
    letter-spacing: -.04em; line-height: 1;
}
.ring-sub {
    font-size: .62rem; font-weight: 600; color: var(--t2);
    text-transform: uppercase; letter-spacing: .08em; margin-top: .3rem;
}

.t-btn {
    width: 100%; padding: .78rem; border: none; border-radius: var(--r1);
    font-family: var(--f); font-size: .8rem; font-weight: 700;
    cursor: pointer; display: inline-flex; align-items: center;
    justify-content: center; gap: .4rem;
    transition: opacity .2s, transform .2s;
}
.t-btn:hover { opacity: .85; transform: translateY(-1px); }
.t-btn-reward { background: linear-gradient(135deg, var(--purple), #7c3aed); color: #fff; box-shadow: 0 4px 18px rgba(155,89,255,.35); }
.t-btn-rank   { background: linear-gradient(135deg, #0ea5e9, #0284c7); color: #fff; box-shadow: 0 4px 18px rgba(14,165,233,.35); }

/* ═══════════════════════════════════════════════════════════
   EXPANDABLE PANELS
═══════════════════════════════════════════════════════════ */
.d-panel {
    background: var(--card); border: 1px solid var(--br);
    border-radius: var(--r3); box-shadow: var(--s1);
    margin-bottom: 1.6rem; display: none;
}
.d-panel.open { display: block; animation: fade-up .3s var(--ease2); }
@keyframes fade-up {
    from { opacity: 0; transform: translateY(14px); }
    to   { opacity: 1; transform: translateY(0); }
}
.d-panel-head {
    padding: 1.4rem 1.8rem; border-bottom: 1px solid var(--br);
    display: flex; align-items: center; gap: .7rem;
    font-size: .92rem; font-weight: 800; color: var(--t1);
}
.d-ph-icon {
    width: 34px; height: 34px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: .85rem;
}
.d-panel-body { padding: 1.8rem; }

/* Level rows */
.lv-row {
    display: grid;
    grid-template-columns: 44px 1fr 160px 80px;
    gap: 1rem; align-items: center;
    padding: .9rem 1.1rem; border-radius: var(--r1);
    border: 1px solid transparent; margin-bottom: .5rem;
    transition: background .18s, border-color .18s;
}
.lv-row:hover { background: rgba(255,255,255,.04); border-color: var(--br2); }
.lv-num {
    width: 44px; height: 44px; border-radius: 11px;
    background: linear-gradient(135deg, var(--purple), #7c3aed);
    color: #fff; display: flex; align-items: center; justify-content: center;
    font-weight: 800; font-size: 1rem; flex-shrink: 0;
    box-shadow: 0 4px 14px rgba(155,89,255,.35);
}
.lv-info h6 { font-size: .82rem; font-weight: 700; color: var(--t1); margin-bottom: .1rem; }
.lv-info p  { font-size: .7rem; color: var(--t2); }
.lv-track { height: 5px; background: rgba(255,255,255,.07); border-radius: 99px; overflow: hidden; margin-bottom: .25rem; }
.lv-fill  { height: 100%; border-radius: 99px; background: linear-gradient(90deg, var(--purple), var(--cyan)); }
.lv-pct   { font-size: .62rem; color: var(--t3); font-weight: 600; }
.lv-count { font-size: .88rem; font-weight: 800; color: var(--t1); text-align: right; }
.lv-count span { font-size: .68rem; color: var(--t3); font-weight: 500; }

/* Rank grid */
.rank-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px,1fr));
    gap: .9rem;
}
.rank-item {
    text-align: center; padding: 1.4rem 1rem;
    border-radius: var(--r2);
    border: 1.5px solid var(--br);
    background: var(--surface);
    transition: all .22s var(--ease2);
}
.rank-item:hover { transform: translateY(-3px); box-shadow: var(--s2); background: var(--card-h); }
.rank-item.won { border-color: rgba(0,230,152,.3); background: rgba(0,230,152,.05); }
.rank-img { width: 52px; height: 52px; object-fit: contain; display: block; margin: 0 auto .7rem; filter: drop-shadow(0 4px 10px rgba(0,0,0,.4)); }
.rank-name  { font-size: .8rem; font-weight: 700; color: var(--t1); margin-bottom: .18rem; }
.rank-req   { font-size: .65rem; color: var(--t2); margin-bottom: .7rem; line-height: 1.4; }
.rank-track { height: 4px; background: rgba(255,255,255,.07); border-radius: 99px; overflow: hidden; margin-bottom: .4rem; }
.rank-bar   { height: 100%; border-radius: 99px; background: linear-gradient(90deg, var(--purple), var(--cyan)); }
.rank-count { font-size: .65rem; color: var(--t3); font-weight: 600; }
.rank-done  {
    display: inline-flex; align-items: center; gap: .22rem;
    background: rgba(0,230,152,.1); color: var(--green);
    border-radius: 50px; padding: .16rem .55rem;
    font-size: .63rem; font-weight: 700; margin-top: .35rem;
}

/* ═══════════════════════════════════════════════════════════
   RESPONSIVE
═══════════════════════════════════════════════════════════ */
@media (max-width: 900px) {
    .kpi-bar { grid-template-columns: repeat(3,1fr); }
    .hero-welcome { font-size: 1.8rem; }
}
@media (max-width: 640px) {
    .kpi-bar { grid-template-columns: repeat(2,1fr); }
    .wallet-grid { grid-template-columns: repeat(2,1fr); }
    .adb-body { padding: 1.4rem 1rem 3rem; }
    .hero-strip { padding: 1.75rem 1.2rem 1.5rem; }
    .balance-spotlight { display: none; }
}
</style>

<div class="adb">

    {{-- Ambient background blobs --}}
    <div class="adb-blobs">
        <div class="adb-blob adb-blob-1"></div>
        <div class="adb-blob adb-blob-2"></div>
        <div class="adb-blob adb-blob-3"></div>
    </div>

    {{-- ─── HERO STRIP ─────────────────────────────────── --}}
    <div class="hero-strip">
        <div class="hero-inner">
            {{-- Left --}}
            <div class="hero-left">
                <div class="eyebrow">Global Visioners International</div>
                <div class="hero-welcome">
                    Hello, <span>{{ Auth::user()->name }}</span> 👋
                </div>
                @if($data['user_plan'] === 'vip')
                    <span class="pkg-pill vip"><i class="fas fa-crown"></i>&nbsp;VIP Gold Package</span>
                @else
                    <span class="pkg-pill std"><i class="fas fa-gem"></i>&nbsp;Standard Package</span>
                @endif
            </div>

            {{-- Balance spotlight --}}
            <div class="balance-spotlight">
                <div class="bal-label">Total Earnings</div>
                <div class="bal-value" id="heroBalance">${{ number_format($data['total_earning'], 2) }}</div>
                <div class="bal-note">
                    <span class="dot"></span>
                    Online Wallet: ${{ number_format($data['online_wallet'], 2) }}
                </div>
            </div>

            {{-- Date --}}
            <div class="hero-date">
                <div class="hero-date-top">Today</div>
                <div class="hero-date-val">{{ now()->format('l, M d Y') }}</div>
                @role('admin')
                <div class="hero-date-tz">{{ now()->format('h:i A') }} · {{ config('app.timezone') }}</div>
                @endrole
            </div>
        </div>
    </div>

    {{-- ─── BODY ───────────────────────────────────────── --}}
    <div class="adb-body">

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

        {{-- KPI Bar --}}
        <div class="kpi-bar">
            <div class="kpi-tile" style="--accent:var(--purple)">
                <div class="kpi-t-label">Total Earnings</div>
                <div class="kpi-t-val">${{ number_format($data['total_earning'], 2) }}</div>
                <div class="kpi-t-sub"><span class="dot"></span> All-time</div>
            </div>
            <div class="kpi-tile" style="--accent:var(--green)">
                <div class="kpi-t-label">Online Wallet</div>
                <div class="kpi-t-val">${{ number_format($data['online_wallet'], 2) }}</div>
                <div class="kpi-t-sub"><span class="dot" style="background:var(--green);box-shadow:0 0 6px var(--green)"></span> Available</div>
            </div>
            <div class="kpi-tile" style="--accent:var(--cyan)">
                <div class="kpi-t-label">ROI Earnings</div>
                <div class="kpi-t-val">${{ number_format($data['roi'], 2) }}</div>
                <div class="kpi-t-sub"><span class="dot" style="background:var(--cyan);box-shadow:0 0 6px var(--cyan)"></span> Return</div>
            </div>
            <div class="kpi-tile" style="--accent:var(--amber)">
                <div class="kpi-t-label">Team Size</div>
                <div class="kpi-t-val">{{ number_format($data['totalTeam']) }}</div>
                <div class="kpi-t-sub"><span class="dot" style="background:var(--amber);box-shadow:0 0 6px var(--amber)"></span> Members</div>
            </div>
            <div class="kpi-tile" style="--accent:var(--pink)">
                <div class="kpi-t-label">Commissions</div>
                <div class="kpi-t-val">${{ number_format($data['direct_indirect'], 2) }}</div>
                <div class="kpi-t-sub"><span class="dot" style="background:var(--pink);box-shadow:0 0 6px var(--pink)"></span> Direct / Indirect</div>
            </div>
        </div>

        {{-- EID MUBARAK BANNER --}}
        <div class="eid-banner">
            <div class="eid-sparkles">
                <div class="eid-sparkle" style="left:6%;top:52%;width:3px;height:3px;--dur:3.2s;--del:.0s"></div>
                <div class="eid-sparkle" style="left:14%;top:30%;width:2px;height:2px;--dur:2.6s;--del:.7s"></div>
                <div class="eid-sparkle" style="left:23%;top:65%;width:4px;height:4px;--dur:3.8s;--del:1.2s"></div>
                <div class="eid-sparkle" style="left:36%;top:22%;width:2px;height:2px;--dur:2.9s;--del:.4s"></div>
                <div class="eid-sparkle" style="left:48%;top:72%;width:3px;height:3px;--dur:3.5s;--del:.9s"></div>
                <div class="eid-sparkle" style="left:60%;top:38%;width:2px;height:2px;--dur:4.1s;--del:.2s"></div>
                <div class="eid-sparkle" style="left:72%;top:55%;width:3px;height:3px;--dur:3.0s;--del:1.5s"></div>
                <div class="eid-sparkle" style="left:82%;top:28%;width:4px;height:4px;--dur:2.7s;--del:.6s"></div>
                <div class="eid-sparkle" style="left:91%;top:62%;width:2px;height:2px;--dur:3.6s;--del:1.0s"></div>
            </div>
            <div class="eid-inner-wrap">
                <div>
                    <div class="eid-toprow">
                        <div class="eid-line"></div>
                        <div class="eid-dot"></div>
                        <div class="eid-org">Global Visioners International</div>
                        <div class="eid-dot"></div>
                        <div class="eid-line rev"></div>
                    </div>
                    <div class="eid-arabic">عيد مبارك</div>
                    <div class="eid-title">Eid Mubarak &mdash; Happy Eid al-Fitr 1446 AH</div>
                    <div class="eid-msg">
                        May the blessings of Allah fill your life with happiness, your heart with love, and your soul with peace. Global Visioners International extends its warmest greetings to you and your entire family.
                    </div>
                    <div class="eid-dua">
                        <span>&#x275D;</span>
                        Taqabbal Allahu minna wa minkum &mdash; May Allah accept from us and from you
                        <span>&#x275E;</span>
                    </div>
                </div>
                <div class="eid-art">
                    <svg class="eid-moon-svg" viewBox="0 0 130 130" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <radialGradient id="moonGv4" cx="36%" cy="33%" r="66%">
                                <stop offset="0%"   stop-color="#f9f0a0"/>
                                <stop offset="28%"  stop-color="#e8c84a"/>
                                <stop offset="58%"  stop-color="#d4af37"/>
                                <stop offset="84%"  stop-color="#b8860b"/>
                                <stop offset="100%" stop-color="#7a5800"/>
                            </radialGradient>
                            <mask id="moonMv4">
                                <circle cx="65" cy="65" r="52" fill="white"/>
                                <circle cx="86" cy="55" r="46" fill="black"/>
                            </mask>
                        </defs>
                        <circle cx="65" cy="65" r="62" fill="rgba(212,175,55,.04)"/>
                        <circle cx="65" cy="65" r="56" fill="rgba(212,175,55,.07)"/>
                        <circle cx="65" cy="65" r="52" fill="url(#moonGv4)" mask="url(#moonMv4)"/>
                        <path d="M 36,38 Q 24,65 36,92" stroke="rgba(255,252,200,.22)" stroke-width="3.5" fill="none" stroke-linecap="round"/>
                    </svg>
                    <div class="eid-stars">
                        <div class="eid-star eid-star-lg"></div>
                        <div class="eid-star eid-star-md"></div>
                        <div class="eid-star eid-star-sm"></div>
                    </div>
                </div>
            </div>
            <div class="eid-gold-line"></div>
        </div>

        {{-- ─── SECTION: Wallets ─────────────────────── --}}
        <div class="sec-head">
            <div class="sec-head-dot" style="background:var(--purple);box-shadow:0 0 8px var(--purple)"></div>
            <div class="sec-head-label">Wallet Overview</div>
        </div>

        <div class="wallet-grid">
            <div class="w-card wc-purple">
                <div class="w-card-top">
                    <div class="w-icon"><i class="fas fa-coins"></i></div>
                    <i class="fas fa-chevron-right w-arrow"></i>
                </div>
                <div class="w-label">Total Earnings</div>
                <div class="w-value">${{ number_format($data['total_earning'], 2) }}</div>
                <div class="w-note"><i class="fas fa-arrow-up" style="color:var(--purple)"></i> All-time cumulative</div>
            </div>

            <div class="w-card wc-green">
                <div class="w-card-top">
                    <div class="w-icon"><i class="fas fa-wallet"></i></div>
                    <i class="fas fa-chevron-right w-arrow"></i>
                </div>
                <div class="w-label">Online Wallet</div>
                <div class="w-value">${{ number_format($data['online_wallet'], 2) }}</div>
                <div class="w-note"><i class="fas fa-circle" style="color:var(--green);font-size:.45rem"></i> Available balance</div>
            </div>

            <div class="w-card wc-cyan">
                <div class="w-card-top">
                    <div class="w-icon"><i class="fas fa-chart-line"></i></div>
                    <i class="fas fa-chevron-right w-arrow"></i>
                </div>
                <div class="w-label">ROI Earnings</div>
                <div class="w-value">${{ number_format($data['roi'], 2) }}</div>
                <div class="w-note"><i class="fas fa-arrow-up" style="color:var(--cyan)"></i> Return on investment</div>
            </div>

            <div class="w-card wc-amber">
                <div class="w-card-top">
                    <div class="w-icon"><i class="fas fa-users"></i></div>
                    <i class="fas fa-chevron-right w-arrow"></i>
                </div>
                <div class="w-label">Direct / Indirect</div>
                <div class="w-value">${{ number_format($data['direct_indirect'], 2) }}</div>
                <div class="w-note"><i class="fas fa-arrow-up" style="color:var(--amber)"></i> Commission income</div>
            </div>

            <div class="w-card wc-pink">
                <div class="w-card-top">
                    <div class="w-icon"><i class="fas fa-chart-pie"></i></div>
                    <i class="fas fa-chevron-right w-arrow"></i>
                </div>
                <div class="w-label">Profit Sharing</div>
                <div class="w-value">${{ number_format($data['profit_share'], 2) }}</div>
                <div class="w-note"><i class="fas fa-calendar" style="color:var(--pink)"></i> Monthly distribution</div>
            </div>

            <div class="w-card wc-blue">
                <div class="w-card-top">
                    <div class="w-icon"><i class="fas fa-gift"></i></div>
                    <i class="fas fa-chevron-right w-arrow"></i>
                </div>
                <div class="w-label">Rewards Earned</div>
                <div class="w-value">${{ number_format($data['rewardWallet'], 2) }}</div>
                <div class="w-note"><i class="fas fa-trophy" style="color:var(--blue)"></i> Achievement bonuses</div>
            </div>

            <a href="{{ route('wallets.incentive') }}" class="w-card wc-gold" style="cursor:pointer">
                <div class="w-card-top">
                    <div class="w-icon"><i class="fas fa-star"></i></div>
                    <i class="fas fa-chevron-right w-arrow"></i>
                </div>
                <div class="w-label">Designation Incentive</div>
                <div class="w-value">${{ number_format($data['designation_incentive'], 2) }}</div>
                <div class="w-note"><i class="fas fa-external-link-alt" style="color:var(--gold);font-size:.55rem"></i> View details</div>
            </a>

            <div class="w-card wc-purple" style="--wc-accent:rgba(0,230,152,.25);">
                <div class="w-card-top">
                    <div class="w-icon" style="color:var(--green);background:rgba(0,230,152,.12)"><i class="fas fa-network-wired"></i></div>
                    <i class="fas fa-chevron-right w-arrow"></i>
                </div>
                <div class="w-label">Team Size</div>
                <div class="w-value" style="font-size:1.5rem;letter-spacing:0">{{ number_format($data['totalTeam']) }}</div>
                <div class="w-note"><i class="fas fa-user-plus" style="color:var(--green)"></i> Active network</div>
            </div>

            <div class="w-card wc-cyan" style="--wc-accent:rgba(155,89,255,.25);">
                <div class="w-card-top">
                    <div class="w-icon" style="color:var(--purple);background:rgba(155,89,255,.12)"><i class="fas fa-crown"></i></div>
                    <i class="fas fa-chevron-right w-arrow"></i>
                </div>
                <div class="w-label">Your Rank</div>
                <div class="w-value" style="font-size:1.3rem;letter-spacing:0">VISIONER</div>
                <div class="w-note"><i class="fas fa-star" style="color:var(--gold)"></i> Active member status</div>
            </div>
        </div>

        {{-- ─── SECTION: Chart + ROI ─────────────────── --}}
        <div class="sec-head">
            <div class="sec-head-dot" style="background:var(--cyan);box-shadow:0 0 8px var(--cyan)"></div>
            <div class="sec-head-label">Analytics &amp; ROI Control</div>
        </div>

        <div class="main-grid">
            {{-- Chart panel --}}
            <div class="glass-card">
                <div class="glass-card-head">
                    <div>
                        <div class="gc-title">Performance Overview</div>
                        <div class="gc-subtitle">Earnings &amp; team growth trend</div>
                    </div>
                    <div class="ftabs">
                        <button class="ftab active" data-period="7d">7D</button>
                        <button class="ftab" data-period="30d">30D</button>
                        <button class="ftab" data-period="90d">3M</button>
                        <button class="ftab" data-period="1y">1Y</button>
                    </div>
                </div>
                <div class="glass-card-body">
                    <div id="businessChart" style="min-height:340px;"></div>
                </div>
            </div>

            {{-- ROI side meters --}}
            <div class="roi-side">
                {{-- 2X --}}
                <div class="roi-meter-card" style="--roi-accent: linear-gradient(90deg, var(--purple), var(--cyan));">
                    <div class="rm-head">
                        <div class="rm-icon-wrap">
                            <div class="rm-icon" style="background:rgba(155,89,255,.15);color:var(--purple)"><i class="fas fa-chart-bar"></i></div>
                            <div>
                                <div class="rm-title">2X ROI Progress</div>
                                <small style="font-size:.62rem;color:var(--t2)">Investment return tracker</small>
                            </div>
                        </div>
                        <span class="rm-badge {{ $data['roi_stats']['has_reached_2x'] ? 'rm-done' : 'rm-active' }}">
                            {{ $data['roi_stats']['has_reached_2x'] ? 'Completed' : 'Active' }}
                        </span>
                    </div>
                    <div class="rm-track">
                        <div class="rm-fill" style="width:{{ min($data['roi_stats']['completion_percentage'],100) }}%;background:linear-gradient(90deg,var(--purple),var(--cyan))"></div>
                    </div>
                    <div class="rm-pct">{{ number_format(min($data['roi_stats']['completion_percentage'],100),1) }}%</div>
                    <div class="rm-stats">
                        <div>
                            <div class="rm-stat-val">${{ number_format($data['roi_stats']['invested_amount'],2) }}</div>
                            <div class="rm-stat-lbl">Invested</div>
                        </div>
                        <div>
                            <div class="rm-stat-val">${{ number_format($data['roi_stats']['total_roi_paid'],2) }}</div>
                            <div class="rm-stat-lbl">Earned</div>
                        </div>
                        <div>
                            <div class="rm-stat-val">${{ number_format($data['roi_stats']['remaining_amount'],2) }}</div>
                            <div class="rm-stat-lbl">Left</div>
                        </div>
                    </div>
                    @if($data['roi_stats']['has_reached_2x'])
                    <div class="rm-notice rn-green"><i class="fas fa-check-circle"></i> 2X ROI Target Achieved!</div>
                    @endif
                </div>

                {{-- 7X --}}
                <div class="roi-meter-card" style="--roi-accent:linear-gradient(90deg, var(--amber), var(--pink));">
                    <div class="rm-head">
                        <div class="rm-icon-wrap">
                            <div class="rm-icon" style="background:rgba(255,182,0,.12);color:var(--amber)"><i class="fas fa-shield-alt"></i></div>
                            <div>
                                <div class="rm-title">7X Withdrawal Control</div>
                                <small style="font-size:.62rem;color:var(--t2)">Withdrawal eligibility</small>
                            </div>
                        </div>
                        <span class="rm-badge {{ $data['roi_stats']['withdrawal_enabled'] ? 'rm-active' : 'rm-warn' }}">
                            {{ $data['roi_stats']['withdrawal_enabled'] ? 'Enabled' : 'Suspended' }}
                        </span>
                    </div>
                    <div class="rm-track">
                        <div class="rm-fill" style="width:{{ min($data['roi_stats']['completion_7x_percentage'],100) }}%;background:linear-gradient(90deg,var(--amber),var(--pink))"></div>
                    </div>
                    <div class="rm-pct">{{ number_format(min($data['roi_stats']['completion_7x_percentage'],100),1) }}%</div>
                    <div class="rm-stats">
                        <div>
                            <div class="rm-stat-val">${{ number_format($data['roi_stats']['seven_x_limit'],2) }}</div>
                            <div class="rm-stat-lbl">7X Limit</div>
                        </div>
                        <div>
                            <div class="rm-stat-val">${{ number_format($data['roi_stats']['total_roi_paid'],2) }}</div>
                            <div class="rm-stat-lbl">Earned</div>
                        </div>
                        <div>
                            <div class="rm-stat-val">${{ number_format($data['roi_stats']['remaining_7x_amount'],2) }}</div>
                            <div class="rm-stat-lbl">Until Limit</div>
                        </div>
                    </div>
                    @if(!$data['roi_stats']['withdrawal_enabled'])
                    <div class="rm-notice rn-amber"><i class="fas fa-ban"></i> Withdrawals suspended — top-up required</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Announcement --}}
        <div class="ann-card">
            <div class="ann-inner">
                <div class="ann-eyebrow">Announcement</div>
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
                    <div class="ann-chip hi">
                        <div class="ann-chip-val">+35%</div>
                        <div class="ann-chip-lbl">Increase</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ─── SECTION: Targets ────────────────────── --}}
        <div class="sec-head">
            <div class="sec-head-dot" style="background:var(--green);box-shadow:0 0 8px var(--green)"></div>
            <div class="sec-head-label">Targets &amp; Progress</div>
        </div>

        <div class="target-grid">
            @php $r = 70; $c = 2 * M_PI * $r; @endphp

            <div class="t-card">
                <h3>Reward Target</h3>
                <p>Track your progress towards the next reward milestone</p>
                <div class="ring-wrap">
                    <svg viewBox="0 0 168 168">
                        <circle class="ring-bg"   cx="84" cy="84" r="{{ $r }}"/>
                        <circle class="ring-fill" cx="84" cy="84" r="{{ $r }}"
                            stroke="url(#rg1)"
                            stroke-dasharray="{{ $c }}"
                            stroke-dashoffset="{{ $c - ($c * $data['reward'] / 100) }}"/>
                        <defs>
                            <linearGradient id="rg1" x1="0%" y1="0%" x2="100%" y2="0%">
                                <stop offset="0%" stop-color="#9b59ff"/>
                                <stop offset="100%" stop-color="#00d2ff"/>
                            </linearGradient>
                        </defs>
                    </svg>
                    <div class="ring-center">
                        <div class="ring-pct">{{ number_format($data['reward'],1) }}%</div>
                        <div class="ring-sub">Complete</div>
                    </div>
                </div>
                <button class="t-btn t-btn-reward" id="btnReward">
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
                            stroke="url(#rg2)"
                            stroke-dasharray="{{ $c }}"
                            stroke-dashoffset="{{ $c }}"/>
                        <defs>
                            <linearGradient id="rg2" x1="0%" y1="0%" x2="100%" y2="0%">
                                <stop offset="0%" stop-color="#0ea5e9"/>
                                <stop offset="100%" stop-color="#8b5cf6"/>
                            </linearGradient>
                        </defs>
                    </svg>
                    <div class="ring-center">
                        <div class="ring-pct">0%</div>
                        <div class="ring-sub">Complete</div>
                    </div>
                </div>
                <button class="t-btn t-btn-rank" id="btnRank">
                    <i class="fas fa-crown"></i> View Rank Progress
                </button>
            </div>
        </div>

        {{-- Reward Detail Panel --}}
        <div class="d-panel" id="panelReward">
            <div class="d-panel-head">
                <div class="d-ph-icon" style="background:rgba(155,89,255,.15);color:var(--purple)"><i class="fas fa-trophy"></i></div>
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
                    <div>
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
                <div class="d-ph-icon" style="background:rgba(14,165,233,.12);color:#0ea5e9"><i class="fas fa-crown"></i></div>
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
                             : 'https://placehold.co/52x52/'.$rk['hex'].'/FFFFFF?text='.substr($rk['name'],0,1);
                    @endphp
                    <div class="rank-item {{ $won ? 'won' : '' }}">
                        <img src="{{ $iu }}" alt="{{ $rk['name'] }}" class="rank-img"
                             onerror="this.src='https://placehold.co/52x52/{{ $rk['hex'] }}/FFFFFF?text={{ substr($rk['name'],0,1) }}'">
                        <div class="rank-name">{{ $rk['name'] }}</div>
                        <div class="rank-req">{{ $rk['req'] }}</div>
                        <div class="rank-track"><div class="rank-bar" style="width:{{ $rp }}%"></div></div>
                        <div class="rank-count">{{ $tm }} / {{ $rk['size'] }}</div>
                        @if($won)<div class="rank-done"><i class="fas fa-check"></i> Achieved</div>@endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>{{-- /adb-body --}}
</div>{{-- /adb --}}
@endsection

@section('page_js')
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ── ApexCharts (dark theme) ─────────────────────────── */
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
                { name: 'Earnings ($)', type: 'column', data: d.e },
                { name: 'Team Growth',  type: 'line',   data: d.t }
            ],
            chart: {
                height: 340, type: 'line',
                fontFamily: "'Poppins',sans-serif",
                background: 'transparent',
                toolbar: { show: true, tools: { download: true, zoom: true, reset: true, selection: false, zoomin: false, zoomout: false, pan: false } },
                animations: { enabled: true, easing: 'easeinout', speed: 600 }
            },
            theme: { mode: 'dark' },
            colors: ['#9b59ff', '#00d2ff'],
            stroke: { width: [0, 3], curve: 'smooth' },
            plotOptions: { bar: { columnWidth: '42%', borderRadius: 8, borderRadiusApplication: 'end' } },
            fill: {
                type: ['gradient', 'solid'],
                gradient: { shade: 'dark', type: 'vertical', opacityFrom: .9, opacityTo: .4, gradientToColors: ['#00d2ff'] }
            },
            dataLabels: { enabled: false },
            markers: { size: [0, 5], strokeWidth: 2, strokeColors: '#0e1628', colors: ['#9b59ff', '#00d2ff'] },
            xaxis: {
                categories: d.x,
                labels: { style: { fontSize: '11px', fontWeight: 600, colors: '#4f617a' } },
                axisBorder: { show: false }, axisTicks: { show: false }
            },
            yaxis: [
                { title: { text: 'Earnings ($)', style: { color: '#9b59ff', fontWeight: 600, fontSize: '11px' } },
                  labels: { formatter: v => '$' + (v||0).toFixed(0), style: { colors: '#9b59ff', fontSize: '10px' } } },
                { opposite: true,
                  title: { text: 'Team Members', style: { color: '#00d2ff', fontWeight: 600, fontSize: '11px' } },
                  labels: { formatter: v => Math.round(v||0), style: { colors: '#00d2ff', fontSize: '10px' } } }
            ],
            tooltip: { shared: true, intersect: false, theme: 'dark',
                y: [{ formatter: y => y != null ? '$' + y.toFixed(2) : y },
                    { formatter: y => y != null ? Math.round(y) + ' members' : y }] },
            grid: { borderColor: 'rgba(255,255,255,.06)', strokeDashArray: 4, padding: { left: 10, right: 10 } },
            legend: { position: 'top', horizontalAlign: 'right', fontSize: '12px', fontWeight: 600,
                      markers: { width: 9, height: 9, radius: 3 }, itemMargin: { horizontal: 10 } }
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

    /* ── Detail panels toggle ────────────────────────────── */
    function toggle(show, hide) {
        document.getElementById(hide).classList.remove('open');
        const p = document.getElementById(show);
        const wasOpen = p.classList.contains('open');
        p.classList.toggle('open', !wasOpen);
        if (!wasOpen) setTimeout(() => p.scrollIntoView({ behavior: 'smooth', block: 'nearest' }), 60);
    }
    document.getElementById('btnReward').addEventListener('click', () => toggle('panelReward', 'panelRank'));
    document.getElementById('btnRank').addEventListener('click',   () => toggle('panelRank',   'panelReward'));

    /* ── Animated counters ───────────────────────────────── */
    function countUp(el, target, prefix, decimals) {
        const dur = 1800, start = performance.now();
        function step(now) {
            const p = Math.min((now - start) / dur, 1);
            const ease = 1 - Math.pow(1 - p, 3);
            el.textContent = prefix + (target * ease).toFixed(decimals);
            if (p < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }

    function initCounters() {
        document.querySelectorAll('.kpi-t-val, .w-value, #heroBalance').forEach(el => {
            const raw = el.textContent.trim();
            const prefix = raw.startsWith('$') ? '$' : '';
            const num = parseFloat(raw.replace(/[^0-9.]/g, ''));
            if (!isNaN(num) && num > 0) {
                const decimals = raw.includes('.') ? 2 : 0;
                el.textContent = prefix + (0).toFixed(decimals);
                countUp(el, num, prefix, decimals);
            }
        });
    }

    /* ── Entrance animations ─────────────────────────────── */
    const enterEls = document.querySelectorAll('.w-card, .roi-meter-card, .t-card, .glass-card, .kpi-tile, .ann-card');
    enterEls.forEach((el, i) => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(22px)';
        el.style.transition = `opacity .5s ease ${i * 45}ms, transform .5s ease ${i * 45}ms`;
    });
    const io = new IntersectionObserver(entries => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                e.target.style.opacity = '1';
                e.target.style.transform = 'translateY(0)';
                io.unobserve(e.target);
            }
        });
    }, { threshold: 0.07 });
    enterEls.forEach(el => io.observe(el));

    setTimeout(initCounters, 300);
});
</script>
@endsection
