# 🔍 Analizador de Red PRO - Inteligencia General

Herramienta de diagnóstico modular diseñada para servidores Ubuntu 20 con Plesk.

## 🚀 Funcionalidades
- **Captura Web**: Visualización dinámica vía Thum.io.
- **DNS**: Tabla estructurada con badges de colores para registros A, MX, NS, TXT.
- **Red**: Pruebas de Ping y resolución global vía Google DNS (8.8.8.8).
- **Identidad**: Consulta de WHOIS (con excepciones para .es).

## 📁 Estructura del Proyecto
- `/index.php` - Interfaz y orquestador.
- `/functions.php` - Funciones de ejecución (shell_exec).
- `/style.css` - Diseño, animaciones y rueda ⚙️.
- `/modules/` - Lógica técnica separada.

## 🛠️ Instalación en Plesk
1. Clonar este repo en `/httpdocs/app`.
2. Asegurar que `shell_exec` y `allow_url_fopen` estén habilitados en la configuración de PHP.
3. Git safe directory: `git config --global --add safe.directory /var/www/vhosts/inteligenciageneral.com/httpdocs/app`.