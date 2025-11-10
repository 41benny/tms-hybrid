import "./bootstrap";

// Theme toggle handler
const themeKey = "tms-theme";

// Wait for DOM to be ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initThemeToggle);
} else {
  // DOM already loaded
  initThemeToggle();
}

function initThemeToggle() {
  const btn = document.getElementById("theme-toggle");
  if (!btn) {
    console.warn('Theme toggle button not found');
    return;
  }

  console.log('Theme toggle initialized');
  
  btn.addEventListener("click", () => {
    const rootEl = document.documentElement;
    const isDark = rootEl.classList.toggle("dark");
    localStorage.setItem(themeKey, isDark ? "dark" : "light");
    console.log('Theme toggled to:', isDark ? 'dark' : 'light');
  });
}
