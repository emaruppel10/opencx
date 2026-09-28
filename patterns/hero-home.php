<?php
/**
 * Title: Hero — Home
 * Slug: opencx/hero-home
 * Categories: banner
 * Description: Hero de la portada: logotipo, campo de prompt y selector de industria.
 * Keywords: hero, home, prompt, industria
 * Viewport Width: 1440
 *
 * NOTA: el hero de la portada vive ahora en templates/front-page.html, no en
 * este pattern. El contenido de un `wp:pattern` dentro de un template no se
 * puede editar en el Site Editor, y el campo de prompt necesita ser editable.
 * Este archivo se mantiene solo como copia reutilizable para el inserter de
 * patrones. El markup es identico al del template a proposito: si cambia el
 * hero, copia de nuevo el bloque `ocx-hero` de templates/front-page.html
 * aqui. No uses PHP para resolver URLs o assets: el cuerpo de un pattern si
 * se evalua como PHP, pero las plantillas .html de un block theme no, y esa
 * diferencia ya rompio el markup una vez.
 *
 * @package OpenCX
 */

?>
<!-- wp:group {"alignFullWidth":true,"className":"ocx-hero","style":{"spacing":{"padding":{"top":"80px","bottom":"51px","left":"64px","right":"64px"},"blockGap":"80px"},"color":{"background":"#ffffff"}},"layout":{"type":"default"}} -->
<div class="wp-block-group alignfull ocx-hero has-background" style="padding-top:80px;padding-right:64px;padding-bottom:51px;padding-left:64px;background-color:var(--Color-White)">

	<!-- wp:group {"className":"ocx-hero__frame","style":{"border":{"width":"1px","color":"#d8d8d8","radius":"16px"},"dimensions":{"minHeight":"413px"}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"center","verticalAlignment":"center"}} -->
	<div class="wp-block-group ocx-hero__frame" style="border-color:var(--Color-Neutral-Lighter);border-radius:16px;border-width:1px;min-height:413px">

		<!-- wp:group {"className":"ocx-hero__actions","style":{"spacing":{"blockGap":"16px"}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"left"}} -->
		<div class="wp-block-group ocx-hero__actions">

			<!-- wp:site-logo {"className":"ocx-hero__logo","width":147} /-->

			<!-- wp:group {"className":"ocx-hero__form","style":{"position":{"type":"relative"},"spacing":{"blockGap":"0px"}},"dimensions":{"minHeight":"209px"}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"space-between"}} -->
			<div class="wp-block-group ocx-hero__form" style="position:relative;background-color:var(--Color-OpenCX-White-Lightest);border-radius:var(--Radius-Medium);min-height:209px">

				<!-- wp:paragraph {"className":"ocx-hero__field"} -->
				<p class="ocx-hero__field">Ask about a challenge in your customer or employee operations, and see how OpenCX would approach it.</p>
				<!-- /wp:paragraph -->

				<!-- wp:group {"className":"ocx-hero__controls","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between","verticalAlignment":"center"}} -->
				<div class="wp-block-group ocx-hero__controls">

					<!-- wp:button {"className":"ocx-hero__industry","style":{"position":{"type":"absolute","inset":{"top":"137px","left":"24.5px"}}}} -->
					<div class="wp-block-button ocx-hero__industry" style="position:absolute;top:137px;left:24.5px"><a class="wp-block-button__link wp-element-button" href="/industries/">Industry <span aria-hidden="true" class="ocx-hero__caret">&#9662;</span></a></div>
					<!-- /wp:button -->

					<!-- wp:button {"className":"ocx-hero__submit","style":{"position":{"type":"absolute","inset":{"top":"169px","left":"732px"}}}} -->
					<div class="wp-block-button ocx-hero__submit" style="position:absolute;top:169px;left:732px"><a class="wp-block-button__link wp-element-button" role="button" aria-label="Enviar consulta" href="/contact/"><img class="ocx-hero__arrow" src="/wp-content/themes/OpenCX/assets/images/icon-arrow-submit.svg" alt="" width="24" height="24" /></a></div>
					<!-- /wp:button -->

				</div>
				<!-- /wp:group -->

			</div>
			<!-- /wp:group -->

		</div>
		<!-- /wp:group -->

	</div>
	<!-- /wp:group -->

</div>
<!-- /wp:group -->
