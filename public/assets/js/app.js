// Bascule entre thème sombre et thème clair, mémorisée dans le navigateur.
(function () {
    const root = document.documentElement;
    const button = document.getElementById('theme-toggle');

    function apply(theme) {
        root.dataset.bsTheme = theme;
        try {
            localStorage.setItem('followup-theme', theme);
        } catch (e) {
            // Stockage local indisponible : le thème reste appliqué pour la page.
        }
        if (button) {
            const label = theme === 'dark' ? 'Passer au thème clair' : 'Passer au thème sombre';
            button.setAttribute('aria-label', label);
            button.setAttribute('title', label);
        }
    }

    const current = root.dataset.bsTheme === 'light' ? 'light' : 'dark';
    apply(current);

    if (button) {
        button.addEventListener('click', function () {
            apply(root.dataset.bsTheme === 'dark' ? 'light' : 'dark');
        });
    }
})();
