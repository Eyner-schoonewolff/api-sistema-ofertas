# API Sistema de Ofertas

API REST desarrollada en PHP 8.4 puro para la gestión de ofertas (licitaciones), utilizando MongoDB como base de datos.

## Requisitos

- PHP 8.4 o superior
- Composer
- MongoDB 4.0 o superior
- Extensiones PHP requeridas:
  - mongodb
  - json
  - mbstring
  - zip (para exportación Excel)

## Instalación

1. Clonar el repositorio:
```bash
git clone <repository-url>
cd api-sistema-ofertas
```

2. Instalar dependencias:
```bash
composer install
```

3. Configurar variables de entorno:
```bash
cp .env.example .env
```

Editar el archivo `.env` con tus configuraciones:
```env
MONGODB_URL=mongodb://localhost:27017
MONGODB_DATABASE=sistema_ofertas
APP_ENV=development
APP_DEBUG=true
APP_TIMEZONE=America/Bogota
UPLOAD_MAX_SIZE=10485760
UPLOAD_ALLOWED_TYPES=pdf,zip
BASE_URL=http://localhost:8000
```

4. Crear directorio de uploads:
```bash
mkdir -p public/uploads/ofertas
chmod 755 public/uploads/ofertas
```

## Estructura del Proyecto

```
api-sistema-ofertas/
├── app/
│   ├── Controllers/      # Controladores de la API
│   ├── Models/          # Modelos para MongoDB
│   ├── Services/        # Servicios de negocio
│   ├── Validators/      # Validadores de datos
│   └── Helpers/         # Utilidades y helpers
├── config/              # Archivos de configuración
├── public/              # Punto de entrada público
│   ├── index.php       # Archivo principal
│   └── uploads/         # Archivos subidos
├── routes/              # Definición de rutas
└── vendor/              # Dependencias de Composer
```

## Endpoints de la API

### Ofertas

- `GET /ofertas` - Lista todas las ofertas (con paginación y filtros)
- `POST /ofertas` - Crea una nueva oferta
- `GET /ofertas/{id}` - Obtiene el detalle de una oferta
- `PUT /ofertas/{id}` - Actualiza una oferta existente
- `POST /ofertas/{id}/documentos` - Sube un documento a una oferta
- `GET /ofertas/export/excel` - Exporta ofertas a Excel

### Parámetros de Query (GET /ofertas)

- `page`: Número de página (default: 1)
- `per_page`: Elementos por página (default: 10)
- `estado`: Filtrar por estado (BORRADOR, ACTIVA, CERRADA)
- `fecha_inicio`: Filtrar por fecha de inicio
- `fecha_cierre`: Filtrar por fecha de cierre

## Ejemplos de Uso

### Crear una oferta

```bash
curl -X POST http://localhost:8000/ofertas \
  -H "Content-Type: application/json" \
  -d '{
    "objeto": "Adquisición de equipos informáticos",
    "descripcion": "Descripción detallada de la oferta",
    "moneda": "COP",
    "presupuesto": 1500000.50,
    "actividad_id": "507f1f77bcf86cd799439011",
    "fecha_inicio": "2025-01-10",
    "hora_inicio": "08:00",
    "fecha_cierre": "2025-01-20",
    "hora_cierre": "17:00",
    "estado": "BORRADOR"
  }'
```

### Listar ofertas

```bash
curl http://localhost:8000/ofertas?page=1&per_page=10&estado=ACTIVA
```

### Subir un documento

```bash
curl -X POST http://localhost:8000/ofertas/{id}/documentos \
  -F "archivo=@documento.pdf" \
  -F "titulo=Documento técnico" \
  -F "descripcion=Descripción del documento"
```

## Formato de Respuesta

### Respuesta exitosa

```json
{
  "success": true,
  "message": "Oferta creada exitosamente",
  "data": {
    "id": "507f1f77bcf86cd799439011",
    "consecutivo": "O-0001-25",
    "objeto": "Adquisición de equipos",
    ...
  }
}
```

### Respuesta con errores

```json
{
  "success": false,
  "message": "Error de validación",
  "errors": {
    "objeto": ["El objeto de la oferta es obligatorio"],
    "presupuesto": ["El presupuesto debe ser un número"]
  }
}
```

### Respuesta paginada

```json
{
  "success": true,
  "data": [...],
  "pagination": {
    "page": 1,
    "per_page": 10,
    "total": 50,
    "total_pages": 5
  }
}
```

## Reglas de Negocio

1. **Consecutivo automático**: Se genera automáticamente con formato `O-{000N}-{YY}`
2. **Validación de fechas**: La fecha y hora de inicio deben ser menores a la fecha y hora de cierre
3. **Documentos obligatorios**: En edición, la oferta debe tener al menos un documento cargado
4. **Tipos de archivo**: Solo se permiten archivos PDF o ZIP
5. **Tamaño máximo**: Configurable en `.env` (default: 10MB)

## Desarrollo

### Servidor de desarrollo

```bash
php -S localhost:8000 -t public
```

### Verificar sintaxis

```bash
php -l app/**/*.php
```

## Licencia

prueba tecnica para Solvos
