@extends('demo.layout.auth')
@section('title', 'Sign In')

@section('nav_bar')
    <span>Don't have an account?</span>
    <a href="{{ route('register') }}">Sign Up</a>
@endsection

@section('content')
<div class="al-card" style="max-width:460px;">

    <div class="al-card-header">
        <div class="al-brand-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none">
                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <div class="al-card-title">Welcome Back</div>
        <div class="al-card-sub">Sign in to your GVI account to continue</div>
    </div>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="f-group">
            <label class="f-label">Email or Username</label>
            <div class="f-wrap">
                <div class="f-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round"/>
                        <polyline points="22,6 12,13 2,6" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </div>
                <input class="f-input" type="text" name="login"
                       placeholder="your@email.com or username"
                       value="{{ old('login') }}" autocomplete="off" required />
            </div>
            @error('login') <div class="f-error">{{ $message }}</div> @enderror
        </div>

        <div class="f-group">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:.4rem;">
                <label class="f-label" style="margin-bottom:0;">Password</label>
            </div>
            <div class="f-wrap">
                <div class="f-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                        <rect x="3" y="11" width="18" height="11" rx="2" stroke="#94a3b8" stroke-width="1.8"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </div>
                <input class="f-input" type="password" name="password" id="pw"
                       placeholder="Enter your password" autocomplete="off" required />
                <button type="button" onclick="togglePw()" tabindex="-1"
                        style="position:absolute;right:13px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#94a3b8;padding:0;display:flex;">
                    <svg id="eye-icon" width="17" height="17" viewBox="0 0 24 24" fill="none">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="1.8"/>
                        <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/>
                    </svg>
                </button>
            </div>
            @error('password') <div class="f-error">{{ $message }}</div> @enderror
        </div>

        <div style="margin-top:1.6rem;">
            <button type="submit" class="f-btn">
                Sign In
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="margin-left:.5rem;vertical-align:middle;">
                    <path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
    </form>

    <p style="text-align:center; margin-top:1.35rem; font-size:.83rem; color:#94a3b8;">
        New to GVI?
        <a href="{{ route('register') }}" style="color:var(--primary);font-weight:600;text-decoration:none;">Create a free account →</a>
    </p>

</div>
@endsection

