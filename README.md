# InventoryManager - Laboratory Inventory Management System

InventoryManager is a comprehensive inventory management system designed for university laboratories. Built with Laravel and Filament, it provides efficient control over equipment, resources, bookings, and loans with role-based access control and detailed reporting.

## 📋 Tabla de Contenidos

1. [Descripción del Proyecto](#descripción-del-proyecto)
2. [Requisitos del Sistema](#requisitos-del-sistema)
3. [Instalación](#instalación)
4. [Configuración del Entorno](#configuración-del-entorno)
5. [Uso y Desarrollo](#uso-y-desarrollo)
6. [Estructura del Proyecto](#estructura-del-proyecto)
7. [Modelos de Datos](#modelos-de-datos)
8. [Scripts y Comandos](#scripts-y-comandos)
9. [Pruebas](#pruebas)
10. [Despliegue](#despliegue)
11. [Mantenimiento](#mantenimiento)
12. [Contribución](#contribución)
13. [Contacto](#contacto)

## 📖 Descripción del Proyecto

Este es un sistema de gestión de laboratorios construido con **Laravel 12** y **Filament 3** que permite:

- Gestión de laboratorios y equipos
- Sistema de reservas y préstamos
- Control de horarios estructurados y no estructurados
- Gestión de usuarios con roles y permisos
- Sistema de autenticación con correo y contraseña
- Exportación de datos a Excel
- Calendario integrado con FullCalendar

### Tecnologías Principales

- **Backend**: Laravel 12, PHP 8.3+
- **Frontend**: Filament 3, Tailwind CSS, Vite
- **Base de Datos**: MySQL 8.0+
- **Autenticación**: Email y contraseña (Filament)
- **Permisos**: Spatie Roles & Permissions
- **UI**: Filament Admin Panel

## 🔧 Requisitos del Sistema

Asegúrate de tener instalados los siguientes componentes:

### Backend
- **PHP** >= 8.3
- **Composer** (última versión estable)
- **MySQL** >= 8.0
- **Extensiones PHP requeridas**:
  - `pdo_mysql`
  - `mbstring`
  - `xml`
  - `curl`
  - `zip`
  - `gd`
  - `fileinfo`

### Frontend
- **Node.js** >= 18.0
- **npm** (última versión estable)

### Opcional (Recomendado)
- **Redis** (para caché y colas)
- **Git** (control de versiones)

## 🚀 Instalación

Sigue estos pasos para configurar el proyecto en tu entorno local:

### 1. Clonar el Repositorio
```bash
git clone https://github.com/tu-usuario/InventoryManager.git
cd InventoryManager
```

### 2. Instalar Dependencias
```bash
# Dependencias de PHP
composer install

# Dependencias de Node.js
npm install
```

### 3. Configurar Entorno
```bash
# Copiar archivo de configuración
cp .env.example .env

# Generar clave de la aplicación
php artisan key:generate

# Crear enlace simbólico para storage
php artisan storage:link
```

### 4. Configurar Base de Datos
```bash
# Crear una base de datos llamada 'inventory_manager'
# Luego ejecutar las migraciones
php artisan migrate

# Opcional: Poblar la base de datos con datos de prueba
php artisan migrate:refresh --seed
```

### 5. Iniciar Servidores
```bash
# Terminal 1: Servidor Laravel
php artisan serve

# Terminal 2: Compilación de assets
npm run dev
```

## ⚙️ Configuración del Entorno

### Variables de Entorno Requeridas

Crea un archivo `.env` en la raíz del proyecto basado en `.env.example`. Las variables más importantes son:

```env
# Configuración de la Aplicación
APP_NAME='InventoryManager'
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

# Base de Datos
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inventory_manager
DB_USERNAME=root
DB_PASSWORD=tu_contraseña

# Configuración de Correo
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu_correo@example.com
MAIL_PASSWORD=tu_contraseña_aplicacion
MAIL_ENCRYPTION=tls
```

### ⚠️ Notas Importantes sobre las Variables de Entorno

- **Seguridad**:
  - Nunca compartas tus credenciales privadas.
  - No subas el archivo `.env` al repositorio.
  - Mantén las claves de API y secretos seguros.

- **Configuración Local**:
  - Cada desarrollador debe crear su propio archivo `.env`.
  - Usa `.env.example` como plantilla.
  - Ajusta los valores según tu entorno local.

- **Base de Datos**:
  - Crea una base de datos local llamada `inventory_manager`.
  - Configura las credenciales de tu base de datos local en el archivo `.env`.

## 🛠️ Uso y Desarrollo

### Comandos de Desarrollo

```bash
# Ejecutar migraciones
php artisan migrate

# Refrescar migraciones con seeders
php artisan migrate:refresh --seed

# Iniciar servidor de desarrollo
php artisan serve

# Compilar assets de desarrollo
npm run dev

# Compilar assets para producción
npm run build

# Limpiar caché
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Acceso a la Aplicación

- **Panel de Administración**: `http://127.0.0.1:8000/admin`
- **Autenticación**: La aplicación utiliza autenticación de Laravel con email y contraseña

### Flujo de Trabajo Típico

1. **Desarrollo de Features**: Trabaja en ramas feature separadas
2. **Testing**: Ejecuta pruebas antes de cada commit
3. **Build**: Compila assets para producción antes de desplegar
4. **Migraciones**: Siempre verifica migraciones en staging antes de producción

## 📁 Estructura del Proyecto

La estructura principal del proyecto es la siguiente:

```
├── app/
│   ├── Http/Controllers/    # Controladores HTTP
│   ├── Models/             # Modelos Eloquent
│   ├── Filament/           # Recursos de Filament
│   ├── Policies/           # Políticas de autorización
│   └── Providers/          # Service Providers
├── bootstrap/              # Archivos de bootstrap
├── config/                 # Archivos de configuración
├── database/
│   ├── migrations/         # Migraciones de base de datos
│   ├── seeders/           # Seeders para datos de prueba
│   └── factories/         # Factories para testing
├── public/                 # Archivos públicos
├── resources/
│   ├── views/             # Vistas Blade
│   ├── js/                # Assets JavaScript
│   └── css/               # Assets CSS
├── routes/                # Definición de rutas
│   ├── api.php            # Rutas API
│   ├── web.php            # Rutas web
│   └── console.php        # Rutas de consola
├── storage/               # Almacenamiento de archivos
├── tests/                 # Pruebas automatizadas
│   ├── Feature/           # Pruebas de características
│   └── Unit/              # Pruebas unitarias
└── vendor/                # Dependencias de Composer
```

## 🗄️ Modelos de Datos

El sistema utiliza los siguientes modelos principales:

### Modelos Principales
- **User**: Gestión de usuarios y autenticación
- **Laboratory**: Gestión de laboratorios
- **Equipment**: Control de equipos
- **Booking**: Sistema de reservas
- **Loan**: Gestión de préstamos
- **Schedule**: Control de horarios (estructurados y no estructurados)
- **Product**: Gestión de productos
- **Role/Permission**: Sistema de roles y permisos

### Relaciones Importantes
- `User` → `Role` (muchos a muchos)
- `Laboratory` → `Equipment` (uno a muchos)
- `Booking` → `User` (muchos a uno)
- `Schedule` → `Laboratory` (muchos a uno)

## 📜 Scripts y Comandos

### Comandos Personalizados
```bash
# Limpiar caché completo
php artisan optimize:clear

# Generar IDE Helper
php artisan ide-helper:generate
php artisan ide-helper:models
```

### Scripts de Package.json
```json
{
  "dev": "vite",           // Desarrollo
  "build": "vite build"    // Producción
}
```

## 🧪 Pruebas

### Ejecutar Pruebas
```bash
# Ejecutar todas las pruebas
php artisan test

# Ejecutar pruebas con cobertura
php artisan test --coverage

# Ejecutar pruebas específicas
php artisan test tests/Feature/AdminAccessTest.php
```

### Tipos de Pruebas
- **Feature Tests**: Pruebas de funcionalidad completa
- **Unit Tests**: Pruebas de unidades individuales
- **Browser Tests**: Pruebas de navegador (si se configuran)

## 🚀 Despliegue

### Preparación para Producción
```bash
# Instalar dependencias de producción
composer install --optimize-autoloader --no-dev

# Compilar assets para producción
npm run build

# Optimizar caché
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Ejecutar migraciones
php artisan migrate --force
```

### Variables de Entorno de Producción
- `APP_ENV=production`
- `APP_DEBUG=false`
- Configurar caché y colas apropiadamente
- Usar variables de entorno reales

## 🔧 Mantenimiento

### Tareas de Mantenimiento Regular
1. **Actualización de Dependencias**:
   ```bash
   composer update
   npm update
   ```

2. **Limpieza de Logs**:
   ```bash
   php artisan log:clear
   ```

3. **Optimización de Base de Datos**:
   ```bash
   php artisan db:optimize
   ```

4. **Backup de Base de Datos**:
   ```bash
   mysqldump -u root -p inventory_manager > backup.sql
   ```

### Monitoreo
- Revisar logs en `storage/logs/laravel.log`
- Monitorear rendimiento con herramientas como Laravel Telescope
- Verificar uso de memoria y CPU

### Solución de Problemas Comunes
- **Error 500**: Verificar permisos de storage
- **Error de Base de Datos**: Revisar configuración en `.env`
- **Assets no cargan**: Ejecutar `npm run build`

## 🤝 Contribución

### Flujo de Trabajo
1. Crear rama desde `main`
2. Desarrollar la funcionalidad
3. Escribir pruebas
4. Hacer commit con mensajes claros
5. Crear Pull Request
6. Revisión y merge

### Estilo de Código
- Usar [Laravel Pint](https://laravel.com/docs/pint) para formateo
- Seguir convenciones de PSR-12
- Escribir código auto-documentado

### Mensajes de Commit
```
feat: agregar nueva funcionalidad
fix: corregir error en login
docs: actualizar README
refactor: optimizar consulta SQL
test: agregar pruebas para modelo User
```

## 📞 Contacto

### Equipo de Desarrollo
- **Maintainer**: [Nombre del Maintainer]
- **Email**: maintainer@example.com
- **Repositorio**: [GitHub URL]

### Soporte
- Para incidencias: Crear issue en GitHub
- Para dudas técnicas: Contactar al equipo de desarrollo
- Documentación adicional: Revisar carpeta `docs/` (si existe)

---

**InventoryManager** - Sistema de gestión de inventarios de propósito general.
