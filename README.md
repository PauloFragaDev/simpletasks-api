# SimpleTasks API

Una API REST moderna y bien estructurada para la gestión de tareas personales, construida con Laravel 12 y Laravel Sanctum.

## 🎯 Descripción

SimpleTasks API es un proyecto de portfolio que demuestra la implementación de una API REST completa siguiendo las mejores prácticas de Laravel. Incluye autenticación con tokens, CRUD completo, validaciones robustas, filtros, paginación y control de permisos.

## ✨ Características

- **Autenticación completa** con Laravel Sanctum (registro, login, logout)
- **CRUD de tareas** con validaciones y permisos
- **Filtros avanzados** por status y priority
- **Ordenación personalizable** de resultados
- **Paginación** configurable
- **API Resources** para respuestas JSON consistentes
- **Form Requests** para validaciones organizadas
- **Control de permisos**: cada usuario solo gestiona sus propias tareas

## 🛠️ Tecnologías

- **Laravel 12** - Framework PHP
- **Laravel Sanctum** - Autenticación con tokens
- **MySQL** - Base de datos
- **PHP 8.2+** - Lenguaje de programación

## 📋 Requisitos Previos

- PHP >= 8.2
- Composer
- MySQL
- Extensiones PHP: `pdo`, `mbstring`, `openssl`

## 🚀 Instalación

1. **Clonar el repositorio**
```bash
git clone <url-repositorio>
cd simpletasks-api
```

2. **Instalar dependencias**
```bash
composer install
```

3. **Configurar variables de entorno**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configurar base de datos en `.env`**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=simpletasks-api
DB_USERNAME=root
DB_PASSWORD=
```

5. **Ejecutar migraciones y seeders**
```bash
php artisan migrate --seed
```

6. **Iniciar servidor de desarrollo**
```bash
php artisan serve
```

La API estará disponible en: `http://localhost:8000`

## 📚 Endpoints

### Autenticación

| Método | Endpoint | Descripción | Auth |
|--------|----------|-------------|------|
| POST | `/api/register` | Registrar nuevo usuario | No |
| POST | `/api/login` | Iniciar sesión | No |
| POST | `/api/logout` | Cerrar sesión | Sí |
| GET | `/api/me` | Obtener usuario autenticado | Sí |

### Tareas

| Método | Endpoint | Descripción | Auth |
|--------|----------|-------------|------|
| GET | `/api/tasks` | Listar tareas con filtros | Sí |
| POST | `/api/tasks` | Crear nueva tarea | Sí |
| GET | `/api/tasks/{id}` | Ver tarea específica | Sí |
| PUT/PATCH | `/api/tasks/{id}` | Actualizar tarea | Sí |
| DELETE | `/api/tasks/{id}` | Eliminar tarea | Sí |

## 💡 Ejemplos de Uso

### 1. Registro de Usuario

```bash
POST /api/register
Content-Type: application/json

{
  "name": "test Developer",
  "email": "test@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Respuesta:**
```json
{
  "message": "User registered successfully",
  "user": {
    "id": 1,
    "name": "test Developer",
    "email": "test@example.com",
    "created_at": "2025-11-25T12:00:00.000000Z"
  },
  "token": "1|abcd1234..."
}
```

### 2. Iniciar Sesión

```bash
POST /api/login
Content-Type: application/json

{
  "email": "test@example.com",
  "password": "password123"
}
```

**Respuesta:**
```json
{
  "message": "Login successful",
  "user": {
    "id": 1,
    "name": "test Developer",
    "email": "test@example.com",
    "created_at": "2025-11-25T12:00:00.000000Z"
  },
  "token": "2|xyz9876..."
}
```

### 3. Crear Tarea

```bash
POST /api/tasks
Authorization: Bearer {token}
Content-Type: application/json

{
  "title": "Completar proyecto",
  "description": "Finalizar SimpleTasks API",
  "status": "in_progress",
  "priority": "high",
  "due_date": "2025-12-31"
}
```

**Respuesta:**
```json
{
  "message": "Task created successfully",
  "task": {
    "id": 1,
    "title": "Completar proyecto",
    "description": "Finalizar SimpleTasks API",
    "status": "in_progress",
    "priority": "high",
    "due_date": "2025-12-31",
    "created_at": "2025-11-25T12:00:00.000000Z",
    "updated_at": "2025-11-25T12:00:00.000000Z"
  }
}
```

### 4. Listar Tareas con Filtros

```bash
GET /api/tasks?status=in_progress&priority=high&sort_by=due_date&sort_order=asc&per_page=10
Authorization: Bearer {token}
```

**Respuesta:**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Completar proyecto",
      "description": "Finalizar SimpleTasks API",
      "status": "in_progress",
      "priority": "high",
      "due_date": "2025-12-31",
      "created_at": "2025-11-25T12:00:00.000000Z",
      "updated_at": "2025-11-25T12:00:00.000000Z"
    }
  ],
  "links": {...},
  "meta": {...}
}
```

## 🔍 Filtros y Parámetros

### Filtros Disponibles
- `status`: `pending`, `in_progress`, `done`
- `priority`: `low`, `medium`, `high`

### Ordenación
- `sort_by`: Campo para ordenar (default: `created_at`)
- `sort_order`: `asc` o `desc` (default: `desc`)

### Paginación
- `per_page`: Número de resultados por página (default: 15)

## 🔐 Autenticación

La API utiliza Laravel Sanctum para autenticación basada en tokens. Para acceder a endpoints protegidos:

1. Obtén un token mediante `/api/login` o `/api/register`
2. Incluye el token en el header de cada request:
```
Authorization: Bearer {tu-token}
```

## 👤 Usuario de Prueba

Los seeders crean un usuario de ejemplo:

- **Email:** `test@example.com`
- **Password:** `password123`

Y 3 tareas de ejemplo asociadas.

## 📁 Estructura del Proyecto

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       ├── AuthController.php
│   │       └── TaskController.php
│   ├── Requests/
│   │   ├── StoreTaskRequest.php
│   │   └── UpdateTaskRequest.php
│   └── Resources/
│       ├── TaskResource.php
│       └── UserResource.php
├── Models/
│   ├── Task.php
│   └── User.php
database/
├── migrations/
└── seeders/
routes/
└── api.php
```

## 🧪 Testing

```bash
php artisan test
```

## 📝 Validaciones

### Crear Tarea
- `title`: requerido, string, máx. 255 caracteres
- `description`: opcional, string
- `status`: opcional, enum (`pending`, `in_progress`, `done`)
- `priority`: opcional, enum (`low`, `medium`, `high`)
- `due_date`: opcional, fecha, debe ser hoy o posterior

### Actualizar Tarea
- `title`: opcional pero si está presente debe ser string, máx. 255
- `description`: opcional, string
- `status`: opcional, enum (`pending`, `in_progress`, `done`)
- `priority`: opcional, enum (`low`, `medium`, `high`)
- `due_date`: opcional, fecha, debe ser hoy o posterior

## 🤝 Contribuciones

Este es un proyecto de portfolio personal. Si encuentras algún bug o tienes sugerencias, siéntete libre de abrir un issue.

## 📄 Licencia

Este proyecto está bajo la licencia MIT.
