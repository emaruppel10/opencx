# OpenCX Testimonials

Manages the cards in the OpenCX "Testimonial / 23 /" slider as a post type, so they are
edited in the admin instead of being hardcoded in the block templates.

## What it stores

| Card field | Where it lives |
| --- | --- |
| Name | Post title |
| Short comment | Post excerpt |
| Full story | Post content |
| Role | `_ocx_testimonial_role` meta |
| Logo | `_ocx_testimonial_logo` meta, attachment ID |
| Avatar | `_ocx_testimonial_avatar` meta, attachment ID |
| Slider order | Post order (`menu_order`) |

The plugin ships no front-end CSS or JS. It renders the theme's existing `.ocx-t23__*`
markup, and the arrows, dots and keyboard support come from the theme's
`testimonial-carousel.js`, which the theme already enqueues on every page.

## The shortcode

```
[opencx_testimonials]
```

Place it inside the `.ocx-t23__track` group in the block template. It emits the cards only,
so the surrounding section, viewport, track, arrows and dots stay in the template where the
carousel script expects them.

Attributes:

| Attribute | Default | What it does |
| --- | --- | --- |
| `order` | `ASC` | Slider order, by `menu_order`. |
| `limit` | all | How many cards to render. |
| `link` | `on` | `off` removes the "Read case study" button from every card. |

Links point at the testimonial permalink by default. Two filters are available when that is
not what you want:

- `opencx_testimonial_card_url` ( `string $url, WP_Post $testimonial` )
- `opencx_testimonial_card_label` ( `string $label, WP_Post $testimonial` )

```php
// Point every card at the customer stories archive instead of the single permalink.
add_filter( 'opencx_testimonial_card_url', function ( $url, $testimonial ) {
	return home_url( '/customer-stories/' );
}, 10, 2 );
```

## The single view

The card permalink (`/testimonials/<slug>/`) is live, but no single template is registered
yet, so those URLs return 404 until one is added. The post content is the reserved slot for
the full story.

## The cards are not shipped

The plugin deliberately creates no demo content, so the slider is empty until testimonials
are added in the admin: **Testimonials → Add New**. The five cards that were hardcoded in the
templates are the ones to recreate there.

## Development

The plugin lives in this repo and is mounted into `wp-env` through `.wp-env.json`:

```json
"plugins": [ "./plugins/opencx-testimonials" ]
```

The `./` is required. Without it `wp-env` reads the value as a GitHub shorthand and tries to
clone `github.com/plugins/opencx-testimonials`.

```
npx @wordpress/env start
```
