# 🎓 Colegio SaaS — Sistema de Gestión Escolar

Aplicación web SaaS para colegios construida con **Laravel 11 + MySQL**. Incluye login con roles, dashboard con gráficos y módulos completos de gestión académica, asistencia, calificaciones, horarios, finanzas y comunicación.

---

## ✅ Requisitos

- PHP **8.2** o superior (con extensiones `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`)
- [Composer](https://getcomposer.org/)
- MySQL 8 / MariaDB (XAMPP, Laragon o WAMP sirven perfectamente)

> El diseño usa Bootstrap 5, Bootstrap Icons y Chart.js desde CDN, así que **no necesitas Node ni npm**.

---

## 🚀 Instalación (Windows)

Abre una terminal (PowerShell o CMD) dentro de la carpeta del proyecto `C:\SAAS\saas_colegio` y ejecuta:

```bash
# 1. Instalar dependencias de PHP (incluye dompdf para los boletines/PDF)
composer install

# 2. Generar la llave de la aplicación
php artisan key:generate
```

> Si ya habías corrido `composer install` antes de esta versión, ejecuta `composer update barryvdh/laravel-dompdf` (o simplemente `composer update`) para instalar la librería de PDF que ahora usan los boletines y las exportaciones.

### 2. Crear la base de datos

Crea la base de datos vacía `saas_colegio`. Puedes usar phpMyAdmin, o por consola:

```sql
CREATE DATABASE saas_colegio CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

La conexión ya está configurada en `.env` (host `127.0.0.1`, usuario `root`, contraseña vacía, puerto `3306`). Si tu MySQL tiene contraseña, edítala en `.env` → `DB_PASSWORD`.

### 3. Migrar y cargar datos de ejemplo

```bash
php artisan migrate --seed
```

Esto crea todas las tablas y carga datos demo (docentes, cursos, materias, ~60 estudiantes, pagos, notas, asistencia y comunicados).

### 4. Enlazar el almacenamiento (para fotos y logo)

```bash
php artisan storage:link
```

Crea el enlace `public/storage` necesario para mostrar el logo del colegio, las fotos de estudiantes y los avatares de usuario.

### 5. Levantar el servidor

```bash
php artisan serve
```

Abre el navegador en **http://localhost:8000**

---

## 🔑 Accesos de prueba

| Rol | Correo | Contraseña |
|-----|--------|-----------|
| **Super-admin (plataforma)** | `super@saas.test` | `password` |
| Administrador (Colegio San Martín) | `admin@colegio.test` | `password` |
| Docente | `docente@colegio.test` | `password` |
| Secretaría | `secretaria@colegio.test` | `password` |
| Estudiante / Padre | `estudiante@colegio.test` | `password` |
| Administrador (Colegio Andino) | `admin@andino.test` | `password` |

### Multi-colegio (multi-tenant)

El sistema es **multi-inquilino**: cada colegio tiene sus datos completamente aislados (filtrado automático por `school_id`).

- **Super-administrador** (`super@saas.test`): gestiona la plataforma — lista de colegios, suspender/activar, cambiar de plan, estadísticas globales.
- **Registro de colegios**: cualquiera puede crear un colegio nuevo desde la landing (botón "Prueba gratis" → `/registro`); se crea el colegio, su administrador y su configuración, y se inicia sesión automáticamente.
- Los datos demo incluyen **dos colegios** (San Martín y Andino) para que verifiques el aislamiento: al entrar con `admin@andino.test` solo verás los datos de ese colegio.

Cada rol ve un **panel y un menú distintos**: el Docente ve sus cursos, materias y horario; el Estudiante ve sus calificaciones, asistencia y pagos.

---

## 📦 Módulos incluidos

**Académico:** Estudiantes (con **importación CSV** y documentos PDF), Docentes, Cursos/Grados (con **carga académica**), Materias, Matrículas
**Gestión diaria:** Asistencia (registro diario + **reporte mensual** PDF), Calificaciones (registro individual y **planilla masiva por curso**), **Tareas/Asignaciones**, Horarios, **Disciplina** (méritos/deméritos), **Calendario** de eventos
**Finanzas:** Pagos y Pensiones (cobranzas, estados, facturas, **estado de cuenta** PDF, **recibo individual** PDF, **generación automática de pensiones** por curso, **reporte de morosos**)
**Notificaciones:** centro de avisos in-app (campana) cuando se publican notas, cargos o comunicados
**Auditoría:** bitácora de quién creó/modificó/eliminó datos clave (solo administrador)
**Biblioteca:** Catálogo de libros y control de **préstamos/devoluciones** con stock
**Comunicación:** Comunicados / Circulares (con **envío por correo** según audiencia), **Mensajería interna** entre usuarios
**Administración:** Reportes, Usuarios y Roles, **Configuración editable** (gestión y período académico)

**Documentos PDF:** boletín de calificaciones, constancia de matrícula, carnet/credencial, estado de cuenta y reporte de asistencia mensual.

**Exportaciones:** listados de estudiantes y pagos a CSV (Excel) y PDF.

### Envío de correos

Por defecto `MAIL_MAILER=log`, así que los comunicados enviados por correo se escriben en `storage/logs/laravel.log` (ideal para probar sin configurar nada). Para enviar correos reales, configura las variables `MAIL_*` en `.env` con tu servidor SMTP.

Cada módulo tiene su pantalla en el **menú vertical** y el **Dashboard** muestra indicadores y gráficos en tiempo real.

---

## 🎨 Diseño

Panel de administración profesional con sidebar oscuro, topbar verde y tarjetas de estadísticas, inspirado en el mockup proporcionado. Estilos en `public/assets/css/app.css` (variables CSS fáciles de personalizar: colores de marca, sidebar, etc.).

---

## 🗂️ Estructura principal

```
app/
  Http/Controllers/   → lógica de cada módulo
  Http/Middleware/    → EnsureUserHasRole (control por rol)
  Models/             → Eloquent (Student, Teacher, Course, ...)
database/
  migrations/         → esquema completo
  seeders/            → datos demo
resources/views/      → vistas Blade (layout, login, dashboard, módulos)
routes/web.php        → rutas
public/assets/css/    → tema visual
```

---

## 🛠️ Comandos útiles

```bash
php artisan migrate:fresh --seed   # Reiniciar la BD con datos demo
php artisan optimize:clear         # Limpiar cachés
```

---

## 📝 Notas

- Roles soportados: `admin`, `docente`, `secretaria`, `estudiante`. Las rutas de **Usuarios** y **Configuración** son solo para `admin`.
- Para producción, configura `APP_ENV=production`, `APP_DEBUG=false` y un `DB_PASSWORD` seguro.
- La moneda mostrada es **Bs (Bolivianos)**; cámbiala en las vistas si lo necesitas.
