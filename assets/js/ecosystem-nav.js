/**
 * Ecosystem AI OS Interactive Navigation & Scrollspy
 *
 * Smooth scroll navigation and active state synchronization for the AI OS section.
 */
(function() {
	'use strict';

	function initEcosystemNav() {
		var nav = document.querySelector('.ocx-aios__nav');
		var blocks = document.querySelectorAll('.ocx-aios__block');
		if (!nav || !blocks.length) return;

		var navItems = nav.querySelectorAll('.ocx-aios__nav-item');
		var icon = nav.querySelector('.ocx-aios__nav-icon');
		var isClickScrolling = false;
		var clickScrollTimeout = null;

		function setActive(targetId, shouldScrollNav) {
			navItems.forEach(function(item) {
				var href = item.getAttribute('href');
				if (href === '#' + targetId) {
					if (!item.classList.contains('is-active')) {
						item.classList.add('is-active');
						if (icon && !item.contains(icon)) {
							item.insertBefore(icon, item.firstChild);
						}
					}
					// Center active item in horizontal nav without scrolling window
					if (shouldScrollNav !== false && window.innerWidth <= 991 && nav) {
						var navRect = nav.getBoundingClientRect();
						var itemRect = item.getBoundingClientRect();
						var scrollLeft = (itemRect.left - navRect.left) + nav.scrollLeft - (nav.clientWidth / 2) + (itemRect.width / 2);
						nav.scrollTo({ left: Math.max(0, scrollLeft), behavior: 'smooth' });
					}
				} else {
					item.classList.remove('is-active');
				}
			});
		}

		// Smooth scroll click handler
		navItems.forEach(function(item) {
			item.addEventListener('click', function(e) {
				var href = item.getAttribute('href');
				if (href && href.startsWith('#')) {
					var target = document.querySelector(href);
					if (target) {
						e.preventDefault();
						isClickScrolling = true;
						if (clickScrollTimeout) {
							clearTimeout(clickScrollTimeout);
						}

						var targetId = href.substring(1);
						setActive(targetId, true);

						var sidebar = document.querySelector('.ocx-aios__sidebar');
						var isMobile = window.innerWidth <= 991;
						var offset = (isMobile && sidebar) ? sidebar.offsetHeight : 140;
						var targetTop = window.scrollY + target.getBoundingClientRect().top - offset;

						window.scrollTo({
							top: Math.max(0, Math.round(targetTop)),
							behavior: 'smooth'
						});

						if (history.pushState) {
							history.pushState(null, null, href);
						}

						clickScrollTimeout = setTimeout(function() {
							isClickScrolling = false;
						}, 850);
					}
				}
			});
		});

		// Scrollspy synchronization during manual page scroll
		var ticking = false;
		function onScroll() {
			if (isClickScrolling) return;
			if (!ticking) {
				window.requestAnimationFrame(function() {
					if (isClickScrolling) {
						ticking = false;
						return;
					}
					var sidebar = document.querySelector('.ocx-aios__sidebar');
					var isMobile = window.innerWidth <= 991;
					var offset = (isMobile && sidebar) ? (sidebar.offsetHeight + 40) : 180;

					var currentId = null;
					blocks.forEach(function(block) {
						var rect = block.getBoundingClientRect();
						if (rect.top <= offset && rect.bottom > offset) {
							currentId = block.id;
						}
					});

					if (currentId) {
						setActive(currentId, true);
					}
					ticking = false;
				});
				ticking = true;
			}
		}

		window.addEventListener('scroll', onScroll, { passive: true });
	}

	function initTableSwipeHints() {
		var hints = document.querySelectorAll('.ocx-eco-swipe-hint');
		if (!hints.length) return;

		hints.forEach(function(hint) {
			var card = hint.closest('.ocx-eco-compare__card, .ocx-eco-llm__card');
			if (!card) return;
			var wrap = card.querySelector('.ocx-eco-compare__table-wrap, .ocx-eco-llm__table-wrap');
			var pill = hint.querySelector('.ocx-eco-swipe-hint__pill');
			if (!wrap) return;

			// Fade out hint when user scrolls to reveal OpenCX, reappear if scrolled back
			wrap.addEventListener('scroll', function() {
				if (wrap.scrollLeft > 30) {
					hint.classList.add('is-scrolled');
				} else {
					hint.classList.remove('is-scrolled');
				}
			}, { passive: true });

			// Tap pill to smooth scroll between OpenCX and start
			if (pill) {
				pill.addEventListener('click', function(e) {
					e.preventDefault();
					var maxScroll = wrap.scrollWidth - wrap.clientWidth;
					var target = wrap.scrollLeft > 30 ? 0 : maxScroll;
					wrap.scrollTo({ left: target, behavior: 'smooth' });
				});
			}
		});
	}

	function initAll() {
		initEcosystemNav();
		initTableSwipeHints();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initAll);
	} else {
		initAll();
	}
})();