@section('page_js')
<style>
/* ── Independence Day Popup ─────────────────────────── */
#pak-overlay {
    position: fixed; inset: 0; z-index: 9999;
    background: rgba(0,0,0,.72);
    display: flex; align-items: center; justify-content: center;
    backdrop-filter: blur(4px);
    animation: pak-fade-in .4s ease;
}
@keyframes pak-fade-in {
    from { opacity: 0; } to { opacity: 1; }
}
#pak-modal {
    position: relative;
    background: linear-gradient(160deg, #010f04 0%, #022b0e 55%, #010f04 100%);
    border: 1px solid rgba(0,165,80,.40);
    border-radius: 20px;
    padding: 2.8rem 2.4rem 2.4rem;
    max-width: 480px; width: 92%;
    text-align: center;
    box-shadow: 0 0 0 1px rgba(0,165,80,.10), 0 32px 80px rgba(0,0,0,.80), 0 0 60px rgba(0,165,80,.18);
    animation: pak-slide-up .45s cubic-bezier(.22,1,.36,1);
    overflow: hidden;
}
@keyframes pak-slide-up {
    from { opacity:0; transform:translateY(40px) scale(.95); }
    to   { opacity:1; transform:translateY(0)   scale(1);    }
}
/* corner flag stripes */
#pak-modal::before {
    content: '';
    position: absolute; top: 0; left: 0; right: 0; height: 4px;
    background: linear-gradient(90deg, #01411c 0%, #00a550 40%, #ffffff 40%, #ffffff 60%, #00a550 60%, #01411c 100%);
    border-radius: 20px 20px 0 0;
}
/* subtle glow orbs */
#pak-modal::after {
    content: '';
    position: absolute; inset: 0; pointer-events: none;
    background:
        radial-gradient(ellipse 300px 200px at 10% 80%, rgba(0,165,80,.18) 0%, transparent 60%),
        radial-gradient(ellipse 200px 160px at 90% 10%, rgba(255,255,255,.04) 0%, transparent 55%);
}
.pak-crescent {
    font-size: 3.6rem; line-height: 1;
    margin-bottom: .6rem;
    filter: drop-shadow(0 0 18px rgba(0,200,80,.6));
    animation: pak-glow-pulse 2s ease infinite;
}
@keyframes pak-glow-pulse {
    0%,100% { filter: drop-shadow(0 0 14px rgba(0,200,80,.55)); }
    50%      { filter: drop-shadow(0 0 28px rgba(0,200,80,.90)); }
}
.pak-title {
    font-size: 1.45rem; font-weight: 900; color: #fff;
    letter-spacing: -.02em; margin-bottom: .3rem;
}
.pak-sub {
    font-size: .8rem; color: #4ade80; font-weight: 700;
    letter-spacing: .12em; text-transform: uppercase;
    margin-bottom: 1.6rem;
}
.pak-quote-box {
    position: relative;
    background: rgba(0,165,80,.08);
    border: 1px solid rgba(0,165,80,.22);
    border-radius: 12px;
    padding: 1.2rem 1.4rem;
    margin-bottom: 1.8rem;
}
.pak-quote-mark {
    font-size: 3rem; line-height: .6; color: rgba(0,165,80,.35);
    font-family: Georgia, serif; position: absolute; top: .6rem; left: .9rem;
}
.pak-quote-text {
    font-size: .9rem; color: #e2e8f0; line-height: 1.7;
    font-style: italic; position: relative; z-index: 1;
    padding-left: .6rem;
}
.pak-quote-author {
    margin-top: .7rem; font-size: .72rem; font-weight: 700;
    color: #4ade80; letter-spacing: .06em; text-transform: uppercase;
}
.pak-btn {
    display: inline-flex; align-items: center; gap: .55rem;
    background: linear-gradient(135deg, #01411c, #00a550);
    color: #fff; font-weight: 800; font-size: .95rem;
    border: none; border-radius: 50px;
    padding: .85rem 2.2rem; cursor: pointer;
    box-shadow: 0 0 0 1px rgba(0,165,80,.4), 0 8px 28px rgba(0,165,80,.35);
    transition: transform .18s, box-shadow .18s;
    position: relative; z-index: 1;
}
.pak-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 0 0 1px rgba(0,165,80,.6), 0 14px 36px rgba(0,165,80,.50);
}
.pak-btn:active { transform: translateY(0); }
.pak-skip {
    display: block; margin-top: 1rem;
    font-size: .75rem; color: rgba(255,255,255,.35); cursor: pointer;
    background: none; border: none; text-decoration: underline;
}
.pak-skip:hover { color: rgba(255,255,255,.6); }

/* ── Confetti Canvas ─────────────────────────────────── */
#pak-confetti {
    position: fixed; inset: 0;
    pointer-events: none; z-index: 10000;
}
/* ── Toast ───────────────────────────────────────────── */
#pak-toast {
    position: fixed; bottom: 2rem; left: 50%; transform: translateX(-50%) translateY(80px);
    background: linear-gradient(135deg, #01411c, #00a550);
    color: #fff; font-weight: 800; font-size: 1rem;
    padding: .85rem 2rem; border-radius: 50px;
    box-shadow: 0 8px 30px rgba(0,165,80,.45);
    z-index: 10001; white-space: nowrap;
    transition: transform .5s cubic-bezier(.22,1,.36,1), opacity .5s;
    opacity: 0;
}
#pak-toast.show { transform: translateX(-50%) translateY(0); opacity: 1; }
</style>

{{-- Independence Day Popup --}}
<div id="pak-overlay">
    <div id="pak-modal">
        <div class="pak-crescent">☪</div>
        <div class="pak-title">Happy Independence Day!</div>
        <div class="pak-sub">🇵🇰 79th · 14 August 2026 · Pakistan Zindabad 🇵🇰</div>

        <div class="pak-quote-box">
            <div class="pak-quote-mark">"</div>
            <div class="pak-quote-text">
                With faith, discipline and selfless devotion to duty, there is nothing worthwhile that you cannot achieve.
            </div>
            <div class="pak-quote-author">— Quaid-e-Azam Muhammad Ali Jinnah</div>
        </div>

        <button class="pak-btn" onclick="celebrateIndependence()">
            🎉 Happy Independence Day!
        </button>
        <button class="pak-skip" onclick="closePakPopup()">Skip for now</button>
    </div>
