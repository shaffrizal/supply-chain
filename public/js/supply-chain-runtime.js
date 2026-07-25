(() => {
    'use strict';

    const status = document.getElementById('realtimeStatus');
    const updated = document.getElementById('realtimeUpdated');
    const refresh = document.getElementById('refreshRealtime');
    const toastStack = document.getElementById('toastStack');
    const number = new Intl.NumberFormat('id-ID');
    let refreshing = false;

    const toast = (message, tone = 'success') => {
        if (!toastStack) return;
        const item = document.createElement('div');
        item.className = `sc-runtime-toast ${tone}`;
        item.innerHTML = `<i class="fas ${tone === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i><span>${message}</span>`;
        toastStack.appendChild(item);
        requestAnimationFrame(() => item.classList.add('show'));
        setTimeout(() => {
            item.classList.remove('show');
            setTimeout(() => item.remove(), 250);
        }, 3000);
    };

    const applyLiveData = (data) => {
        document.querySelectorAll('[data-live-key]').forEach((element) => {
            const value = data[element.dataset.liveKey];
            if (value === undefined || value === null) return;
            const decimals = Number(element.dataset.liveDecimals || 0);
            element.textContent = decimals ? Number(value).toFixed(decimals) : number.format(value);
        });
        document.dispatchEvent(new CustomEvent('supplychain:updated', { detail: data }));
    };

    const sync = async (announce = false) => {
        if (!status || refreshing) return;
        refreshing = true;
        status.classList.add('syncing');
        try {
            const response = await fetch(`${status.dataset.endpoint}?_=${Date.now()}`, {
                headers: { Accept: 'application/json' },
                cache: 'no-store',
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            const payload = await response.json();
            applyLiveData(payload.data || {});
            status.classList.remove('offline');
            if (updated) updated.textContent = `Sinkron ${new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' })}`;
            if (announce) toast('Data terbaru berhasil dimuat');
        } catch {
            status.classList.add('offline');
            if (updated) updated.textContent = navigator.onLine ? 'Koneksi data terganggu' : 'Perangkat offline';
            if (announce) toast('Data belum dapat diperbarui', 'danger');
        } finally {
            refreshing = false;
            status.classList.remove('syncing');
        }
    };

    refresh?.addEventListener('click', () => sync(true));
    window.addEventListener('online', () => sync(true));
    window.addEventListener('offline', () => {
        status?.classList.add('offline');
        if (updated) updated.textContent = 'Perangkat offline';
    });
    sync();
    window.setInterval(() => document.visibilityState === 'visible' && sync(), 30000);

    document.querySelectorAll('form').forEach((form) => {
        form.addEventListener('submit', () => {
            const button = form.querySelector('button[type="submit"]');
            if (!button || button.dataset.noLoading !== undefined) return;
            button.classList.add('is-loading');
            button.setAttribute('aria-busy', 'true');
        });
    });

    const palette = document.getElementById('commandPalette');
    const trigger = document.getElementById('commandTrigger');
    const close = document.getElementById('commandClose');
    const search = document.getElementById('commandSearch');
    const results = document.getElementById('commandResults');
    const links = [...document.querySelectorAll('.sidebar-nav a.nav-link')].map((link) => ({
        label: link.querySelector('span')?.textContent.trim() || link.textContent.trim(),
        href: link.href,
        icon: link.querySelector('i')?.className || 'fas fa-arrow-right',
    }));
    let active = 0;

    const render = () => {
        if (!results) return;
        const query = (search?.value || '').toLowerCase().trim();
        const filtered = links.filter((item) => item.label.toLowerCase().includes(query)).slice(0, 10);
        active = Math.min(active, Math.max(0, filtered.length - 1));
        results.innerHTML = filtered.length
            ? filtered.map((item, index) => `<a href="${item.href}" class="${index === active ? 'active' : ''}"><i class="${item.icon}"></i><span>${item.label}</span><i class="fas fa-arrow-right"></i></a>`).join('')
            : '<div class="sc-command-empty">Fitur tidak ditemukan.</div>';
    };
    const openPalette = () => {
        if (!palette) return;
        palette.hidden = false;
        document.body.classList.add('command-open');
        active = 0;
        if (search) search.value = '';
        render();
        setTimeout(() => search?.focus(), 30);
    };
    const closePalette = () => {
        if (!palette) return;
        palette.hidden = true;
        document.body.classList.remove('command-open');
    };
    trigger?.addEventListener('click', openPalette);
    close?.addEventListener('click', closePalette);
    palette?.addEventListener('click', (event) => event.target === palette && closePalette());
    search?.addEventListener('input', () => { active = 0; render(); });
    document.addEventListener('keydown', (event) => {
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
            event.preventDefault();
            palette?.hidden ? openPalette() : closePalette();
        }
        if (palette?.hidden) return;
        if (event.key === 'Escape') closePalette();
        if (event.key === 'ArrowDown') { event.preventDefault(); active++; render(); }
        if (event.key === 'ArrowUp') { event.preventDefault(); active = Math.max(0, active - 1); render(); }
        if (event.key === 'Enter') {
            const selected = results?.querySelector('a.active');
            if (selected) { event.preventDefault(); window.location.href = selected.href; }
        }
    });
})();
