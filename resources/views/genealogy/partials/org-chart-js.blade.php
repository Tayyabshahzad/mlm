{{--
    Org chart (pan / zoom / collapse, details panel on the right).
    Needs: $nodeDataArray (GenealogyController::orgNode rows). Optional: $ocTree ('standard'|'saving'), $ocTeamLabel.
--}}
<script>
(function () {
    const NODES   = @json($nodeDataArray);
    const DETAILS = @json(route('genealogy.team.member', ['member' => '__ID__', 'tree' => $ocTree ?? 'standard']));
    const W = 244, H = 96, GAP_X = 36, GAP_Y = 76, PAD = 80, OPEN_DEPTH = Infinity; // open everything on load, like the old tree

    const wrap = document.getElementById('ocWrap'), stage = document.getElementById('ocStage'), svg = document.getElementById('ocLinks');
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const money = v => '$' + Number(v || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    const GRADS = [['#8950fc','#6f3fe6'],['#1bc5bd','#0bb7af'],['#ffa800','#ff8a00'],['#f64e60','#ee2d41'],['#3699ff','#187de4'],['#20c997','#12a37b']];
    const STRIPE = {active: '#1bc5bd', inactive: '#b5b5c3', blocked: '#f64e60'};

    // ── build tree ──
    const byId = {}; NODES.forEach(n => byId[n.key] = Object.assign(n, {children: [], depth: 0}));
    let root = null;
    NODES.forEach(n => (n.parent && byId[n.parent]) ? byId[n.parent].children.push(n) : (root = root || n));
    (function walk(n, d) { n.depth = d; n.open = d < OPEN_DEPTH; n.size = 0; n.children.forEach(c => { walk(c, d + 1); n.size += 1 + c.size; }); })(root, 0);
    document.getElementById('ocTotal').textContent = root.size;

    function avatar(n, cls) {
        const g = GRADS[n.key % GRADS.length];
        return n.image
            ? `<img src="${esc(n.image)}" alt="" class="oc-avatar ${cls || ''}">`
            : `<span class="oc-avatar ${cls || ''}" style="background:linear-gradient(135deg,${g[0]},${g[1]})">${esc(n.initials)}</span>`;
    }
    function tag(plan) {
        const c = plan.startsWith('VIP') ? 'vip' : (plan === 'Saving' ? 'saving' : 'standard');
        return `<span class="oc-tag ${c}">${esc(plan)}</span>`;
    }

    // ── layout: each subtree is as wide as its visible children ──
    function measure(n) {
        const kids = n.open ? n.children : [];
        n.w = kids.length ? Math.max(W, kids.reduce((s, c) => s + measure(c), 0) + GAP_X * (kids.length - 1)) : W;
        return n.w;
    }
    function place(n, left, top) {
        n.x = left + n.w / 2 - W / 2; n.y = top;
        if (!n.open) return;
        let x = left + (n.w - (n.children.reduce((s, c) => s + c.w, 0) + GAP_X * (n.children.length - 1))) / 2;
        n.children.forEach(c => { place(c, x, top + H + GAP_Y); x += c.w + GAP_X; });
    }
    function link(p, c) {
        const x1 = p.x + W / 2, y1 = p.y + H, x2 = c.x + W / 2, y2 = c.y, my = y1 + GAP_Y / 2, r = Math.min(12, Math.abs(x2 - x1) / 2);
        if (Math.abs(x2 - x1) < 1) return `M${x1} ${y1} L${x2} ${y2}`;
        const s = x2 > x1 ? 1 : -1;
        return `M${x1} ${y1} L${x1} ${my - r} Q${x1} ${my} ${x1 + s * r} ${my} L${x2 - s * r} ${my} Q${x2} ${my} ${x2} ${my + r} L${x2} ${y2}`;
    }

    let openId = null, matchIds = new Set();
    function render() {
        measure(root); place(root, PAD, PAD);
        const shown = []; (function collect(n) { shown.push(n); if (n.open) n.children.forEach(collect); })(root);
        const width = Math.max(...shown.map(n => n.x + W)) + PAD, height = Math.max(...shown.map(n => n.y + H)) + PAD;
        stage.style.width = width + 'px'; stage.style.height = height + 'px';
        svg.setAttribute('width', width); svg.setAttribute('height', height);

        svg.innerHTML = shown.filter(n => n.open).flatMap(p => p.children.map(c => `<path d="${link(p, c)}"/>`)).join('');
        stage.querySelectorAll('.oc-node').forEach(e => e.remove());
        const frag = document.createDocumentFragment();
        shown.forEach(n => {
            const el = document.createElement('div');
            el.className = 'oc-node'; el.style.left = n.x + 'px'; el.style.top = n.y + 'px'; el.dataset.id = n.key;
            el.innerHTML = `
                <button type="button" class="oc-card ${n === root ? 'is-root' : ''} ${matchIds.has(n.key) ? 'is-match' : ''} ${openId === n.key ? 'is-open' : ''}" data-act="open">
                    <span class="oc-stripe" style="background:${STRIPE[n.status] || STRIPE.inactive}"></span>
                    ${n === root ? '<span class="oc-crown"><i class="fas fa-crown" style="color:#fff;font-size:10px"></i></span>' : ''}
                    ${avatar(n)}
                    <span class="oc-body">
                        <span class="oc-name">${esc(n.name)}</span>
                        <span class="oc-user">@${esc(n.username)}</span>
                        ${tag(n.plan)}
                    </span>
                    ${n.children.length ? `<span class="oc-count"><b>${n.size}</b><small>team</small></span>` : ''}
                </button>
                ${n.children.length ? `<button type="button" class="oc-toggle" data-act="toggle" title="${n.open ? 'Collapse' : 'Expand'}">${n.open ? '&minus;' : '+' + n.children.length}</button>` : ''}`;
            frag.appendChild(el);
        });
        stage.appendChild(frag);
    }

    // ── pan & zoom ──
    let view = {x: 0, y: 0, k: 1};
    const apply = () => stage.style.transform = `translate(${view.x}px,${view.y}px) scale(${view.k})`;
    function zoomAt(k, cx, cy) {
        k = Math.min(2, Math.max(0.1, k));
        view.x = cx - (cx - view.x) * (k / view.k); view.y = cy - (cy - view.y) * (k / view.k); view.k = k; apply();
    }
    function fit() {
        const w = stage.offsetWidth, h = stage.offsetHeight, k = Math.min(1, wrap.clientWidth / w, wrap.clientHeight / h);
        view.k = Math.max(0.1, k); view.x = (wrap.clientWidth - w * view.k) / 2; view.y = Math.max(0, (wrap.clientHeight - h * view.k) / 2); apply();
    }
    function centerOn(n) {
        view.k = Math.max(view.k, 0.8);
        view.x = wrap.clientWidth / 2 - (n.x + W / 2) * view.k; view.y = wrap.clientHeight / 3 - (n.y + H / 2) * view.k; apply();
    }
    wrap.addEventListener('wheel', e => { e.preventDefault(); const r = wrap.getBoundingClientRect(); zoomAt(view.k * (e.deltaY < 0 ? 1.12 : 1 / 1.12), e.clientX - r.left, e.clientY - r.top); }, {passive: false});
    let drag = null;
    wrap.addEventListener('pointerdown', e => { if (e.target.closest('button')) return; drag = {x: e.clientX - view.x, y: e.clientY - view.y}; wrap.classList.add('dragging'); wrap.setPointerCapture(e.pointerId); });
    wrap.addEventListener('pointermove', e => { if (!drag) return; view.x = e.clientX - drag.x; view.y = e.clientY - drag.y; apply(); });
    ['pointerup', 'pointercancel'].forEach(t => wrap.addEventListener(t, () => { drag = null; wrap.classList.remove('dragging'); }));
    const mid = () => [wrap.clientWidth / 2, wrap.clientHeight / 2];
    document.getElementById('ocZoomIn').onclick  = () => zoomAt(view.k * 1.25, ...mid());
    document.getElementById('ocZoomOut').onclick = () => zoomAt(view.k / 1.25, ...mid());
    document.getElementById('ocFit').onclick = fit;

    // ── expand / collapse ──
    const setAll = open => (function f(n) { n.open = open || n === root; n.children.forEach(f); })(root);
    document.getElementById('ocExpandAll').onclick = () => { setAll(true); render(); fit(); };
    document.getElementById('ocCollapseAll').onclick = () => { setAll(false); render(); fit(); };

    stage.addEventListener('click', e => {
        const btn = e.target.closest('[data-act]'); if (!btn) return;
        const n = byId[btn.closest('.oc-node').dataset.id];
        if (btn.dataset.act === 'toggle') { n.open = !n.open; render(); }
        else openPanel(n);
    });

    // ── search: highlight, open the path and centre on the first match ──
    let t = null;
    document.getElementById('ocSearch').addEventListener('input', e => {
        clearTimeout(t);
        t = setTimeout(() => {
            const q = e.target.value.trim().toLowerCase();
            matchIds = new Set(q ? NODES.filter(n => n.name.toLowerCase().includes(q) || n.username.toLowerCase().includes(q)).map(n => n.key) : []);
            matchIds.forEach(id => { let p = byId[byId[id].parent]; while (p) { p.open = true; p = byId[p.parent]; } });
            render();
            const first = [...matchIds][0];
            if (first) centerOn(byId[first]);
        }, 250);
    });

    // ── details panel ──
    const panel = document.getElementById('ocPanel'), overlay = document.getElementById('ocOverlay'), body = document.getElementById('ocPBody');
    function closePanel() { panel.classList.remove('open'); overlay.classList.remove('open'); panel.setAttribute('aria-hidden', 'true'); openId = null; render(); }
    overlay.onclick = closePanel; document.getElementById('ocClose').onclick = closePanel;
    document.addEventListener('keydown', e => e.key === 'Escape' && closePanel());

    function openPanel(n) {
        openId = n.key; render();
        document.getElementById('ocPAvatar').innerHTML = avatar(n);
        document.getElementById('ocPName').textContent = n.name;
        document.getElementById('ocPUser').textContent = '@' + n.username;
        document.getElementById('ocPTags').innerHTML = tag(n.plan);
        body.innerHTML = '<div class="text-center text-muted py-10"><span class="spinner spinner-primary mr-3"></span> Loading…</div>';
        panel.classList.add('open'); overlay.classList.add('open'); panel.setAttribute('aria-hidden', 'false');

        fetch(DETAILS.replace('__ID__', n.key), {headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'}})
            .then(r => r.ok ? r.json() : Promise.reject(r.status))
            .then(d => { if (openId === n.key) body.innerHTML = details(d); })
            .catch(() => { body.innerHTML = '<div class="alert alert-light-danger">Could not load the details. Please try again.</div>'; });
    }
    const row = (k, v) => `<div class="oc-row"><span>${esc(k)}</span><span>${v}</span></div>`;
    function details(d) {
        const b = d.basic, st = b.status === 'Active' ? 'success' : (b.status === 'Blocked' ? 'danger' : 'dark');
        let h = '<div class="oc-sec">Basic info</div>'
            + row('Joined', esc(b.joined || '—'))
            + row('Sponsor', b.sponsor ? '@' + esc(b.sponsor) : '—')
            + row('Plan', esc(b.plan))
            + row('Status', `<span class="label label-inline label-light-${st} font-weight-bold">${esc(b.status)}</span>`)
            + (b.roi ? row('ROI status', esc(b.roi)) : '')
            + row(d.team.tree === 'saving' ? 'Level in your saving team' : 'Level in your team', b.level === 0 ? 'You' : (b.level ? 'Level ' + b.level : '—'));

        h += '<div class="oc-sec">Investment &amp; team</div><div class="oc-stats">'
            + `<div class="oc-stat"><b>${money(d.investment.standard)}</b><small>Standard investment</small></div>`
            + (d.investment.saving > 0 ? `<div class="oc-stat"><b>${money(d.investment.saving)}</b><small>Saving deposited</small></div>` : '')
            + `<div class="oc-stat"><b>${d.team.direct}</b><small>Direct members</small></div>`
            + `<div class="oc-stat"><b>${d.team.total}</b><small>Total team</small></div></div>`;

        const saving = d.team.tree === 'saving', maxHave = Math.max(1, ...d.team.levels.map(l => l.have));
        h += saving
            ? '<div class="oc-sec">Saving team per level</div>'
            : '<div class="oc-sec">Active members per level <span class="text-muted text-lowercase font-weight-normal">(for profit share)</span></div>';
        d.team.levels.forEach(l => {
            const ok = !saving && l.have >= l.need, pct = Math.min(100, l.have / (saving ? maxHave : l.need) * 100);
            h += `<div class="oc-lv ${ok ? 'ok' : ''}"><span class="lbl">Level ${l.level}</span><span class="bar"><i style="width:${pct}%"></i></span>`
               + `<span class="num">${saving ? l.have + ' member' + (l.have === 1 ? '' : 's') : l.have + ' / ' + l.need.toLocaleString()} ${ok ? '<i class="fas fa-check-circle text-success"></i>' : ''}</span></div>`;
        });

        h += '<div class="oc-sec">Earnings (total)</div>';
        Object.entries(d.earnings).forEach(([k, v]) => h += row(k, money(v)));

        if (d.contact) {
            h += '<div class="oc-sec">Contact <span class="text-muted text-lowercase font-weight-normal">(admin only)</span></div>'
               + row('Email', d.contact.email ? `<a href="mailto:${esc(d.contact.email)}">${esc(d.contact.email)}</a>` : '—')
               + row('Phone', d.contact.phone ? `<a href="tel:${esc(d.contact.phone)}">${esc(d.contact.phone)}</a>` : '—');
        }
        return h;
    }

    // start readable: fit if it stays legible, otherwise centre on you at a normal zoom
    render(); fit();
    if (view.k < 0.5) { view.k = 0.75; centerOn(root); }
    window.addEventListener('resize', () => clearTimeout(window.__ocR) || (window.__ocR = setTimeout(fit, 200)));
})();
</script>
