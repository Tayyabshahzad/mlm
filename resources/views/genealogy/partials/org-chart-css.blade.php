{{--
    Org chart (pan / zoom / collapse, details panel on the right).
    Needs: $nodeDataArray (GenealogyController::orgNode rows). Optional: $ocTree ('standard'|'saving'), $ocTeamLabel.
--}}
<style>
    /* ── Org chart canvas ── */
    .oc-wrap { position: relative; height: 70vh; min-height: 520px; overflow: hidden; border: 1px solid #ebedf3; border-radius: 12px;
        background-color: #fafbfc; background-image: radial-gradient(circle, #e4e6ef 1px, transparent 1px); background-size: 20px 20px;
        cursor: grab; touch-action: none; user-select: none; }
    .oc-wrap.dragging { cursor: grabbing; }
    .oc-stage { position: absolute; left: 0; top: 0; transform-origin: 0 0; }
    .oc-links { position: absolute; left: 0; top: 0; overflow: visible; pointer-events: none; }
    .oc-links path { fill: none; stroke: #c9b6ff; stroke-width: 2; stroke-linecap: round; }

    .oc-node { position: absolute; width: 244px; height: 96px; }
    .oc-card { position: relative; display: flex; align-items: center; gap: 12px; width: 100%; height: 100%; padding: 0 14px;
        background: #fff; border: 1px solid #ebedf3; border-radius: 12px; box-shadow: 0 2px 8px rgba(30,30,45,.06);
        text-align: left; cursor: pointer; transition: box-shadow .25s, border-color .25s, transform .25s; overflow: visible; }
    .oc-card:hover { box-shadow: 0 8px 22px rgba(137,80,252,.18); border-color: #b99bff; transform: translateY(-2px); }
    .oc-card:focus { outline: none; }
    .oc-card.is-root { border-color: #d6c4ff; box-shadow: 0 0 0 1px #e8ddff, 0 4px 14px rgba(137,80,252,.15); }
    .oc-card.is-match { border-color: #ffa800; box-shadow: 0 0 0 3px rgba(255,168,0,.35); }
    .oc-card.is-open { border-color: #8950fc; box-shadow: 0 0 0 3px rgba(137,80,252,.25); }
    .oc-stripe { position: absolute; left: 0; top: 0; bottom: 0; width: 3px; border-radius: 12px 0 0 12px; }
    .oc-avatar { flex-shrink: 0; width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: 14px; color: #fff; object-fit: cover; box-shadow: 0 1px 3px rgba(0,0,0,.12); transition: transform .25s; }
    .oc-card:hover .oc-avatar { transform: scale(1.05); }
    .oc-body { min-width: 0; flex: 1; }
    .oc-name { display: block; font-size: 13px; font-weight: 600; color: #181c32; line-height: 1.2; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .oc-user { display: block; font-size: 12px; color: #7e8299; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .oc-tag  { display: inline-block; margin-top: 4px; padding: 1px 6px; border-radius: 4px; font-size: 10px; font-weight: 600; line-height: 1.5; }
    .oc-tag.vip { background: #fff4de; color: #a86400; }
    .oc-tag.standard { background: #eee5ff; color: #6f3fe6; }
    .oc-tag.saving { background: #c9f7f5; color: #0f8a83; }
    .oc-count { flex-shrink: 0; min-width: 36px; padding: 4px 6px; border: 1px solid #ebedf3; border-radius: 8px; background: #f8f9fb; text-align: center; }
    .oc-count b { display: block; font-size: 13px; color: #181c32; line-height: 1; }
    .oc-count small { display: block; font-size: 7.5px; font-weight: 700; color: #b5b5c3; text-transform: uppercase; letter-spacing: .04em; margin-top: 2px; }
    .oc-crown { position: absolute; top: -12px; left: 50%; transform: translateX(-50%); width: 24px; height: 24px; border-radius: 50%;
        background: linear-gradient(135deg, #ffc700, #ff8a00); display: flex; align-items: center; justify-content: center; box-shadow: 0 0 0 2px #fff, 0 2px 6px rgba(0,0,0,.15); }
    .oc-toggle { position: absolute; left: 50%; bottom: -12px; transform: translateX(-50%); z-index: 2; min-width: 24px; height: 24px; padding: 0 6px; border-radius: 12px;
        border: 1px solid #e4e6ef; background: #fff; color: #5e6278; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700;
        box-shadow: 0 1px 4px rgba(0,0,0,.1); cursor: pointer; transition: box-shadow .2s, transform .1s; }
    .oc-toggle:hover { box-shadow: 0 3px 8px rgba(0,0,0,.16); color: #8950fc; }
    .oc-toggle:active { transform: translateX(-50%) scale(.9); }

    .oc-tools { position: absolute; right: 12px; bottom: 12px; z-index: 5; display: flex; flex-direction: column; gap: 6px; }
    .oc-tools button { width: 34px; height: 34px; border-radius: 8px; border: 1px solid #e4e6ef; background: #fff; color: #5e6278; font-size: 15px;
        box-shadow: 0 1px 4px rgba(0,0,0,.08); }
    .oc-tools button:hover { color: #8950fc; border-color: #c9b6ff; }
    .oc-hint { position: absolute; left: 12px; bottom: 12px; z-index: 5; font-size: 11px; color: #b5b5c3; pointer-events: none; }

    /* ── Slide-in details panel (right) ── */
    .oc-overlay { position: fixed; inset: 0; background: rgba(24,28,50,.25); z-index: 1040; opacity: 0; visibility: hidden; transition: opacity .25s, visibility .25s; }
    .oc-overlay.open { opacity: 1; visibility: visible; }
    .oc-panel { position: fixed; right: 0; top: 0; bottom: 0; width: 400px; max-width: 92vw; background: #fff; z-index: 1041; box-shadow: -8px 0 30px rgba(0,0,0,.12);
        transform: translateX(105%); transition: transform .3s ease; display: flex; flex-direction: column; }
    .oc-panel.open { transform: translateX(0); }
    .oc-panel-head { padding: 20px 20px 16px; background: linear-gradient(135deg, #8950fc 0%, #6f3fe6 100%); color: #fff; position: relative; }
    .oc-panel-head .oc-avatar { width: 56px; height: 56px; font-size: 18px; box-shadow: 0 0 0 3px rgba(255,255,255,.35); }
    .oc-panel-close { position: absolute; right: 14px; top: 12px; border: 0; background: rgba(255,255,255,.18); color: #fff; width: 30px; height: 30px; border-radius: 8px; font-size: 18px; line-height: 1; }
    .oc-panel-body { padding: 16px 20px 24px; overflow-y: auto; flex: 1; }
    .oc-sec { font-size: 11px; font-weight: 700; color: #b5b5c3; text-transform: uppercase; letter-spacing: .05em; margin: 18px 0 8px; }
    .oc-sec:first-child { margin-top: 0; }
    .oc-row { display: flex; justify-content: space-between; gap: 12px; padding: 7px 0; border-bottom: 1px dashed #ebedf3; font-size: 13px; }
    .oc-row span:first-child { color: #7e8299; }
    .oc-row span:last-child { color: #181c32; font-weight: 600; text-align: right; }
    .oc-stats { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
    .oc-stat { background: #f8f5ff; border: 1px solid #eee5ff; border-radius: 10px; padding: 10px 12px; }
    .oc-stat b { display: block; font-size: 18px; color: #4b23b5; }
    .oc-stat small { color: #7e8299; font-size: 11px; }
    .oc-lv { display: flex; align-items: center; gap: 8px; font-size: 12px; padding: 5px 0; }
    .oc-lv .bar { flex: 1; height: 6px; background: #f3f6f9; border-radius: 3px; overflow: hidden; }
    .oc-lv .bar i { display: block; height: 100%; background: #c9b6ff; }
    .oc-lv.ok .bar i { background: #1bc5bd; }
    .oc-lv .lbl { width: 54px; color: #7e8299; }
    .oc-lv .num { width: 88px; text-align: right; font-weight: 600; color: #3f4254; }
</style>
