#!/bin/bash

# Script para configurar el archivo .env con la conexión a MongoDB Atlas

cat > .env << 'EOF'
# MongoDB Configuration
MONGODB_URL=mongodb+srv://enewolff2014_db_user:XqNSCRWbPCsrARmt@cluster0.iflacpi.mongodb.net/
MONGODB_DATABASE=sistema_ofertas

# Application Configuration
APP_ENV=development
APP_DEBUG=true
APP_TIMEZONE=America/Bogota

# Upload Configuration
UPLOAD_MAX_SIZE=10485760
UPLOAD_ALLOWED_TYPES=pdf,zip

# Server Configuration
BASE_URL=http://localhost:8000
EOF

echo "✓ Archivo .env creado exitosamente"
echo ""
echo "Configuración:"
echo "  MONGODB_URL: mongodb+srv://enewolff2014_db_user:***@cluster0.iflacpi.mongodb.net/"
echo "  MONGODB_DATABASE: sistema_ofertas"
echo ""
echo "Ahora puedes ejecutar: php test_connection.php"
