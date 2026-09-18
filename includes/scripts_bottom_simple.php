<script>
    // Aplica el tema guardado (mismo mecanismo que assets/app.js) sin cargar
    // toda la lógica de la app, que estas páginas simples no necesitan.
    try {
        if (localStorage.getItem('darkMode') === '1') document.body.classList.add('dark-mode');
        const theme = localStorage.getItem('cuakcom-theme');
        if (theme && theme !== 'default') document.documentElement.classList.add('theme-' + theme);
    } catch (_) {}
</script>
</body>
</html>
