# TuCardex - Plataforma Integral de Control Escolar y Secretaría SaaS

**TuCardex** es un sistema integral de gestión y control escolar diseñado para instituciones educativas de nivel básico, medio superior y superior. Integra la administración académica, calificaciones, kardex, asistencias, colegiaturas y un módulo especializado de **Secretaría y Control Escolar**.

---

## 🚀 Arquitectura y Tecnologías

- **Backend:** Laravel 11 (PHP 8.2 FPM)
- **Base de Datos:** MariaDB 10.11 / MySQL
- **Servidor Web:** Nginx Alpine
- **Proxy Inverso:** Traefik con SSL automático (Let's Encrypt)
- **Almacenamiento en la Nube:** Cloudflare R2 (S3 compatible)
- **Correos Transaccionales:** Resend SMTP (TLS 587)
- **Motor de PDFs:** DomPDF & Barcode/QR Code Generator (Endroid QR)
- **Contenedores:** Docker & Docker Compose

---

## 🏛 Módulos Principales

### 1. Secretaría y Control Escolar (`/secretaria`)
- **Generador de Credenciales Escolares:**
  - Emisión individual en formato estándar PVC (85.6 mm × 54 mm) con anverso y reverso para corte y laminado.
  - Generación por lote de grupos completos (8 credenciales por hoja Carta).
  - Códigos QR institucionales y fotografía del alumno.
- **Constancias Oficiales:**
  - Constancia de Estudios (con opción de motivos personalizados y validación QR).
  - Carta de Buena Conducta.
  - Constancia de No Adeudo Financiero (conciliada automáticamente en tiempo real con la base de datos de pagos).
  - Kárdex Académico Oficial.
  - Ficha de Matrícula.
- **Oficios, Citatorios y Justificantes con Despacho de Correo:**
  - Redacción rápida con folios oficiales únicos.
  - Descarga inmediata en PDF membretado.
  - Envío por correo electrónico con el PDF oficial adjunto a padres de familia individuales o masivo a grupos completos.
  - Captura y actualización automática de correos y usuarios en el expediente del alumno.

### 2. Gestión Académica
- Control de Alumnos, Profesores, Grados, Grupos y Materias.
- Captura de calificaciones y cálculo automático de promedios.
- Control de asistencias diarias y reportes de inasistencias.

### 3. Pagos y Finanzas
- Control de colegiaturas, inscripciones y conceptos varios.
- Conciliación de adeudos y estados de cuenta en tiempo real.

---

## 📦 Despliegue con Docker

### 1. Clonar el repositorio
```bash
git clone https://github.com/brucefernando-design/tucardex.git
cd tucardex
```

### 2. Configurar el archivo de entorno
```bash
cp app/.env.example app/.env
# Editar app/.env con las credenciales correspondientes
```

### 3. Iniciar los contenedores
```bash
docker compose up -d --build
```

### 4. Inicializar Laravel
```bash
docker exec -it tucardex-app php artisan key:generate
docker exec -it tucardex-app php artisan migrate --force
docker exec -it tucardex-app php artisan storage:link
docker exec -it tucardex-app php artisan config:clear
docker exec -it tucardex-app php artisan cache:clear
```

---

## 🔒 Seguridad y Privacidad
- La configuración de variables de entorno (`.env`), contraseñas y respaldos de bases de datos se encuentran ignorados por `.gitignore` para proteger la privacidad institucional.
