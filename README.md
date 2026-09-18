# Check Berry

Suite modular de soporte técnico: diagnóstico DNS/SSL/correo/web, redes, y utilidades para clientes no avanzados. Diseñada para servidores Ubuntu con Plesk.

## Secciones

Diagnóstico · Web · Correo · DNS · Redes · Utilidades · Herramientas · Desarrollo. Cada sección top-level vive en su propio archivo bajo `/sections`, y se registra en `menu.php` — el único archivo que hay que tocar para añadir un apartado principal nuevo.

## Estructura del proyecto

- `/index.php` — bootstrap: datos de visitante + incluye `menu.php`, `includes/head.php`, `includes/header.php`, `includes/footer_visitor.php`, `includes/scripts_bottom.php`.
- `/menu.php` — árbol de navegación (secciones, iconos, ids, archivo asociado).
- `/includes/` — cabecera HTML, header con logo y pestañas, footer del visitante, scripts finales.
- `/sections/` — un PHP por apartado principal (Diagnóstico, Web, Correo, DNS, Redes, Utilidades, Herramientas, Desarrollo).
- `/assets/app.js` — lógica de frontend (peticiones a `api.php`, exportación, temas, drag&drop de tarjetas, footer del visitante).
- `/api.php` — dispatcher de peticiones AJAX, delega en `/modules/<módulo>.php`.
- `/modules/` — lógica de cada herramienta (DNS, WHOIS, SSL, correo, geolocalización...).
- `/functions.php` — funciones auxiliares compartidas.
- `/py/` — scripts Python invocados vía `shell_exec` desde PHP para utilidades que lo requieran.
- `/config.php` (no versionado, ver `config.example.php`) — claves API y secretos.

## Instalación en Plesk

1. Clonar este repo en `/httpdocs/app`.
2. Copiar `config.example.php` como `config.php` y rellenar las claves (`ABUSEIPDB_KEY`, `GEMINI_API_KEY`, `DEPLOY_SECRET`).
3. Asegurar que `shell_exec` y `allow_url_fopen` estén habilitados en la configuración de PHP.
4. Git safe directory: `git config --global --add safe.directory /var/www/vhosts/inteligenciageneral.com/httpdocs/app`.
5. Configurar el webhook de GitHub (Settings → Webhooks) apuntando a `https://<dominio>/app/deploy.php`, con el mismo secreto que `DEPLOY_SECRET`, para auto-deploy en cada push a `main`.
