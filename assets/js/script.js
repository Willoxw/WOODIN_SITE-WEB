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