</div>

{{-- Confetti Canvas --}}
<canvas id="pak-confetti"></canvas>

{{-- Toast --}}
<div id="pak-toast">🇵🇰 Pakistan Zindabad! Jashn-e-Azadi Mubarak! ☪</div>

<script>
/* ── Popup close ──────────────────────────────────── */
function closePakPopup() {
    var overlay = document.getElementById('pak-overlay');
    overlay.style.transition = 'opacity .35s';
    overlay.style.opacity = '0';
    setTimeout(function(){ overlay.style.display = 'none'; }, 350);
}

/* ── Celebration: confetti + toast ───────────────── */
function celebrateIndependence() {
    closePakPopup();
    setTimeout(function() {
        launchConfetti();
        showPakToast();
    }, 400);
}

/* ── Confetti ─────────────────────────────────────── */
function launchConfetti() {
    var canvas = document.getElementById('pak-confetti');
    var ctx    = canvas.getContext('2d');
    canvas.width  = window.innerWidth;
    canvas.height = window.innerHeight;

    var colors = ['#00a550','#4ade80','#01411c','#ffffff','#86efac','#d1fae5','#00e676','#fff9c4'];
    var pieces = [];
    var total  = 160;

    for (var i = 0; i < total; i++) {
        pieces.push({
            x:      Math.random() * canvas.width,
            y:      -10 - Math.random() * 200,
            w:      6  + Math.random() * 8,
            h:      10 + Math.random() * 8,
            color:  colors[Math.floor(Math.random() * colors.length)],
            rot:    Math.random() * 360,
            rotSpd: (Math.random() - .5) * 8,
            vx:     (Math.random() - .5) * 3,
            vy:     2.5 + Math.random() * 3.5,
            alpha:  1,
            shape:  Math.random() > .5 ? 'rect' : 'circle'
        });
    }

    var frame;
    var startTime = Date.now();
    var duration  = 4500; // ms

    function draw() {
        var elapsed = Date.now() - startTime;
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        var alive = false;
        pieces.forEach(function(p) {
            if (p.y > canvas.height + 20) return;
            alive = true;

            // fade out in last second
            if (elapsed > duration - 1000) {
                p.alpha = Math.max(0, p.alpha - .018);
            }

            ctx.save();
            ctx.globalAlpha = p.alpha;
            ctx.translate(p.x + p.w / 2, p.y + p.h / 2);
            ctx.rotate(p.rot * Math.PI / 180);

            ctx.fillStyle = p.color;
            if (p.shape === 'circle') {
                ctx.beginPath();
                ctx.arc(0, 0, p.w / 2, 0, Math.PI * 2);
                ctx.fill();
            } else {
                ctx.fillRect(-p.w / 2, -p.h / 2, p.w, p.h);
            }
            ctx.restore();

            p.x   += p.vx;
            p.y   += p.vy;
            p.rot += p.rotSpd;
            p.vy  *= 1.005; // slight gravity increase
        });

        if (alive && elapsed < duration + 1000) {
            frame = requestAnimationFrame(draw);
        } else {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            cancelAnimationFrame(frame);
        }
    }
    draw();
}

/* ── Toast ─────────────────────────────────────────── */
function showPakToast() {
    var t = document.getElementById('pak-toast');
    t.classList.add('show');
    setTimeout(function(){ t.classList.remove('show'); }, 4000);
}

/* ── Password toggle ────────────────────────────────── */
function togglePw() {
    var pw = document.getElementById('pw');
    var ic = document.getElementById('eye-icon');
    if (pw.type === 'password') {
        pw.type = 'text';
        ic.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><line x1="1" y1="1" x2="23" y2="23" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>';
    } else {
        pw.type = 'password';
        ic.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/>';
    }
}
</script>
@endsection
