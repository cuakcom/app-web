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

## Login y Roadmap

- `/data/app.db` (no versionado) — SQLite con `users`, `allowed_emails`, `roadmap_items`, `roadmap_imports`. Se crea solo la primera vez que algo llama a `db()` (`includes/db.php`).
- Registro (`register.php`) solo permite emails que coincidan con `allowed_emails` (exactos o `*@dominio`). Whitelist inicial: `jfernandez@arsys.es` (admin), `*@arsys.es`, `*@cuak.com`.
- `login.php` / `logout.php` — sesión nativa de PHP. `includes/auth.php` tiene los helpers (`current_user()`, `require_login()`, `require_admin()`, `email_allowed()`, CSRF).
- `admin.php` (solo admins) — gestiona la whitelist y aprueba/rechaza/marca en progreso las propuestas del Roadmap.
- El Roadmap público (listado + formulario para proponer, solo si hay sesión) vive en el pie de página de `index.php` (`includes/footer_roadmap.php` → `roadmap_propose.php`).

## Smart Check (Herramientas → Gemini)

- `smart_check.php` — recibe correo/dominio/IP + tipo de comprobación + pregunta libre, ejecuta en paralelo (vía `run_modules()` en `functions.php`, llamando a `api.php` internamente) los módulos reales que correspondan, y le pasa esos datos ya verificados a Gemini (`includes/gemini.php`) solo para que los resuma — nunca para que invente.
- `includes/smart_check_types.php` — catálogo de tipos de comprobación (spam, reputación IP, SSL, caducidad...), qué módulos dispara cada uno y a qué apartados enlaza. Único archivo a tocar para añadir una opción nueva al desplegable.
- Los enlaces "ver en detalle" (`index.php?tab=<seccion>&q=<consulta>`) los resuelve `assets/app.js` al cargar la página: activa la pestaña y rellena el buscador, sin que la IA tenga que generar URLs.
- Sin `GEMINI_API_KEY` configurada, el resto sigue funcionando (se ven los datos crudos y un aviso en vez de resumen).

## Instalación en Plesk

1. Clonar este repo en `/httpdocs/app`.
2. Copiar `config.example.php` como `config.php` y rellenar las claves (`ABUSEIPDB_KEY`, `GEMINI_API_KEY`, `DEPLOY_SECRET`).
3. Asegurar que `shell_exec`, `allow_url_fopen` y la extensión `pdo_sqlite` estén habilitados en la configuración de PHP.
4. Que el proceso PHP tenga permiso de escritura sobre `/data` (para crear `app.db`).
5. Git safe directory: `git config --global --add safe.directory /var/www/vhosts/inteligenciageneral.com/httpdocs/app`.
6. Configurar el webhook de GitHub (Settings → Webhooks) apuntando a `https://<dominio>/app/deploy.php`, con el mismo secreto que `DEPLOY_SECRET`, para auto-deploy en cada push a `main`.
