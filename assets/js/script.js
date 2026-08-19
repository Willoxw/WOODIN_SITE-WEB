// WOODIN Cameroun — Scripts globaux
(function(){
  const $ = (sel, scope=document) => scope.querySelector(sel);
  const $$ = (sel, scope=document) => Array.from(scope.querySelectorAll(sel));

  document.addEventListener('DOMContentLoaded', () => {
    // Navbar color change on scroll
    const navbar = $('#mainNavbar');
    const onScroll = () => {
      if(!navbar) return;
      if(window.scrollY > 10){ navbar.classList.add('navbar-scrolled'); }
      else { navbar.classList.remove('navbar-scrolled'); }
    };
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });

    // WhatsApp floating button (appears after 3s)
    const wa = $('#whatsappFloat');
    if(wa){ setTimeout(() => wa.classList.add('visible'), 3000); }

    // Animated counters (home stats)
    const counters = $$('.stat-number');
    if(counters.length){
      const easeOut = t => 1 - Math.pow(1 - t, 3);
      const animate = (el, target, duration=1200) => {
        let start = null;
        const step = ts => {
          if(!start) start = ts;
          const p = Math.min((ts - start) / duration, 1);
          const val = Math.floor(easeOut(p) * target);
          el.textContent = val.toString();
          if(p < 1) requestAnimationFrame(step); else el.textContent = target.toString();
        };
        requestAnimationFrame(step);
      };
      const io = new IntersectionObserver((entries, obs) => {
        entries.forEach(entry => {
          if(entry.isIntersecting){
            const el = entry.target;
            const target = parseInt(el.getAttribute('data-target') || '0', 10);
            animate(el, target);
            obs.unobserve(el);
          }
        });
      }, { threshold: .6 });
      counters.forEach(c => io.observe(c));
    }

    // Catalogue: filter + sort + search
    const productsContainer = $('#productsContainer');
    if(productsContainer){
      let currentFilter = 'all';
      const cards = $$('#productsContainer > div');
      const btns = $$('.btn-filter');
      const searchInput = $('#searchInput');
      const sortSelect = $('#sortSelect');

      const applyFilters = () => {
        const query = (searchInput?.value || '').toLowerCase().trim();
        cards.forEach(card => {
          const name = (card.getAttribute('data-name') || '').toLowerCase();
          const cat = (card.getAttribute('data-category') || '');
          const matchCat = currentFilter === 'all' || cat === currentFilter;
          const matchSearch = !query || name.includes(query);
          if(matchCat && matchSearch){ card.classList.remove('d-none'); }
          else { card.classList.add('d-none'); }
        });
      };

      const applySort = () => {
        const dir = sortSelect?.value || 'asc';
        const visible = cards.slice().filter(el => !el.classList.contains('d-none'));
        visible.sort((a,b) => {
          const pa = parseInt(a.getAttribute('data-price')||'0',10);
          const pb = parseInt(b.getAttribute('data-price')||'0',10);
          return dir === 'asc' ? pa - pb : pb - pa;
        });
        visible.forEach(el => productsContainer.appendChild(el));
      };

      btns.forEach(btn => btn.addEventListener('click', () => {
        btns.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentFilter = btn.getAttribute('data-filter') || 'all';
        applyFilters();
        applySort();
      }));

      searchInput?.addEventListener('input', () => { applyFilters(); applySort(); });
      sortSelect?.addEventListener('change', applySort);

      // Initial
      applyFilters();
      applySort();
    }

    // Product quantity selector
    const qtyMinus = $('#qtyMinus');
    const qtyPlus = $('#qtyPlus');
    const qtyInput = $('#qtyInput');
    if(qtyInput){
      const clamp = v => Math.max(1, Math.min(99, v));
      qtyMinus?.addEventListener('click', () => { const v = clamp(parseInt(qtyInput.value||'1',10)-1); qtyInput.value = v; });
      qtyPlus?.addEventListener('click', () => { const v = clamp(parseInt(qtyInput.value||'1',10)+1); qtyInput.value = v; });
      qtyInput.addEventListener('input', () => { const n = parseInt(qtyInput.value||'1',10); qtyInput.value = isNaN(n)?1:clamp(n); });
    }

    // Timeline interactivity
    const timeline = $('#historyTimeline');
    if(timeline){
      const items = $$('.timeline-item', timeline);
      items.forEach(it => it.addEventListener('click', () => {
        items.forEach(i => i.classList.remove('active'));
        it.classList.add('active');
        it.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
      }));
    }

    // Close mobile menu when clicking a link
    const navLinks = $$('.navbar .nav-link');
    const collapseEl = $('#navbarSupportedContent');
    navLinks.forEach(l => l.addEventListener('click', () => {
      if(getComputedStyle($('.navbar-toggler')).display !== 'none' && collapseEl?.classList.contains('show')){
        const bs = bootstrap.Collapse.getOrCreateInstance(collapseEl, { toggle: false });
        bs.hide();
      }
    }));
  });
})();
