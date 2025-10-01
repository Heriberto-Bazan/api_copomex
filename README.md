# 🇲🇽 Estados y Municipios COPOMEX - Laravel 11

Sistema completo para consultar estados y municipios de México utilizando la API de COPOMEX, desarrollado con Laravel 11 y arquitectura limpia.

## 🚀 Inicialización del Proyecto

### 📋 Requisitos Previos

- **PHP >= 8.2**
- **Composer**
- **MySQL/MariaDB**
- **Node.js y NPM** (para assets frontend)
- **Git**

### 🔧 Instalación Paso a Paso

#### 1. **Clonar el Repositorio**
```bash
git clone https://github.com/Heriberto-Bazan/api_copomex.git
cd estados-municipios-copomex
```

#### 2. **Instalar Dependencias PHP**
```bash
composer install
```

#### 3. **Configurar Variables de Entorno**
```bash
# Copiar archivo de configuración
cp .env.example .env

# Generar clave de aplicación
php artisan key:generate
```

#### 4. **Configurar BD**
```env
# BD
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=copomex_estados
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password
```

#### 5. **Configurar API COPOMEX**
Agrega tu token de COPOMEX al archivo `.env`:
```env
COPOMEX_TOKEN=ddaf6671-5e28-4d81-bfbb-5e6bdc8b25ba
COPOMEX_BASE_URL=https://api.copomex.com/query
COPOMEX_TIMEOUT=30
```
> **Nota:** Obtén tu token gratuito en [copomex.com](https://copomex.com)


#### 5. **Ejecutar Migraciones**
```bash
php artisan migrate
```

#### 6. **Instalar y Compilar Assets Frontend**
```bash
# Instalar dependencias Node.js
npm install
```

#### 7. **Iniciar Servidor de Desarrollo**
```bash
php artisan serve
```

El proyecto estará disponible en: `http://localhost:8000`

---

### **Limpiar Cache**
```bash
# Limpiar cache de estados y municipios
php artisan copomex:limpiar-cache

# Limpiar todos los caches de Laravel
php artisan optimize:clear
```

### **Probar Conexión COPOMEX**
```bash
# Verificar conectividad con API
php artisan copomex:test
```

---

## 🏗️ Arquitectura del Proyecto

### **📁 Estructura de Directorios**
```
app/
├── DTOs/                          # Data Transfer Objects
├── Repositories/                  # Patrón Repository
├── Services/                      # Lógica de negocio
├── Exceptions/                    # Excepciones personalizadas
├── Http/Controllers/              # Controladores
├── Http/Requests/                 # Form Requests
├── Console/Commands/              # Comandos Artisan
├── Models/                        # Modelos Eloquent
└── Providers/                     # Service Providers
```

### **🔧 Patrones Implementados**
- **Repository Pattern** - Abstracción de datos
- **Service Layer** - Lógica de negocio
- **DTO Pattern** - Transferencia de datos
- **Dependency Injection** - Inversión de dependencias
- **Exception Handling** - Manejo centralizado de errores

---

## 🚀 Funcionalidades

### **🌐 Frontend**
- **DataTables** con paginación server-side
- **Modal** para consultar municipios
- **Exportación CSV** de municipios
- **Carga automática** desde COPOMEX
- **Interfaz responsive** con Bootstrap 5
- **Notificaciones** con SweetAlert2

### **⚙️ Backend**
- **API REST** para estados y municipios
- **Cache inteligente** de consultas
- **Validaciones robustas**
- **Logging completo**
- **Manejo de errores**



## 🔒 Variables de Entorno

### **Configuración Completa**
```env
# Aplicación
APP_NAME="Estados Municipios COPOMEX"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

# Base de Datos
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=copomex_estados
DB_USERNAME=
DB_PASSWORD=


# Cache
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

# Logging
LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug
```

---
