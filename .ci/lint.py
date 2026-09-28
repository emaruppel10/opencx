#!/usr/bin/env python3
"""Checks estructurales del theme OpenCX (corridos por CI y localmente).

Solo se analizan archivos TRACKEADOS (git ls-files), asi los archivos de entorno
local que estan gitignoreados (wordpress_env/, transcripts, backups, etc.) no se
cuelan. Checks:

- JSON validos: theme.json, .wp-env.json y Variables/*.json
- Llaves y comentarios balanceados en assets/css/*.css
- Balance de comentarios de bloque wp:* en templates/ y parts/
- Guard: rutas de entorno local que estan gitignoreadas no deben estar trackeadas
"""
import json
import re
import subprocess
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
errors = []


def fail(msg):
    errors.append(msg)


def tracked(suffixes):
    out = subprocess.check_output(
        ["git", "ls-files", "--", *[f"*{s}" for s in suffixes]], text=True
    )
    return [ROOT / p for p in out.splitlines() if p]


def check_json(path):
    try:
        json.loads(path.read_text(encoding="utf-8"))
    except (json.JSONDecodeError, UnicodeDecodeError) as exc:
        fail(f"{path.relative_to(ROOT)}: JSON invalido — {exc}")


def check_css(path):
    text = path.read_text(encoding="utf-8")
    braces = text.count("{")
    if braces != text.count("}"):
        fail(f"{path.relative_to(ROOT)}: llaves sin balancear ({braces} '{{' / {text.count('}')} '}}')")
    if text.count("/*") != text.count("*/"):
        fail(f"{path.relative_to(ROOT)}: comentarios '/*' y '*/' desbalanceados")


def check_blocks(path):
    text = path.read_text(encoding="utf-8")
    opens = re.findall(r"<!-- wp:([a-z-]+)", text)
    closes = re.findall(r"<!-- /wp:([a-z-]+)", text)
    self_closing = re.findall(r"<!-- wp:([a-z-]+)\b[^>]*?/-->", text)
    for kind in set(opens):
        opened = opens.count(kind) - self_closing.count(kind)
        closed = closes.count(kind)
        if opened != closed:
            fail(f"{path.relative_to(ROOT)}: bloque wp:{kind} abre {opened} veces y cierra {closed}")
    for kind in set(closes) - set(opens):
        fail(f"{path.relative_to(ROOT)}: cierre wp:{kind} sin apertura")


def guard_gitignored():
    forbidden = [
        "wordpress_env",
        "BACKLOG.md",
        "OpenCX Wireframes V1.0.fig",
        "transcript_head.json",
        "step31.json",
        "restore_plan.json",
        "scratch.py",
        "scratch2.py",
    ]
    listed = subprocess.check_output(["git", "ls-files", "--", *forbidden], text=True).splitlines()
    for name in listed:
        fail(f"{name} esta trackeado en el repo y no debe subirse")


def main():
    for path in tracked([".json"]):
        check_json(path)
    for path in tracked([".css"]):
        check_css(path)
    for path in tracked([".html"]):
        check_blocks(path)
    guard_gitignored()

    if errors:
        print("\n".join(f"[ERROR] {e}" for e in errors), file=sys.stderr)
        sys.exit(1)

    n_json = len(tracked([".json"]))
    n_css = len(tracked([".css"]))
    n_html = len(tracked([".html"]))
    print(f"OK — {n_json} JSON validos, {n_css} archivos CSS con llaves balanceadas, "
          f"{n_html} plantillas con bloques wp: balanceados, guardias git OK")


if __name__ == "__main__":
    main()