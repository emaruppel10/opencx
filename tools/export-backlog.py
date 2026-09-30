#!/usr/bin/env python3
"""Convierte BACKLOG.md a BACKLOG.html y BACKLOG.pdf (formato adjuntable en CRM).

Uso:
    python3 tools/export-backlog.py                 # genera en la raíz del repo
"""
import datetime
import os
import subprocess
import sys
from pathlib import Path

import markdown

REPO = Path(__file__).resolve().parent.parent
SRC = REPO / "BACKLOG.md"
HTML = REPO / "BACKLOG.html"
PDF = REPO / "BACKLOG.pdf"

CSS = """
@page {
    size: A4;
    margin: 14mm 12mm;
}
body {
    font-family: -apple-system, "Segoe UI", Helvetica, Arial, sans-serif;
    font-size: 10.5pt;
    line-height: 1.45;
    color: #1f2328;
    max-width: 900px;
    margin: 0 auto;
    padding: 8px;
}
h1 { font-size: 20pt; border-bottom: 2px solid #2c3e50; padding-bottom: 4px; }
h2 { font-size: 15pt; color: #16324f; border-bottom: 1px solid #d0d7de; margin-top: 22px; }
h3 { font-size: 12.5pt; color: #224f7a; }
code {
    background: #f6f8fa;
    padding: 1px 4px;
    border-radius: 4px;
    font-family: ui-monospace, "SF Mono", Menlo, Consolas, monospace;
    font-size: 9pt;
}
pre {
    background: #f6f8fa;
    border: 1px solid #d0d7de;
    border-radius: 6px;
    padding: 8px;
    overflow-x: auto;
    font-family: ui-monospace, "SF Mono", Menlo, Consolas, monospace;
    font-size: 8.5pt;
    white-space: pre-wrap;
    word-break: break-word;
}
table {
    border-collapse: collapse;
    width: 100%;
    margin: 8px 0;
    font-size: 9pt;
}
th, td {
    border: 1px solid #d0d7de;
    padding: 4px 7px;
    text-align: left;
    vertical-align: top;
}
th { background: #eef1f4; }
blockquote {
    margin: 6px 0 6px 0;
    padding: 6px 12px;
    background: #f6f8fa;
    border-left: 4px solid #57606a;
    color: #444;
}
ul, ol { padding-left: 22px; }
hr { border: none; border-top: 1px solid #d0d7de; margin: 14px 0; }
a { color: #0969da; text-decoration: none; }
.footer { margin-top: 26px; color: #666; font-size: 9pt; border-top: 1px solid #d0d7de; padding-top: 6px; }
"""


def build_html():
    md = markdown.markdown(
        SRC.read_text(encoding="utf-8"),
        extensions=["tables", "fenced_code", "sane_lists"],
    )
    date = datetime.date.today().strftime("%d/%m/%Y")
    now = datetime.datetime.now().strftime("%d/%m/%Y %H:%M")
    return f"""<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Backlog — OpenCX Block Theme (export {date})</title>
<style>{CSS}</style>
</head>
<body>
{md}
<div class="footer">Generado automáticamente desde BACKLOG.md el {now} · OpenCX Block Theme</div>
</body>
</html>
"""


def main():
    html = build_html()
    HTML.write_text(html, encoding="utf-8")
    print(f"[ok] {HTML.relative_to(REPO)} ({HTML.stat().st_size // 1024} KB)")

    chrome = Path("/Applications/Google Chrome.app/Contents/MacOS/Google Chrome")
    if chrome.exists():
        subprocess.run(
            [str(chrome), "--headless", "--disable-gpu", "--no-sandbox",
             f"--print-to-pdf={PDF}", str(HTML)],
            check=False, capture_output=True, timeout=120,
        )
        print(f"[ok] {PDF.relative_to(REPO)} ({PDF.stat().st_size // 1024} KB)")
    else:
        print("[aviso] Chrome no encontrado: solo se generó el HTML", file=sys.stderr)


if __name__ == "__main__":
    main()