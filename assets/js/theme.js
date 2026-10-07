/**
 * assets/js/theme.js
 * Light/Dark theme switcher with localStorage persistence
 */

(function() {
    'use strict';

    const THEME_KEY = 'cshub-theme';
    const THEME_LIGHT = 'light';
    const THEME_DARK = 'dark';

    // Get theme toggle elements
    const themeToggle = document.getElementById('themeToggle');
    const html = document.documentElement;

    /**
     * Get current theme from localStorage or system preference
     */
    function getCurrentTheme() {
        // Check localStorage first
        const savedTheme = localStorage.getItem(THEME_KEY);
        if (savedTheme) {
            return savedTheme;
        }

        // Check system preference
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            return THEME_DARK;
        }

        // Default to light
        return THEME_LIGHT;
    }

    /**
     * Apply theme to the document
     */
    function applyTheme(theme) {
        html.setAttribute('data-theme', theme);
        html.setAttribute('data-bs-theme', theme);
        updateToggleIcon(theme);
        localStorage.setItem(THEME_KEY, theme);

        // Dispatch custom event for other scripts
        window.dispatchEvent(new CustomEvent('themeChanged', { detail: { theme } }));
    }

    /**
     * Update the theme toggle button icon
     */
    function updateToggleIcon(theme) {
        if (!themeToggle) return;

        const icon = themeToggle.querySelector('.theme-icon');
        if (icon) {
            icon.textContent = theme === THEME_DARK ? '☀️' : '🌙';
        }

        // Update aria-label for accessibility
        themeToggle.setAttribute('aria-label',
            theme === THEME_DARK ? 'Switch to light mode' : 'Switch to dark mode'
        );
    }

    /**
     * Toggle between light and dark theme
     */
    function toggleTheme() {
        const currentTheme = html.getAttribute('data-theme');
        const newTheme = currentTheme === THEME_DARK ? THEME_LIGHT : THEME_DARK;
        applyTheme(newTheme);

        // Add a little animation feedback
        if (themeToggle) {
            themeToggle.style.transform = 'rotate(360deg)';
            setTimeout(() => {
                themeToggle.style.transform = '';
            }, 300);
        }
    }

    /**
     * Initialize theme on page load
     */
    function initTheme() {
        const currentTheme = getCurrentTheme();
        applyTheme(currentTheme);

        // Add event listener to toggle button
        if (themeToggle) {
            themeToggle.addEventListener('click', toggleTheme);
        }

        // Listen for system theme changes
        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                // Only auto-switch if user hasn't manually set a preference
                if (!localStorage.getItem(THEME_KEY)) {
                    applyTheme(e.matches ? THEME_DARK : THEME_LIGHT);
                }
            });
        }
    }

    // Initialize immediately to prevent FOUC
    initTheme();

    // Re-initialize when DOM is fully loaded (in case elements weren't ready)
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTheme);
    }

    // Expose theme functions globally for other scripts
    window.CSHubTheme = {
        getCurrentTheme: () => html.getAttribute('data-theme'),
        setTheme: applyTheme,
        toggleTheme: toggleTheme
    };

})();
