# Facturación Electrónica (SUNAT · Perú)

Módulo de emisión de comprobantes electrónicos (boletas, facturas y notas de
crédito) ante SUNAT usando UBL 2.1. Emisión directa mediante la librería
**Greenter**, con arquitectura de *drivers* para poder operar sin conexión.

## 1. Instalar dependencias

En la raíz del proyecto (`C:\SAAS\saas_colegio`):

```bash
composer require greenter/greenter
composer require endroid/qr-code   # QR de la representación impresa (PDF)
php artisan migrate
```

> `endroid/qr-code` es opcional: si no está instalado, el PDF se genera igual
> pero sin la imagen del QR. Requiere la extensión GD de PHP (activa por
> defecto en XAMPP).

La migración crea dos tablas: `electronic_billing_settings` (configuración por
colegio) y `electronic_invoices` (comprobantes emitidos).

## 2. Certificado digital

Coloca el certificado en formato **.pem** (certificado + llave privada) en una
ruta accesible, por ejemplo:

```
C:\SAAS\saas_colegio\storage\facturacion\pe\certificate.pem
```

- En **beta / homologación** puedes usar el certificado de pruebas de Greenter
  y las credenciales: RUC `20000000001`, usuario `MODDATOS`, clave `MODDATOS`.
- Si tienes un `.pfx`, conviértelo a `.pem`:
  `openssl pkcs12 -in certificado.pfx -out certificate.pem -nodes`

## 3. Configurar en el sistema

Menú lateral → **Finanzas → Config. Facturación** (solo Administrador):

1. Marca *Habilitar facturación electrónica*.
2. Driver de emisión: **Greenter**. Entorno: **Beta** (para pruebas).
3. Completa *Datos del emisor* (RUC, razón social, dirección, ubigeo…).
4. Ingresa *Usuario Clave SOL*, *Clave SOL* y la *ruta del certificado .pem*.
5. Define las series (F001 facturas, B001 boletas) y el IGV (18%).
6. Guarda y pulsa **Probar conexión con SUNAT**.

Las credenciales sensibles (Clave SOL, clave del certificado) se guardan
**encriptadas** en la base de datos.

## 4. Emitir comprobantes

- **Automático**: si activas *Emitir automáticamente al registrar el pago*,
  cada pago que se marque como *pagado* genera y envía una boleta.
- **Manual**: en **Finanzas → Pagos/Pensiones**, botón de comprobante de cada
  fila → *Boleta* o *Factura*.
- Los comprobantes, su estado SUNAT, XML firmado, CDR y la **representación
  impresa en PDF** (con QR y hash) se consultan y descargan en
  **Finanzas → Facturación Electrónica**.

## 5. Notas de crédito (anulación / devolución)

Desde **Finanzas → Facturación Electrónica**, abre el detalle de una factura o
boleta **aceptada** y pulsa *Nota de crédito*. Elige el motivo (catálogo SUNAT
09). Se emite con serie `FC01` (facturas) o `BC01` (boletas) referenciando el
comprobante afectado; si el motivo es anulación o devolución total, el original
queda **anulado** automáticamente al aceptarse la nota.

## 6. Resumen Diario de boletas (producción)

En producción las boletas se informan agrupadas por día:

1. En *Configuración* activa **Boletas por Resumen Diario**. Las boletas dejarán
   de enviarse una por una y quedarán *pendientes*.
2. Ve a **Finanzas → Resúmenes Diarios**. Verás las boletas pendientes agrupadas
   por fecha; pulsa *Enviar resumen* (o elige una fecha manual). SUNAT devuelve
   un **ticket**.
3. Pulsa *Consultar* para obtener el CDR. Al aceptarse, las boletas del resumen
   pasan a *aceptado*.

## 7. Automatización (cron)

Dos comandos automatizan el flujo de boletas por resumen en todos los colegios:

```bash
php artisan facturacion:resumen-diario [YYYY-MM-DD]   # genera/envía el resumen (por defecto: ayer)
php artisan facturacion:consultar-tickets             # consulta tickets pendientes y trae el CDR
```

Ya están programados en `routes/console.php` (resumen a la 01:00; consulta de
tickets cada 30 min). Para que se ejecuten, agrega **una** línea al cron del
servidor:

```
* * * * * php /ruta/al/proyecto/artisan schedule:run >> /dev/null 2>&1
```

En Windows (XAMPP) puedes crear una Tarea Programada que ejecute
`php artisan schedule:run` cada minuto.

## 8. Pruebas automatizadas

Se incluye una batería de pruebas (PHPUnit) que valida la lógica sin contactar
a SUNAT, usando un **driver simulado** (`tests/Support/FakeSunatDriver.php`) y
SQLite en memoria:

```bash
php artisan test
# o un archivo puntual:
php artisan test tests/Feature/EmisionComprobanteTest.php
```

Cubren: cálculo de IGV desde el total, numeración correlativa por serie,
selección de DNI/RUC, notas de crédito (con y sin anulación del original),
modo "boletas por resumen" y el flujo de resumen diario + consulta de ticket,
además del importe en letras. No requieren tener Greenter instalado.

## Arquitectura

```
app/Services/Facturacion/
├── SunatBilling.php            Orquestador (emite, NC, resumen diario)
├── BillingResult.php           DTO de respuesta unificada
├── Contracts/BillingDriver.php Interfaz de driver
├── Drivers/
│   ├── NullDriver.php          "Ninguno": deja el comprobante pendiente
│   └── GreenterDriver.php      Emisión real (facturas, boletas, NC y resúmenes)
└── Support/
    ├── NumberToWords.php       Importe en letras (leyenda 1000)
    └── QrGenerator.php         QR SUNAT para la representación impresa
```

Modelos: `ElectronicBillingSetting` (config), `ElectronicInvoice`
(comprobantes y notas), `ElectronicSummary` (resúmenes diarios).

Comandos: `app/Console/Commands/GenerarResumenDiario.php` y
`ConsultarTicketsResumen.php`, programados en `routes/console.php`.

Para agregar otro proveedor (p. ej. un API externo), implementa
`BillingDriver` y regístralo en `SunatBilling::driverFor()`.

## Notas

- En **producción** las boletas se informan por *Resumen Diario* y las
  anulaciones por *Comunicación de Baja*; el `GreenterDriver` deja el punto de
  extensión (`voidDocument`) para completar ese flujo según el volumen.
- Para factura el adquirente debe tener **RUC** válido (11 dígitos).
