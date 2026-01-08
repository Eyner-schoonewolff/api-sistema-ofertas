# Colección de Postman - Sistema de Ofertas API

Esta carpeta contiene la colección de Postman con todos los endpoints de la API.

## Instalación

1. Abre Postman
2. Click en "Import" (botón superior izquierdo)
3. Selecciona el archivo `Sistema_Ofertas_API.postman_collection.json`
4. La colección se importará con todos los endpoints

## Configuración de Variables

La colección incluye variables de entorno que debes configurar:

### Variables de la Colección

- `base_url`: URL base de la API (por defecto: `http://localhost:8000`)
- `oferta_id`: ID de una oferta existente (se actualiza automáticamente)
- `actividad_id`: ID de una actividad existente en MongoDB

### Configurar Variables

1. Click derecho en la colección → "Edit"
2. Ve a la pestaña "Variables"
3. Actualiza los valores según tu entorno:
   - `base_url`: Cambia si tu servidor está en otro puerto o dominio
   - `actividad_id`: Necesitas crear una actividad primero o usar una existente

## Endpoints Incluidos

### Actividades

1. **Listar Actividades** (GET)
   - Lista todas las actividades con paginación
   - Parámetros opcionales: `page`, `per_page`, `search`

2. **Buscar Actividades** (GET)
   - Ejemplo específico de búsqueda por término

3. **Obtener Detalle de Actividad** (GET)
   - Obtiene el detalle completo de una actividad
   - Requiere: `{{actividad_id}}`

4. **Crear Actividad** (POST)
   - Crea una nueva actividad (UNSPSC)
   - El código_producto debe ser único

5. **Actualizar Actividad** (PUT)
   - Actualiza una actividad existente
   - Requiere: `{{actividad_id}}`

6. **Eliminar Actividad** (DELETE)
   - Elimina una actividad
   - Requiere: `{{actividad_id}}`

### Ofertas

1. **Listar Ofertas** (GET)
   - Lista todas las ofertas con paginación
   - Parámetros opcionales: `page`, `per_page`, `estado`, `fecha_inicio`, `fecha_cierre`

2. **Listar Ofertas - Filtro por Estado** (GET)
   - Ejemplo específico filtrando por estado ACTIVA

3. **Obtener Detalle de Oferta** (GET)
   - Obtiene el detalle completo incluyendo actividad y documentos
   - Requiere: `{{oferta_id}}`

4. **Crear Oferta** (POST)
   - Crea una nueva oferta
   - El consecutivo se genera automáticamente
   - Requiere: `{{actividad_id}}`

5. **Actualizar Oferta** (PUT)
   - Actualiza una oferta existente
   - Requiere: `{{oferta_id}}`
   - La oferta debe tener al menos un documento cargado

6. **Subir Documento a Oferta** (POST)
   - Sube un archivo PDF o ZIP a una oferta
   - Requiere: `{{oferta_id}}` y archivo
   - Formato: multipart/form-data

7. **Exportar Ofertas a Excel** (GET)
   - Exporta todas las ofertas a Excel
   - El archivo se descarga automáticamente

8. **Exportar Ofertas Activas a Excel** (GET)
   - Exporta solo ofertas activas a Excel

## Flujo de Trabajo Recomendado

1. **Primero**: Crear una actividad
   - Usa el endpoint "Crear Actividad" para crear una nueva actividad
   - Copia el `id` de la respuesta
   - Actualiza la variable `{{actividad_id}}` en la colección
   - O usa el endpoint "Listar Actividades" para obtener una existente

2. **Crear Oferta**: Usa el endpoint "Crear Oferta"
   - Copia el `id` de la respuesta
   - Actualiza la variable `{{oferta_id}}` en la colección

3. **Subir Documento**: Usa el endpoint "Subir Documento a Oferta"
   - Selecciona un archivo PDF o ZIP
   - La oferta debe tener al menos un documento para poder editarse

4. **Actualizar Oferta**: Ahora puedes actualizar la oferta

5. **Listar/Exportar**: Usa los endpoints de listado y exportación

## Notas Importantes

- **Actividades**: Debes crear una actividad primero usando el endpoint "Crear Actividad" antes de crear ofertas
- **Código Producto**: El `codigo_producto` debe ser único en las actividades
- **Estructura UNSPSC**: Las actividades siguen la estructura UNSPSC (Segmento > Familia > Clase > Producto)
- **Documentos**: Las ofertas deben tener al menos un documento para poder editarse
- **Consecutivo**: Se genera automáticamente con formato `O-{000N}-{YY}`
- **Estados permitidos**: `BORRADOR`, `ACTIVA`, `CERRADA`
- **Monedas permitidas**: `COP`, `USD`, `EUR`
- **Tipos de archivo**: Solo `PDF` y `ZIP` (máximo 10MB)

## Solución de Problemas

### Error 503: No se pudo conectar a MongoDB
- Verifica que MongoDB esté corriendo
- Verifica la configuración en `.env`
- Para MongoDB Atlas, verifica la IP whitelist

### Error 404: Oferta no encontrada
- Verifica que el `{{oferta_id}}` sea correcto
- Asegúrate de haber creado la oferta primero

### Error 422: Error de validación
- Revisa los campos requeridos
- Verifica los formatos de fecha (YYYY-MM-DD) y hora (HH:MM)
- Asegúrate de que la fecha de inicio sea anterior a la fecha de cierre

### Error al subir documento
- Verifica que el archivo sea PDF o ZIP
- Verifica que el tamaño no exceda 10MB
- Asegúrate de usar `form-data` y no `raw` JSON
