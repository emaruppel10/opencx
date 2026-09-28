#!/usr/bin/env python3
"""Build preview estático de / y /ecosystem/ para GitHub Pages.

Descarga el HTML renderizado del WordPress local (localhost:8888) y todos sus
assets (css/js/imagenes de wp-content y wp-includes), y reescribe las URLs
absolutas a relativas para que el sitio funcione bajo la raiz de GitHub Pages
(<owner>.github.io/opencx/). El resultado se escribe en docs/.

Uso:
    python3 tools/build-preview.py            # requiere WP local en localhost:8888
"""
import os
import re
import shutil
import sys
from pathlib import Path
from urllib.parse import urlsplit
from urllib.request import urlopen

BASE = "http://localhost:8888"
REPO = Path(__file__).resolve().parent.parent
OUT = REPO / "docs"

PAGES = {
    "home": ("/", OUT / "index.html"),
    "ecosystem": ("/ecosystem/", OUT / "ecosystem" / "index.html"),
}

# rutas internas que NO son assets ni pages exportadas -> link muerto controlado
DEAD_LINK = "#"

ASSET_ATTR = re.compile(r"(\b(?:href|src|srcset)=)([\"'])(.*?)\2")
ANY_ASSET_URL = re.compile(r"http://localhost:8888(?P<path>/wp-(?:content|includes)/[^\s'\"<>]+)")


def fetch(url):
    req = urlopen(url, timeout=30)
    data = req.read()
    return data.decode("utf-8", errors="replace")


def classify(path):
    """Devuelve la ruta de salida (relativa a OUT) de una URL de sitio, o None."""
    if path in ("", "/", "/index.html"):
        return "index.html"
    if path in ("/ecosystem", "/ecosystem/"):
        return "ecosystem/index.html"
    if path.startswith("/wp-content/") or path.startswith("/wp-includes/"):
        return path.lstrip("/")
    return None


def rewrite_url(value, from_dir):
    """Reescribe una URL (single o srcset) segun de donde se referencia."""
    if value.startswith(BASE + "/"):
        path = value[len(BASE):]
    elif value.startswith("/"):
        path = value
    else:
        return value
    target = classify(path)
    if target is None:
        return DEAD_LINK
    rel = str(Path(os.path.relpath(OUT / target, from_dir)))
    if rel == ".":
        rel = "index.html"
    return rel


def scan_and_rewrite(html, from_dir):
    """Reescribe TODAS las referencias locales (atributos, url() en <style>,
    import maps...) y colecta los assets a descargar (desde el HTML original)."""
    assets = set()

    def is_asset(path):
        return path.startswith("/wp-content/") or path.startswith("/wp-includes/")

    for _, _, value in ASSET_ATTR.findall(html):
        for u in value.split(","):
            path = urlsplit(u.strip().split()[0]).path
            if is_asset(path):
                assets.add(path)
    for m in ANY_ASSET_URL.finditer(html):
        path = urlsplit(m.group("path")).path
        if is_asset(path):
            assets.add(path)

    def repl(m):
        if m.group(1).rstrip("=") == "srcset":
            parts = []
            for part in m.group(3).split(","):
                part = part.strip()
                if not part:
                    continue
                url = part.split()[0]
                if url.startswith(BASE) or url.startswith("/"):
                    suffix = part[len(url):]
                    parts.append(rewrite_url(url, from_dir) + suffix)
                else:
                    parts.append(part)
            return f"{m.group(1)}{m.group(2)}{', '.join(parts)}{m.group(2)}"
        return f"{m.group(1)}{m.group(2)}{rewrite_url(m.group(3), from_dir)}{m.group(2)}"

    html = ASSET_ATTR.sub(repl, html)

    def repl_any(m):
        path = urlsplit(m.group("path")).path
        if not is_asset(path):
            return m.group(0)
        return str(Path(os.path.relpath(OUT / path.lstrip("/"), from_dir)))

    html = ANY_ASSET_URL.sub(repl_any, html)
    return html, assets


def main():
    shutil.rmtree(OUT, ignore_errors=True)

    assets = set()
    for name, (page_url, dest) in PAGES.items():
        html = fetch(BASE + page_url)
        html, found = scan_and_rewrite(html, dest.parent)
        assets |= found
        dest.parent.mkdir(parents=True, exist_ok=True)
        dest.write_text(html, encoding="utf-8")
        print(f"[page] {name}: {dest.relative_to(REPO)}")

    for path in sorted(assets):
        if path.endswith("/"):
            continue
        out = OUT / path.lstrip("/")
        out.parent.mkdir(parents=True, exist_ok=True)
        try:
            data = urlopen(BASE + path, timeout=60).read()
            out.write_bytes(data)
            print(f"[asset] {path}")
        except Exception as exc:  # noqa: BLE001
            print(f"[warn] no se pudo bajar {path}: {exc}", file=sys.stderr)

    print("\nPreview en:", str(OUT.relative_to(REPO)) + "/")


if __name__ == "__main__":
    main()