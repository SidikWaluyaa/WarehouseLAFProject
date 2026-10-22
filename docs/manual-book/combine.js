import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const baseDir = __dirname;
const files = [
    'index.md',
    '01-dashboard.md',
    '02-master-data.md',
    '03-audit-stok.md',
    '04-transaksi-gudang.md',
    '05-forecast-analisis.md',
    '06-operasi-gudang.md',
    '07-laporan-dan-audit-log.md',
    '08-manajemen-akses.md'
];

const combinedContent = files.map(filename => {
    return fs.readFileSync(path.join(baseDir, filename), 'utf8');
}).join('\n\n<div style="page-break-after: always;"></div>\n\n');

fs.writeFileSync(path.join(baseDir, 'combined.md'), combinedContent, 'utf8');
console.log('Successfully created combined.md, size:', combinedContent.length, 'bytes');
