# OpenCX — Web Theme de WordPress

Block theme de **OpenCX** construido sobre Full Site Editing, que implementa el
diseño del wireframe de Figma *"OpenCX Wireframes V1.0"*.

Los estilos, tipografías y colores se definen **exclusivamente** en `theme.json` y
en los tokens de `assets/css/tokens.css`; cada componente del wireframe se declara
en HTML como Block Pattern/Group con nombres `ocx-*` y se estiliza en
`assets/css/patterns.css`.

[![CI theme-ci](https://github.com/emaruppel10/opencx/actions/workflows/theme-ci.yml/badge.svg)](https://github.com/emaruppel10/opencx/actions/workflows/theme-ci.yml)

## Estructura

| Ruta             | Contenido                                                  |
| ---------------- | ---------------------------------------------------------- |
| `templates/`     | Plantillas del tema (`front-page`, `page-ecosystem`, `index`) |
| `parts/`         | Template parts: headers y footer                           |
| `patterns/`      | Block patterns registrados en PHP                          |
| `inc/`           | Setup del tema, enqueue, navegación, custom post types     |
| `assets/css/`    | `tokens.css` → valores de diseño; `patterns.css` → patrones |
| `assets/js/`     | Scripts del front (carousel, logo-intro, count-up, nav)    |
| `assets/images/` | Imágenes, íconos y fuentes del tema                        |
| `Variables/`     | Export de variables de diseño desde Figma                  |
| `.ci/`           | Script de checks estructurales del tema                    |

## Requisitos

- WordPress 6.6+ (probado hasta 6.7)
- PHP 8.0+
- `@wordpress/env` (Docker) para el entorno local

## Cómo correrlo

```bash
npx wp-env start    # levanta WordPress en http://localhost:8888
```

La configuración del entorno está en `.wp-env.json` (monta este tema como active
theme y activa `WP_DEBUG`).

## Preview en línea

Para ver las páginas sin instalar nada:

https://emaruppel10.github.io/opencx/preview/          → Home
https://emaruppel10.github.io/opencx/preview/ecosystem/ → Ecosistema

Es una captura estática del HTML renderizado (CSS, JS, fuentes e imágenes
incluidas) que se publica desde la carpeta `preview/` de este repo mediante
GitHub Pages.

Para regenerarla tras nuevos cambios, con el WordPress local corriendo:

```bash
python3 tools/build-preview.py
git add preview/ && git commit -m "preview: actualizar captura estática" && git push
```

## CI / Calidad

Cada push o pull request corre el workflow `theme-ci.yml`:

- `php -l` sobre todos los `.php` del tema.
- `theme.json` y tokens JSON válidos.
- Llaves balanceadas en `assets/css/`.
- Balance de comentarios de bloque `wp:*` en `templates/` y `parts/` (detecta
  secciones mal anidadas antes de que lleguen al navegador).

Para correr los checks localmente:

```bash
python3 .ci/lint.py
```

## Avances

### Home (`/`)

- Hero con buscador de consulta, logo-in con preloader, contadores animados,
  grid de pillars, testimonios, secciones de features y footer completo — con
  medición de fidelidad contra el wireframe (píxeles, tokens y tipografía).

### Ecosistema (`/ecosystem/`)

Secciones implementadas del wireframe:

- [x] **Header / 62 /** — hero *"Not a feature. The foundation."*
- [x] **Layout / 71 /** — intro de dos columnas
- [x] **Layout / 192 /** × 2 — *"AI Orchestrator"* y *"Specialized AI Agents"*
- [x] **Header / 62 /** — *"Four pillars. One AI operating system."*
- [x] **Layout / 19 /** — grilla 2×2 de los cuatro pilares
- [ ] Resto de secciones del wireframe (en curso)

La implementación replica los valores del diseño en los anchos de escritorio y
tablet (gaps, paddings, alturas de sección y familias/tamaños de tipo de la
escala de `theme.json`).