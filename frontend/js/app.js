// Minimal hash-based SPA router (Milestone 1: static, no backend calls).
// Each route maps to an HTML template in /views, loaded with fetch and
// injected into #app. No full page reloads happen during navigation.

const ROUTES = [
  'login', 'register', 'dashboard', 'classes', 'my-bookings', 'trainers',
  'plans', 'exercises', 'profile',
  'admin/users', 'admin/memberships', 'admin/classes',
];
const DEFAULT_ROUTE = 'dashboard';

// Hooks that run after a view is inserted (e.g. to draw charts).
const viewHooks = {
  dashboard: initDashboardChart,
};

const appEl = document.getElementById('app');

function currentRoute() {
  const hash = window.location.hash.replace(/^#\/?/, '');
  return hash || DEFAULT_ROUTE;
}

function setActiveLink(route) {
  document.querySelectorAll('[data-route]').forEach((a) => {
    a.classList.toggle('active', a.dataset.route === route);
  });
}

async function loadView(route) {
  const name = ROUTES.includes(route) ? route : '404';
  try {
    const res = await fetch(`views/${name}.html`);
    if (!res.ok) throw new Error(`View not found: ${name}`);
    appEl.innerHTML = await res.text();
    if (viewHooks[name]) viewHooks[name]();
  } catch (err) {
    appEl.innerHTML = '<div class="alert alert-danger">Could not load this page.</div>';
    console.error(err);
  }
  setActiveLink(route);
  window.scrollTo(0, 0);
  // close the mobile menu after navigating
  const nav = document.getElementById('mainNav');
  if (nav && nav.classList.contains('show')) {
    bootstrap.Collapse.getOrCreateInstance(nav).hide();
  }
}

function router() {
  loadView(currentRoute());
}

// Static placeholder chart; real data comes from the API in Milestone 4.
function initDashboardChart() {
  const el = document.getElementById('bookings-chart');
  if (!el || typeof Highcharts === 'undefined') return;
  Highcharts.chart(el, {
    chart: { type: 'column' },
    title: { text: null },
    xAxis: { categories: ['May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct'] },
    yAxis: { title: { text: 'Bookings' } },
    legend: { enabled: false },
    credits: { enabled: false },
    series: [{ name: 'Bookings', data: [120, 150, 135, 170, 210, 240], color: '#ffb400' }],
  });
}

window.addEventListener('hashchange', router);
window.addEventListener('DOMContentLoaded', router);
