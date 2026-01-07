# Ejemplos de Uso de la API

## Configuración Inicial

Antes de usar la API, asegúrate de tener:

1. MongoDB corriendo
2. Variables de entorno configuradas en `.env`
3. Dependencias instaladas: `composer install`
4. Iniciar app php `php -S localhost:8000`

## Ejemplos con cURL

### 1. Crear una Oferta

```bash
curl -X POST http://localhost:8000/ofertas \
  -H "Content-Type: application/json" \
  -d '{
    "objeto": "Adquisición de equipos informáticos",
    "descripcion": "Se requiere la adquisición de 20 computadores de escritorio con especificaciones técnicas mínimas para oficina",
    "moneda": "COP",
    "presupuesto": 50000000,
    "actividad_id": "507f1f77bcf86cd799439011",
    "fecha_inicio": "2025-01-10",
    "hora_inicio": "08:00",
    "fecha_cierre": "2025-01-20",
    "hora_cierre": "17:00",
    "estado": "BORRADOR"
  }'
```

**Respuesta esperada:**
```json
{
  "success": true,
  "message": "Oferta creada exitosamente",
  "data": {
    "id": "507f1f77bcf86cd799439011",
    "consecutivo": "O-0001-25",
    "objeto": "Adquisición de equipos informáticos",
    "descripcion": "Se requiere la adquisición...",
    "moneda": "COP",
    "presupuesto": 50000000,
    "actividad_id": "507f1f77bcf86cd799439011",
    "fecha_inicio": "2025-01-10",
    "hora_inicio": "08:00",
    "fecha_cierre": "2025-01-20",
    "hora_cierre": "17:00",
    "estado": "BORRADOR",
    "creado_en": "2025-01-09T10:30:00+00:00",
    "actualizado_en": "2025-01-09T10:30:00+00:00"
  }
}
```

### 2. Listar Ofertas

```bash
# Listar todas las ofertas (primera página)
curl http://localhost:8000/ofertas

# Listar con paginación
curl "http://localhost:8000/ofertas?page=1&per_page=10"

# Filtrar por estado
curl "http://localhost:8000/ofertas?estado=ACTIVA"

# Filtrar por fecha de inicio
curl "http://localhost:8000/ofertas?fecha_inicio=2025-01-10"
```

### 3. Obtener Detalle de una Oferta

```bash
curl http://localhost:8000/ofertas/507f1f77bcf86cd799439011
```

**Respuesta esperada:**
```json
{
  "success": true,
  "message": "Oferta obtenida exitosamente",
  "data": {
    "id": "507f1f77bcf86cd799439011",
    "consecutivo": "O-0001-25",
    "objeto": "Adquisición de equipos informáticos",
    "descripcion": "...",
    "moneda": "COP",
    "presupuesto": 50000000,
    "actividad_id": "507f1f77bcf86cd799439011",
    "actividad": {
      "id": "507f1f77bcf86cd799439011",
      "codigo_segmento": 10000000,
      "segmento": "Segmento",
      "codigo_familia": 10100000,
      "familia": "Familia",
      "codigo_clase": 10101500,
      "clase": "Clase",
      "codigo_producto": 10101501,
      "producto": "Producto"
    },
    "documentos": [
      {
        "id": "507f1f77bcf86cd799439012",
        "oferta_id": "507f1f77bcf86cd799439011",
        "titulo": "Documento técnico",
        "descripcion": "Especificaciones técnicas",
        "archivo": "/uploads/ofertas/doc1.pdf",
        "creado_en": "2025-01-09T10:35:00+00:00"
      }
    ],
    "fecha_inicio": "2025-01-10",
    "hora_inicio": "08:00",
    "fecha_cierre": "2025-01-20",
    "hora_cierre": "17:00",
    "estado": "BORRADOR",
    "creado_en": "2025-01-09T10:30:00+00:00",
    "actualizado_en": "2025-01-09T10:30:00+00:00"
  }
}
```

### 4. Actualizar una Oferta

```bash
curl -X PUT http://localhost:8000/ofertas/507f1f77bcf86cd799439011 \
  -H "Content-Type: application/json" \
  -d '{
    "objeto": "Adquisición de equipos informáticos - Actualizado",
    "descripcion": "Descripción actualizada",
    "moneda": "USD",
    "presupuesto": 12000,
    "estado": "ACTIVA"
  }'
```

**Nota:** El consecutivo no se puede modificar. La oferta debe tener al menos un documento cargado para poder editarse.

### 5. Subir un Documento

```bash
curl -X POST http://localhost:8000/ofertas/507f1f77bcf86cd799439011/documentos \
  -F "archivo=@/ruta/al/archivo/documento.pdf" \
  -F "titulo=Documento técnico" \
  -F "descripcion=Especificaciones técnicas detalladas"
```

**Respuesta esperada:**
```json
{
  "success": true,
  "message": "Documento subido exitosamente",
  "data": {
    "id": "507f1f77bcf86cd799439012",
    "oferta_id": "507f1f77bcf86cd799439011",
    "titulo": "Documento técnico",
    "descripcion": "Especificaciones técnicas detalladas",
    "archivo": "/uploads/ofertas/doc_507f1f77bcf86cd799439012.pdf",
    "creado_en": "2025-01-09T10:35:00+00:00"
  }
}
```

### 6. Exportar a Excel

```bash
# Exportar todas las ofertas
curl -O http://localhost:8000/ofertas/export/excel

# Exportar solo ofertas activas
curl -O "http://localhost:8000/ofertas/export/excel?estado=ACTIVA"
```

El archivo se descargará con el nombre `ofertas_YYYY-MM-DD_HHMMSS.xlsx`

## Ejemplos con JavaScript (Fetch API)

### Crear una Oferta

```javascript
const response = await fetch('http://localhost:8000/ofertas', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    objeto: 'Adquisición de equipos informáticos',
    descripcion: 'Se requiere la adquisición de 20 computadores',
    moneda: 'COP',
    presupuesto: 50000000,
    actividad_id: '507f1f77bcf86cd799439011',
    fecha_inicio: '2025-01-10',
    hora_inicio: '08:00',
    fecha_cierre: '2025-01-20',
    hora_cierre: '17:00',
    estado: 'BORRADOR'
  })
});

const data = await response.json();
console.log(data);
```

### Subir un Documento

```javascript
const formData = new FormData();
formData.append('archivo', fileInput.files[0]);
formData.append('titulo', 'Documento técnico');
formData.append('descripcion', 'Especificaciones técnicas');

const response = await fetch('http://localhost:8000/ofertas/507f1f77bcf86cd799439011/documentos', {
  method: 'POST',
  body: formData
});

const data = await response.json();
console.log(data);
```

## Códigos de Estado HTTP

- `200` - OK (operación exitosa)
- `201` - Created (recurso creado exitosamente)
- `400` - Bad Request (error en la petición)
- `404` - Not Found (recurso no encontrado)
- `422` - Unprocessable Entity (error de validación)
- `500` - Internal Server Error (error del servidor)

## Manejo de Errores

Todas las respuestas de error siguen este formato:

```json
{
  "success": false,
  "message": "Mensaje de error descriptivo",
  "errors": {
    "campo": ["Error específico del campo"]
  }
}
```

Ejemplo de error de validación:

```json
{
  "success": false,
  "message": "Error de validación",
  "errors": {
    "objeto": ["El objeto de la oferta es obligatorio"],
    "presupuesto": ["El presupuesto debe ser un número"],
    "fecha_inicio": ["La fecha y hora de inicio deben ser menores a la fecha y hora de cierre"]
  }
}
```
