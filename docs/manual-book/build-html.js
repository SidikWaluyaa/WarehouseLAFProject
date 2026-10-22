import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const mdContent = fs.readFileSync(path.join(__dirname, 'combined.md'), 'utf8');

// Basic markdown to HTML converter with GitHub style CSS
function markdownToHtml(md) {
    let html = md;
    
    // Headers
    html = html.replace(/^### (.*$)/gim, '<h3>$1</h3>');
    html = html.replace(/^## (.*$)/gim, '<h2>$1</h2>');
    html = html.replace(/^# (.*$)/gim, '<h1>$1</h1>');

    // Bold & Italic
    html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    html = html.replace(/\*(.*?)\*/g, '<em>$1</em>');

    // Images: ![alt](src) -> <img alt="alt" src="src" />
    html = html.replace(/!\[(.*?)\]\((.*?)\)/g, '<div class="img-container"><img alt="$1" src="$2" /><p class="caption">$1</p></div>');

    // Code blocks / inline code
    html = html.replace(/`([^`]+)`/g, '<code>$1</code>');

    // Horizontal Rule
    html = html.replace(/^---$/gim, '<hr>');

    // Tables
    html = html.replace(/^\|(.+)\|$/gim, (match) => {
        const cells = match.split('|').filter(c => c.trim() !== '');
        if (cells.every(c => c.trim().startsWith('-'))) {
            return ''; // separator row
        }
        const cellTags = cells.map(c => `<td>${c.trim()}</td>`).join('');
        return `<tr>${cellTags}</tr>`;
    });
    html = html.replace(/(<tr>.*?<\/tr>\s*)+/gs, (match) => `<table>${match}</table>`);

    // Lists
    html = html.replace(/^\s*-\s+(.*$)/gim, '<li>$1</li>');
    html = html.replace(/(<li>.*?<\/li>\s*)+/gs, (match) => `<ul>${match}</ul>`);

    // Paragraphs
    html = html.replace(/^\n([^\n<].*)/gim, '<p>$1</p>');

    return html;
}

const bodyHtml = markdownToHtml(mdContent);

const fullHtml = `<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manual Book WMS LAF Project</title>
    <style>
        @page {
            size: A4;
            margin: 20mm 15mm 20mm 15mm;
        }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.6;
            color: #1e293b;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
        }
        h1 {
            font-size: 22pt;
            color: #0f172a;
            border-bottom: 2px solid #0284c7;
            padding-bottom: 8px;
            margin-top: 24px;
            page-break-after: avoid;
        }
        h2 {
            font-size: 16pt;
            color: #0369a1;
            margin-top: 20px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 6px;
            page-break-after: avoid;
        }
        h3 {
            font-size: 13pt;
            color: #334155;
            margin-top: 16px;
            page-break-after: avoid;
        }
        p, ul, ol {
            margin-bottom: 12px;
        }
        li {
            margin-bottom: 4px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 16px 0;
            font-size: 9.5pt;
            page-break-inside: avoid;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            text-align: left;
        }
        th {
            background-color: #f1f5f9;
            font-weight: bold;
            color: #0f172a;
        }
        tr:nth-child(even) {
            background-color: #f8fafc;
        }
        code {
            background-color: #f1f5f9;
            color: #0f172a;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: Consolas, monospace;
            font-size: 9pt;
        }
        .img-container {
            text-align: center;
            margin: 20px 0;
            page-break-inside: avoid;
        }
        img {
            max-width: 95%;
            height: auto;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }
        .caption {
            font-size: 9pt;
            color: #64748b;
            font-style: italic;
            margin-top: 6px;
        }
        hr {
            border: 0;
            height: 1px;
            background: #e2e8f0;
            margin: 24px 0;
        }
    </style>
</head>
<body>
    ${bodyHtml}
</body>
</html>`;

fs.writeFileSync(path.join(__dirname, 'combined.html'), fullHtml, 'utf8');
console.log('HTML generated successfully, length:', fullHtml.length);
