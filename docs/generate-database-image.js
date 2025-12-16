#!/usr/bin/env node

/**
 * Script Node.js para generar imagen del diagrama de base de datos
 * 
 * Requisitos:
 * - Node.js instalado
 * - @mermaid-js/mermaid-cli instalado: npm install -g @mermaid-js/mermaid-cli
 * 
 * Uso:
 *   node generate-database-image.js
 * 
 * O instala las dependencias localmente:
 *   npm install @mermaid-js/mermaid-cli
 *   npx mmdc -i database-diagram.mmd -o database-diagram.png
 */

const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

const SCRIPT_DIR = __dirname;
const DIAGRAM_FILE = path.join(SCRIPT_DIR, 'database-diagram.md');
const OUTPUT_PNG = path.join(SCRIPT_DIR, 'database-diagram.png');
const OUTPUT_SVG = path.join(SCRIPT_DIR, 'database-diagram.svg');

console.log('📊 Generando imagen del diagrama de base de datos...\n');

// Leer el archivo markdown
let markdownContent;
try {
    markdownContent = fs.readFileSync(DIAGRAM_FILE, 'utf8');
} catch (error) {
    console.error('❌ Error al leer el archivo:', error.message);
    process.exit(1);
}

// Extraer el código Mermaid
const mermaidMatch = markdownContent.match(/```mermaid\n([\s\S]*?)```/);
if (!mermaidMatch) {
    console.error('❌ No se encontró código Mermaid en el archivo');
    process.exit(1);
}

const mermaidCode = mermaidMatch[1];

// Crear archivo temporal con solo el código Mermaid
const tempMermaidFile = path.join(SCRIPT_DIR, 'temp-diagram.mmd');
fs.writeFileSync(tempMermaidFile, mermaidCode);

// Verificar si mmdc está disponible
let mmdcCommand = 'mmdc';
try {
    execSync('which mmdc', { stdio: 'ignore' });
} catch (error) {
    // Intentar con npx si no está instalado globalmente
    try {
        execSync('which npx', { stdio: 'ignore' });
        mmdcCommand = 'npx @mermaid-js/mermaid-cli';
    } catch (npxError) {
        console.error('❌ @mermaid-js/mermaid-cli no está instalado');
        console.log('\n📦 Instala @mermaid-js/mermaid-cli:');
        console.log('   npm install -g @mermaid-js/mermaid-cli');
        console.log('\n🌐 O usa el Mermaid Live Editor:');
        console.log('   https://mermaid.live/');
        fs.unlinkSync(tempMermaidFile);
        process.exit(1);
    }
}

// Generar PNG
console.log('🖼️  Generando PNG...');
try {
    execSync(`${mmdcCommand} -i "${tempMermaidFile}" -o "${OUTPUT_PNG}" -b transparent -w 2400 -H 1800`, {
        stdio: 'inherit'
    });
    console.log(`✅ PNG generado: ${OUTPUT_PNG}\n`);
} catch (error) {
    console.error('❌ Error al generar PNG:', error.message);
}

// Generar SVG
console.log('🖼️  Generando SVG...');
try {
    execSync(`${mmdcCommand} -i "${tempMermaidFile}" -o "${OUTPUT_SVG}" -b transparent`, {
        stdio: 'inherit'
    });
    console.log(`✅ SVG generado: ${OUTPUT_SVG}\n`);
} catch (error) {
    console.error('❌ Error al generar SVG:', error.message);
}

// Limpiar archivo temporal
fs.unlinkSync(tempMermaidFile);

console.log('✨ ¡Proceso completado!');
