#!/bin/bash

# =====================================================
# Script para generar imagen del diagrama de base de datos
# =====================================================
# Este script genera una imagen PNG/SVG desde el diagrama Mermaid
# Requisitos:
# - Node.js instalado
# - @mermaid-js/mermaid-cli instalado globalmente: npm install -g @mermaid-js/mermaid-cli
# =====================================================

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
DIAGRAM_FILE="$SCRIPT_DIR/database-diagram.md"
OUTPUT_PNG="$SCRIPT_DIR/database-diagram.png"
OUTPUT_SVG="$SCRIPT_DIR/database-diagram.svg"

# Colores para output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${GREEN}Generando imagen del diagrama de base de datos...${NC}"

# Verificar si mmdc está instalado
if ! command -v mmdc &> /dev/null; then
    echo -e "${YELLOW}@mermaid-js/mermaid-cli no está instalado.${NC}"
    echo -e "${YELLOW}Instalando @mermaid-js/mermaid-cli...${NC}"
    npm install -g @mermaid-js/mermaid-cli
    
    if [ $? -ne 0 ]; then
        echo -e "${RED}Error al instalar @mermaid-js/mermaid-cli${NC}"
        echo -e "${YELLOW}Alternativa: Usa el Mermaid Live Editor en https://mermaid.live/${NC}"
        echo -e "${YELLOW}O instala manualmente: npm install -g @mermaid-js/mermaid-cli${NC}"
        exit 1
    fi
fi

# Extraer el código Mermaid del archivo markdown
MERMAID_CODE=$(awk '/^```mermaid$/,/^```$/' "$DIAGRAM_FILE" | sed '/^```mermaid$/d; /^```$/d')

if [ -z "$MERMAID_CODE" ]; then
    echo -e "${RED}No se encontró código Mermaid en el archivo${NC}"
    exit 1
fi

# Crear archivo temporal con solo el código Mermaid
TEMP_MERMAID=$(mktemp)
echo "$MERMAID_CODE" > "$TEMP_MERMAID"

# Generar PNG
echo -e "${GREEN}Generando PNG...${NC}"
mmdc -i "$TEMP_MERMAID" -o "$OUTPUT_PNG" -b transparent -w 2400 -H 1800

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Imagen PNG generada: $OUTPUT_PNG${NC}"
else
    echo -e "${RED}Error al generar PNG${NC}"
fi

# Generar SVG
echo -e "${GREEN}Generando SVG...${NC}"
mmdc -i "$TEMP_MERMAID" -o "$OUTPUT_SVG" -b transparent

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Imagen SVG generada: $OUTPUT_SVG${NC}"
else
    echo -e "${RED}Error al generar SVG${NC}"
fi

# Limpiar archivo temporal
rm "$TEMP_MERMAID"

echo -e "${GREEN}¡Proceso completado!${NC}"



