import fs from 'fs';
import path from 'path';
import { execSync } from 'child_process';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const outputDir = path.join(__dirname, 'output');
if (!fs.existsSync(outputDir)) {
    fs.mkdirSync(outputDir, { recursive: true });
}

const outputFile = path.join(outputDir, 'Manual-Book-WMS-LAF-Project.pdf');
const htmlFile = path.join(__dirname, 'combined.html');

console.log('Generating PDF using Edge headless...');
const cmd = `"C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe" --headless --disable-gpu --no-pdf-header-footer --print-to-pdf="${outputFile}" "${htmlFile}"`;

try {
    const res = execSync(cmd, { cwd: __dirname, encoding: 'utf8' });
    console.log('Result:', res);
} catch (e) {
    console.error('Error:', e.message);
}

if (fs.existsSync(outputFile)) {
    const stats = fs.statSync(outputFile);
    console.log(`SUCCESS! PDF created at ${outputFile}, size: ${stats.size} bytes`);
} else {
    console.error('PDF file was not created!');
}
