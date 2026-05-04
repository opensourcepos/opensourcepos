#!/bin/bash
# =============================================================================
# deploy-accounting.sh
# Script para desplegar el módulo de Contabilidad en el contenedor Docker
# de OpenSourcePOS sin necesidad de reconstruir la imagen.
# =============================================================================

set -e

CONTAINER_NAME="ospos"  # Nombre del servicio en docker-compose.yml
APP_PATH="/app"          # Ruta base dentro del contenedor (imagen jekkos)
OSPOS_DIR="$(cd "$(dirname "$0")" && pwd)"  # Directorio donde está este script

# ---- Colores para output ----
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # Sin color

echo -e "${BLUE}================================================${NC}"
echo -e "${BLUE}  OSPOS - Despliegue Módulo de Contabilidad     ${NC}"
echo -e "${BLUE}================================================${NC}"
echo ""

# ---- 1. Verificar que Docker esté corriendo ----
echo -e "${YELLOW}[1/5] Verificando que Docker y el contenedor estén activos...${NC}"

if ! docker info > /dev/null 2>&1; then
    echo -e "${RED}✗ Error: Docker no está corriendo. Inicia Docker Desktop e intenta de nuevo.${NC}"
    exit 1
fi

# Intentar encontrar el contenedor (puede tener prefijo del proyecto)
RUNNING_CONTAINER=$(docker ps --filter "name=${CONTAINER_NAME}" --format "{{.Names}}" | head -n 1)

if [ -z "$RUNNING_CONTAINER" ]; then
    echo -e "${YELLOW}  Contenedor no encontrado corriendo. Iniciando con docker compose...${NC}"
    docker compose -f "$OSPOS_DIR/docker-compose.yml" up -d
    echo -e "${YELLOW}  Esperando 10 segundos a que los servicios estén listos...${NC}"
    sleep 10
    RUNNING_CONTAINER=$(docker ps --filter "name=${CONTAINER_NAME}" --format "{{.Names}}" | head -n 1)
fi

if [ -z "$RUNNING_CONTAINER" ]; then
    echo -e "${RED}✗ Error: No se pudo iniciar el contenedor '${CONTAINER_NAME}'.${NC}"
    echo -e "${RED}  Verifica con: docker compose ps${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Contenedor activo: ${RUNNING_CONTAINER}${NC}"
echo ""

# ---- 2. Copiar los nuevos archivos PHP al contenedor ----
echo -e "${YELLOW}[2/5] Copiando archivos del módulo al contenedor...${NC}"

copy_file() {
    local SRC="$1"
    local DEST="$2"
    if [ -f "$SRC" ]; then
        docker cp "$SRC" "$RUNNING_CONTAINER:$DEST"
        echo -e "  ${GREEN}✓ Copiado:${NC} $(basename $SRC)"
    else
        echo -e "  ${RED}✗ No encontrado:${NC} $SRC"
    fi
}

