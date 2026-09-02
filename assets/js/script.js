document.addEventListener('DOMContentLoaded', () => {
  const navbar = document.querySelector('#mainNav');
  const updateNavbar = () => navbar?.classList.toggle('scrolled', window.scrollY > 30);
  updateNavbar();
  window.addEventListener('scroll', updateNavbar, { passive: true });
  if (window.AOS) AOS.init({ duration: 700, once: true, offset: 70 });
  document.querySelectorAll('[data-quantity]').forEach((button) => button.addEventListener('click', () => {
    const input = document.querySelector('#quantity');
    if (!input) return;
    const current = Number(input.value || 1);
    input.value = button.dataset.quantity === 'plus' ? current + 1 : Math.max(1, current - 1);
  }));
  const productGrid = document.querySelector('#productGrid');
  if (productGrid) {
    const columns = [...productGrid.querySelectorAll('.product-column')];
    let activeFilter = 'all';
    const search = document.querySelector('#productSearch');
    const sort = document.querySelector('#sortProducts');
    const render = () => {
      const query = (search?.value || '').trim().toLowerCase();
      const visible = columns.filter((column) => {
        const matchesFilter = activeFilter === 'all' || column.dataset.category === activeFilter;
        const matchesSearch = !query || column.dataset.name.includes(query);
        column.hidden = !(matchesFilter && matchesSearch);
        return matchesFilter && matchesSearch;
      });
      if (sort?.value !== 'default') {
        const direction = sort.value === 'asc' ? 1 : -1;
        visible.sort((a, b) => (Number(a.dataset.price) - Number(b.dataset.price)) * direction).forEach((item) => productGrid.appendChild(item));
      }
    };
    document.querySelectorAll('.filter-btn').forEach((button) => button.addEventListener('click', () => {
      document.querySelector('.filter-btn.active')?.classList.remove('active');
      button.classList.add('active'); activeFilter = button.dataset.filter; render();
    }));
    search?.addEventListener('input', render); sort?.addEventListener('change', render);
  }
});

const adminLoginPath = window.location.pathname.indexOf('/client/') !== -1 ? '../admin/login.php' : 'admin/login.php';
document.addEventListener('keydown', function (e) {
  if (e.ctrlKey && e.altKey && (e.key === 'a' || e.key === 'A')) {
    e.preventDefault();
    window.location.href = adminLoginPath;
  }
});

  // PARTIE 5: Feedback immédiat sur les boutons 'Ajouter au panier'
  document.querySelectorAll('form[action="actions/add_to_cart.php"]').forEach((form) => {
    form.addEventListener('submit', (e) => {
      const button = form.querySelector('button[type="submit"]');
      if (!button) return;
      
      // Store original content
      const originalHTML = button.innerHTML;
      const originalClass = button.className;
      
      // Change button appearance immediately
      button.innerHTML = '<i class="fa-solid fa-check"></i> Ajout...';
      button.classList.add('btn-submitting');
      button.disabled = true;
      
      // If page doesn't reload after 3 seconds, restore button
      // (in case there's an error or validation issue)
      const timeout = setTimeout(() => {
        button.innerHTML = originalHTML;
        button.className = originalClass;
        button.disabled = false;
      }, 3000);
      
      // Allow natural form submission
    });
  });

  // PARTIE 6: Animated counters in stats-band
  const animateCounters = () => {
    const statItems = document.querySelectorAll('.stat-item strong');
    if (statItems.length === 0) return;
    
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        
        const strong = entry.target;
        const text = strong.textContent.trim();
        
        // Parse the number and any suffix (like +, %)
        const match = text.match(/^(\d+)(.*)/);
        if (!match) return;
        
        const finalValue = parseInt(match[1], 10);
        const suffix = match[2];
        
        // Skip if already animated
        if (strong.dataset.animated === 'true') return;
        strong.dataset.animated = 'true';
        
        // Animation duration
        const duration = 1500; // 1.5 seconds
        const steps = 30;
        const increment = finalValue / steps;
        let current = 0;
        const startTime = Date.now();
        
        const animate = () => {
          const elapsed = Date.now() - startTime;
          const progress = Math.min(elapsed / duration, 1);
          current = Math.floor(progress * finalValue);
          strong.textContent = current + suffix;
          
          if (progress < 1) {
            requestAnimationFrame(animate);
          } else {
            strong.textContent = finalValue + suffix;
          }
        };
        
        animate();
        observer.unobserve(strong);
      });
    }, { threshold: 0.5 });
    
    statItems.forEach((item) => observer.observe(item));
  };
  
  animateCounters();

  // PARTIE 7: Hide splash screen reliably
  const hideSplash = () => {
    const splash = document.getElementById('splashScreen');
    if (splash) {
      setTimeout(() => {
        splash.classList.add('hidden');
      }, 800);
    }
  };

  // Use both DOM and window load to ensure the overlay disappears
  const onPageReady = () => {
    requestAnimationFrame(() => {
      hideSplash();
    });
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', onPageReady, { once: true });
  } else {
    onPageReady();
  }
