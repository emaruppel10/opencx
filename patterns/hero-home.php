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

					<!-- wp:button {"className":"ocx-hero__industry"} -->
					<div class="wp-block-button ocx-hero__industry ocx-hero__industry-picker">
						<button type="button" class="wp-block-button__link wp-element-button ocx-hero__industry-btn" id="ocxHeroIndustryBtn" aria-expanded="false" aria-haspopup="dialog" aria-controls="ocxHeroIndustryPopover">
							<span class="ocx-hero__industry-icon-slot" aria-hidden="true"></span>
							<span class="ocx-hero__industry-label">Industry</span>
							<img class="ocx-hero__plus-icon" src="/wp-content/themes/OpenCX/assets/images/icon-plus.svg" alt="+" width="14" height="14" />
							<span class="ocx-hero__industry-clear" id="ocxHeroIndustryClear" role="button" aria-label="Clear industry selection" tabindex="0" title="Clear selection">×</span>
						</button>

						<!-- Floating Industry Capsules Tray (Option 1) -->
						<div class="ocx-hero__industry-popover" id="ocxHeroIndustryPopover" role="dialog" aria-label="Choose Industry" aria-hidden="true">
							<div class="ocx-hero__industry-popover-header">
								<span class="ocx-hero__industry-popover-title">Industry Context</span>
								<span class="ocx-hero__industry-popover-hint">Select a sector to tailor OpenCX AI</span>
							</div>
							<div class="ocx-hero__industry-chips" role="listbox">
								<button type="button" class="ocx-hero__industry-chip" role="option" data-industry="financial-services" data-label="Financial Services" data-icon="/wp-content/themes/OpenCX/assets/images/industry-icon-financial.svg" data-prompt="Ask how OpenCX streamlines dispute resolution, fraud alerts, and compliance workflows in banking.">
									<img src="/wp-content/themes/OpenCX/assets/images/industry-icon-financial.svg" alt="" width="18" height="18" class="ocx-hero__chip-icon" />
									<span class="ocx-hero__chip-text">Financial Services</span>
								</button>
								<button type="button" class="ocx-hero__industry-chip" role="option" data-industry="retail" data-label="Retail" data-icon="/wp-content/themes/OpenCX/assets/images/industry-icon-retail.svg" data-prompt="Ask how OpenCX unifies omnichannel loyalty, inventory visibility, and personalized customer journeys.">
									<img src="/wp-content/themes/OpenCX/assets/images/industry-icon-retail.svg" alt="" width="18" height="18" class="ocx-hero__chip-icon" />
									<span class="ocx-hero__chip-text">Retail</span>
								</button>
								<button type="button" class="ocx-hero__industry-chip" role="option" data-industry="healthcare" data-label="Healthcare" data-icon="/wp-content/themes/OpenCX/assets/images/industry-icon-healthcare.svg" data-prompt="Ask how OpenCX orchestrates patient intake, provider coordination, and HIPAA-compliant care pathways.">
									<img src="/wp-content/themes/OpenCX/assets/images/industry-icon-healthcare.svg" alt="" width="18" height="18" class="ocx-hero__chip-icon" />
									<span class="ocx-hero__chip-text">Healthcare</span>
								</button>
								<button type="button" class="ocx-hero__industry-chip" role="option" data-industry="hospitality" data-label="Hospitality" data-icon="/wp-content/themes/OpenCX/assets/images/industry-icon-hospitality.svg" data-prompt="Ask how OpenCX elevates guest reservations, concierge requests, and real-time loyalty experiences.">
									<img src="/wp-content/themes/OpenCX/assets/images/industry-icon-hospitality.svg" alt="" width="18" height="18" class="ocx-hero__chip-icon" />
									<span class="ocx-hero__chip-text">Hospitality</span>
								</button>
								<button type="button" class="ocx-hero__industry-chip" role="option" data-industry="education" data-label="Education" data-icon="/wp-content/themes/OpenCX/assets/images/industry-icon-education.svg" data-prompt="Ask how OpenCX simplifies student admissions, academic advising, and unified campus operations.">
									<img src="/wp-content/themes/OpenCX/assets/images/industry-icon-education.svg" alt="" width="18" height="18" class="ocx-hero__chip-icon" />
									<span class="ocx-hero__chip-text">Education</span>
								</button>
								<button type="button" class="ocx-hero__industry-chip" role="option" data-industry="manufacturing" data-label="Manufacturing" data-icon="/wp-content/themes/OpenCX/assets/images/industry-icon-manufacturing.svg" data-prompt="Ask how OpenCX accelerates supply chain coordination, predictive maintenance, and vendor workflows.">
									<img src="/wp-content/themes/OpenCX/assets/images/industry-icon-manufacturing.svg" alt="" width="18" height="18" class="ocx-hero__chip-icon" />
									<span class="ocx-hero__chip-text">Manufacturing</span>
								</button>
							</div>
						</div>
						<input type="hidden" name="industry" id="ocxHeroIndustryInput" value="" />
					</div>
					<!-- /wp:button -->

					<div class="ocx-hero__right-tools" style="display:flex;align-items:center;gap:14px;">
						<!-- wp:button {"className":"ocx-hero__mic"} -->
						<div class="wp-block-button ocx-hero__mic"><a class="wp-block-button__link wp-element-button" role="button" aria-label="Microphone" href="#"><img class="ocx-hero__mic-icon" src="/wp-content/themes/OpenCX/assets/images/hero-mic.svg" alt="Mic" width="24" height="24" /></a></div>
						<!-- /wp:button -->

						<!-- wp:button {"className":"ocx-hero__submit"} -->
						<div class="wp-block-button ocx-hero__submit"><a class="wp-block-button__link wp-element-button" role="button" aria-label="Enviar consulta" href="/contact/" id="ocxHeroSubmitBtn"><img class="ocx-hero__arrow" src="/wp-content/themes/OpenCX/assets/images/hero-submit.svg" alt="Submit" width="36" height="36" /></a></div>
						<!-- /wp:button -->
					</div>

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
