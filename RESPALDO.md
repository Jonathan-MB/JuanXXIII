# Respaldo y recuperación

Este repositorio guarda **solo el código y la documentación**. Lo demás vive en el repositorio **privado** `JuanXXIII-documentos`, clonado en `C:\JuanXXIII-documentos`:

| Qué | Dónde |
|---|---|
| Código y documentación de la app | Este repositorio (GitHub). |
| Base de datos (`juanxxiii`) | `JuanXXIII-documentos/respaldos/base/juanxxiii.sql` |
| Archivos subidos a la app (`storage/app/private`) | `JuanXXIII-documentos/respaldos/archivos/private/` |
| Documentos originales y sus vistas previas | `JuanXXIII-documentos/originales/` y `vista-previa/` |
| Cargas de datos desde Excel | `JuanXXIII-documentos/importaciones/` |
| `.env` | En ningún lado: se crea desde `.env.example`. La app no cifra datos con `APP_KEY`, así que una clave nueva no pierde nada (solo cierra las sesiones). |

Nunca subir aquí datos de personas, comprobantes ni el `.env`: este repositorio puede ser público.

## Respaldar

```bash
/c/JuanXXIII-documentos/respaldar.sh
```

Hace un volcado de la base y copia `storage/app/private` al repositorio privado, y lo sube a GitHub (solo si algo cambió). Correrlo al terminar cada jornada y después de cargar o corregir datos. Lo que no se respalde existe solo en el PC.

## Recuperar en un PC nuevo

Los pasos completos están en el README de `JuanXXIII-documentos`. En resumen:

1. Clonar los dos repositorios (`/c/xampp/htdocs/JuanXXIII` y `/c/JuanXXIII-documentos`).
2. `composer install`, `cp .env.example .env`, `php artisan key:generate`. **Sin** `migrate --seed`.
3. `/c/JuanXXIII-documentos/restaurar.sh`, que trae la base y los archivos del último respaldo.
4. `php artisan test`, que usa una base aparte y no toca los datos reales.

## Datos reales

Desde el 20 de noviembre de 2026, la base `juanxxiii` de Secretaría tiene datos reales. Por eso:

- no correr `DatosPruebaSeeder`, `migrate:fresh` ni `db:seed` sobre ella;
- `php artisan datos-prueba:quitar` ya no hace falta allí;
- antes de un cambio grande, sacar una copia (`mysqldump`) en `C:\JuanXXIII-documentos\respaldos-locales\`, que no se sube;
- después de bajar código nuevo (`git pull`), aplicar los cambios de la base con `php artisan migrate --force < /dev/null`: solo agrega lo nuevo y no borra datos. El 21 de noviembre de 2026 se aplicó así la que permite dejar pendientes la cédula, la fecha de nacimiento y la talla de quien Secretaría agrega en Llegada.
