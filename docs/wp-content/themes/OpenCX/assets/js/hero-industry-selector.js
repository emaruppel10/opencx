/**
 * OpenCX Hero Industry Capsule Selector
 *
 * Implements an interactive AI-chat-style industry context picker.
 * Clicking "Industry +" displays a floating tray with the 6 OpenCX industry capsules.
 * Selecting an industry transforms the trigger into an active pill tag with a clear (×) button,
 * updates the prompt placeholder with an industry-tailored example, and sets context for future AI queries.
 */
(function() {
	'use strict';

	function initHeroIndustrySelector() {
		var picker = document.querySelector('.ocx-hero__industry-picker');
		if (!picker) return;

		var btn = document.getElementById('ocxHeroIndustryBtn');
		var popover = document.getElementById('ocxHeroIndustryPopover');
		var clearBtn = document.getElementById('ocxHeroIndustryClear');
		var labelSlot = picker.querySelector('.ocx-hero__industry-label');
		var iconSlot = picker.querySelector('.ocx-hero__industry-icon-slot');
		var hiddenInput = document.getElementById('ocxHeroIndustryInput');
		var promptField = document.getElementById('ocxHeroPromptField');
		var submitBtn = document.getElementById('ocxHeroSubmitBtn');
		var chips = picker.querySelectorAll('.ocx-hero__industry-chip');

		if (!btn || !popover) return;

		var defaultPrompt = promptField ? (promptField.getAttribute('data-default') || promptField.textContent.trim()) : '';
		var defaultLabel = 'Industry';
		var defaultSubmitHref = submitBtn ? submitBtn.getAttribute('href') : '/contact/';

		function isOpen() {
			return popover.classList.contains('is-open');
		}

		function openPopover() {
			popover.classList.add('is-open');
			btn.setAttribute('aria-expanded', 'true');
			popover.setAttribute('aria-hidden', 'false');
		}

		function closePopover() {
			popover.classList.remove('is-open');
			btn.setAttribute('aria-expanded', 'false');
			popover.setAttribute('aria-hidden', 'true');
		}

		function togglePopover() {
			if (isOpen()) {
				closePopover();
			} else {
				openPopover();
			}
		}

		function updatePromptText(newText) {
			if (!promptField) return;
			promptField.classList.add('is-changing');
			setTimeout(function() {
				promptField.textContent = newText;
				promptField.classList.remove('is-changing');
			}, 140);
		}

		function selectIndustry(chip) {
			var industrySlug = chip.getAttribute('data-industry');
			var industryLabel = chip.getAttribute('data-label');
			var industryIcon = chip.getAttribute('data-icon');
			var industryPrompt = chip.getAttribute('data-prompt');

			// Highlight selected chip in popover
			chips.forEach(function(c) {
				c.classList.remove('is-selected');
				c.setAttribute('aria-selected', 'false');
			});
			chip.classList.add('is-selected');
			chip.setAttribute('aria-selected', 'true');

			// Update hidden input
			if (hiddenInput) {
				hiddenInput.value = industrySlug;
			}

			// Update trigger button to active pill state
			if (labelSlot) {
				labelSlot.textContent = industryLabel;
			}
			if (iconSlot) {
				iconSlot.innerHTML = '<img src="' + industryIcon + '" alt="" width="18" height="18" />';
			}
			btn.classList.add('has-selection');

			// Update submit link with industry parameter for contact/demo funnel
			if (submitBtn) {
				var baseHref = defaultSubmitHref.split('?')[0];
				submitBtn.setAttribute('href', baseHref + '?industry=' + encodeURIComponent(industrySlug));
			}

			// Smooth cross-fade to tailored prompt text
			if (industryPrompt) {
				updatePromptText(industryPrompt);
			}

			// Close popover
			closePopover();
		}

		function clearSelection(e) {
			if (e) {
				e.stopPropagation();
				e.preventDefault();
			}

			// Reset chips in popover
			chips.forEach(function(c) {
				c.classList.remove('is-selected');
				c.setAttribute('aria-selected', 'false');
			});

			// Reset hidden input
			if (hiddenInput) {
				hiddenInput.value = '';
			}

			// Reset trigger button
			if (labelSlot) {
				labelSlot.textContent = defaultLabel;
			}
			if (iconSlot) {
				iconSlot.innerHTML = '';
			}
			btn.classList.remove('has-selection');

			// Reset submit link
			if (submitBtn) {
				submitBtn.setAttribute('href', defaultSubmitHref);
			}

			// Reset prompt text
			if (defaultPrompt) {
				updatePromptText(defaultPrompt);
			}

			closePopover();
		}

		// Event Listeners
		btn.addEventListener('click', function(e) {
			// Don't toggle if clicking the clear button inside
			if (e.target === clearBtn || (clearBtn && clearBtn.contains(e.target))) {
				return;
			}
			togglePopover();
		});

		if (clearBtn) {
			clearBtn.addEventListener('click', clearSelection);
			clearBtn.addEventListener('keydown', function(e) {
				if (e.key === 'Enter' || e.key === ' ') {
					clearSelection(e);
				}
			});
		}

		chips.forEach(function(chip) {
			chip.addEventListener('click', function(e) {
				e.stopPropagation();
				selectIndustry(chip);
			});
		});

		// Close on click outside
		document.addEventListener('click', function(e) {
			if (!picker.contains(e.target)) {
				closePopover();
			}
		});

		// Keyboard: Close on Escape
		document.addEventListener('keydown', function(e) {
			if (e.key === 'Escape' && isOpen()) {
				closePopover();
				btn.focus();
			}
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initHeroIndustrySelector);
	} else {
		initHeroIndustrySelector();
	}
})();