copy_dir() {
    local SRC_DIR="$1"
    local DEST_DIR="$2"
    if [ -d "$SRC_DIR" ]; then
        # Crear el directorio destino si no existe
        docker exec "$RUNNING_CONTAINER" mkdir -p "$DEST_DIR"
        for file in "$SRC_DIR"/*.php; do
            [ -f "$file" ] && docker cp "$file" "$RUNNING_CONTAINER:$DEST_DIR/$(basename $file)" && echo -e "  ${GREEN}✓ Copiado:${NC} $(basename $file)"
        done
    else
        echo -e "  ${YELLOW}⚠ Directorio no encontrado localmente:${NC} $SRC_DIR"
    fi
}

# Migración
copy_file \
    "$OSPOS_DIR/app/Database/Migrations/20260427000000_AccountingModule.php" \
    "$APP_PATH/app/Database/Migrations/20260427000000_AccountingModule.php"
copy_file \
    "$OSPOS_DIR/app/Database/Migrations/20260428000000_AccountingMexico.php" \
    "$APP_PATH/app/Database/Migrations/20260428000000_AccountingMexico.php"

# Modelos
copy_file \
    "$OSPOS_DIR/app/Models/Account.php" \
    "$APP_PATH/app/Models/Account.php"

copy_file \
    "$OSPOS_DIR/app/Models/Journal.php" \
    "$APP_PATH/app/Models/Journal.php"

copy_file \
    "$OSPOS_DIR/app/Models/Accounting_entry.php" \
    "$APP_PATH/app/Models/Accounting_entry.php"

# Modelos modificados (con integración contable)
copy_file \
    "$OSPOS_DIR/app/Models/Sale.php" \
    "$APP_PATH/app/Models/Sale.php"

copy_file \
    "$OSPOS_DIR/app/Models/Expense.php" \
    "$APP_PATH/app/Models/Expense.php"

copy_file \
    "$OSPOS_DIR/app/Models/Receiving.php" \
    "$APP_PATH/app/Models/Receiving.php"

# Controlador
copy_file \
    "$OSPOS_DIR/app/Controllers/Accounting.php" \
    "$APP_PATH/app/Controllers/Accounting.php"

# Vistas
docker exec "$RUNNING_CONTAINER" mkdir -p "$APP_PATH/app/Views/accounting"
copy_file \
    "$OSPOS_DIR/app/Views/accounting/dashboard.php" \
    "$APP_PATH/app/Views/accounting/dashboard.php"

copy_file \
    "$OSPOS_DIR/app/Views/accounting/manage_accounts.php" \
    "$APP_PATH/app/Views/accounting/manage_accounts.php"

copy_file \
    "$OSPOS_DIR/app/Views/accounting/manage_entries.php" \
    "$APP_PATH/app/Views/accounting/manage_entries.php"

# Archivo de idioma
copy_file \
    "$OSPOS_DIR/app/Language/en/Module.php" \
    "$APP_PATH/app/Language/en/Module.php"

echo ""

# ---- 3. Limpiar caché de CodeIgniter ----
echo -e "${YELLOW}[3/5] Limpiando caché y preparando directorios...${NC}"
docker exec "$RUNNING_CONTAINER" bash -c "
    rm -rf ${APP_PATH}/writable/cache/* 2>/dev/null || true
    mkdir -p ${APP_PATH}/public/license
    touch ${APP_PATH}/public/license/LICENSE
    mkdir -p ${APP_PATH}/public/uploads/item_pics
    mkdir -p ${APP_PATH}/writable/uploads
    touch ${APP_PATH}/writable/uploads/importCustomers.csv
    chmod 750 ${APP_PATH}/writable/logs ${APP_PATH}/public/uploads ${APP_PATH}/public/uploads/item_pics
    chmod 640 ${APP_PATH}/writable/uploads/importCustomers.csv
    chown -R www-data:www-data ${APP_PATH}/public/license ${APP_PATH}/public/uploads ${APP_PATH}/writable/uploads
" && echo -e "${GREEN}✓ Caché limpiada y directorios listos${NC}"
echo ""

# ---- 4. Ejecutar migraciones ----
echo -e "${YELLOW}[4/5] Ejecutando migraciones de base de datos...${NC}"

MIGRATE_OUTPUT=$(docker exec "$RUNNING_CONTAINER" bash -c "
    cd $APP_PATH && php spark migrate --all 2>&1
")

echo "$MIGRATE_OUTPUT"

if echo "$MIGRATE_OUTPUT" | grep -qi "error\|exception\|failed"; then
    echo -e "${RED}✗ La migración reportó errores. Revisa el output anterior.${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Migraciones ejecutadas correctamente${NC}"
echo ""

# ---- 5. Verificar que las tablas fueron creadas ----
echo -e "${YELLOW}[5/6] Verificando tablas en la base de datos...${NC}"

TABLES=$(docker exec mysql mysql -u admin -ppointofsale ospos -e \
    "SHOW TABLES LIKE '%account%'; SHOW TABLES LIKE '%journal%';" 2>/dev/null)

if echo "$TABLES" | grep -q "accounts"; then
    echo -e "${GREEN}✓ Tabla 'ospos_accounts' encontrada${NC}"
else
    echo -e "${YELLOW}⚠ Tabla 'ospos_accounts' no encontrada (puede tener prefijo diferente)${NC}"
fi

if echo "$TABLES" | grep -q "journals"; then
    echo -e "${GREEN}✓ Tabla 'ospos_journals' encontrada${NC}"
else
    echo -e "${YELLOW}⚠ Tabla 'ospos_journals' no encontrada${NC}"
fi

echo ""

# ---- 6. Configurar ícono del módulo ----
echo -e "${YELLOW}[6/6] Configurando ícono del módulo...${NC}"
docker exec "$RUNNING_CONTAINER" cp /app/public/images/menubar/expenses.svg /app/public/images/menubar/accounting.svg 2>/dev/null || true
echo -e "${GREEN}✓ Ícono configurado (placeholder)${NC}"

echo ""
echo -e "${GREEN}================================================${NC}"
echo -e "${GREEN}  ¡Módulo de Contabilidad desplegado! 🎉       ${NC}"
echo -e "${GREEN}================================================${NC}"
echo ""
echo -e "  Accede a OSPOS en ${BLUE}http://localhost${NC}"
echo -e "  El módulo 'Accounting' aparece en el menú ${BLUE}Office${NC}"
echo -e "  (Es necesario que el admin le otorgue permisos a otros usuarios)"
echo ""
