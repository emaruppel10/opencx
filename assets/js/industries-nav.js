/**
 * Industries Interactive Navigation & Scrollspy
 *
 * Smooth scroll navigation and active state synchronization for the Industries section.
 * Tracks 6 industries: Financial Services, Hospitality, Retail, Education, Healthcare, Manufacturing.
 */
(function() {
	'use strict';

	function initIndustriesNav() {
		var nav = document.querySelector('.ocx-industries__nav');
		var cards = document.querySelectorAll('.ocx-industries__card');
		if (!nav || !cards.length) return;

		var navItems = nav.querySelectorAll('.ocx-industries__nav-item');
		var indicator = nav.querySelector('.ocx-industries__nav-indicator');
		var isProgrammaticScroll = false;
		var scrollTimeout = null;

		function getStickyOffset() {
			var sidebar = document.querySelector('.ocx-industries__sidebar');
			if (window.innerWidth <= 1199.98) {
				var sidebarH = sidebar ? sidebar.offsetHeight : 106;
				return sidebarH + 16;
			}
			return 140;
		}

		// Scroll active pill into view horizontally without touching window vertical scroll
		function scrollNavPill(item) {
			if (!nav || !item || window.innerWidth > 1199.98) return;
			var navRect = nav.getBoundingClientRect();
			var itemRect = item.getBoundingClientRect();
			var currentScrollLeft = nav.scrollLeft;
			var relativeLeft = itemRect.left - navRect.left + currentScrollLeft;
			var targetScrollLeft = relativeLeft - (nav.clientWidth / 2) + (itemRect.width / 2);

			nav.scrollTo({
				left: Math.max(0, targetScrollLeft),
				behavior: 'smooth'
			});
		}

		function setActive(targetId, shouldScrollPill) {
			navItems.forEach(function(item) {
				var href = item.getAttribute('href');
				if (href === '#' + targetId) {
					if (!item.classList.contains('is-active')) {
						item.classList.add('is-active');
						if (indicator && !item.contains(indicator)) {
							item.insertBefore(indicator, item.firstChild);
						}
						if (shouldScrollPill !== false) {
							scrollNavPill(item);
						}
					}
				} else {
					item.classList.remove('is-active');
				}
			});
		}

		// Release programmatic scroll if user manually touches screen or wheels
		function releaseScrollLock() {
			if (isProgrammaticScroll) {
				isProgrammaticScroll = false;
				clearTimeout(scrollTimeout);
			}
		}

		window.addEventListener('wheel', releaseScrollLock, { passive: true });
		window.addEventListener('touchstart', releaseScrollLock, { passive: true });
		window.addEventListener('pointerdown', releaseScrollLock, { passive: true });

		// Smooth scroll click handler
		navItems.forEach(function(item) {
			item.addEventListener('click', function(e) {
				var href = item.getAttribute('href');
				if (href && href.startsWith('#')) {
					var target = document.querySelector(href);
					if (target) {
						e.preventDefault();

						// Prevent scroll listener from hijacking scroll while animating
						isProgrammaticScroll = true;
						clearTimeout(scrollTimeout);
						scrollTimeout = setTimeout(function() {
							isProgrammaticScroll = false;
						}, 850);

						var targetId = href.substring(1);
						setActive(targetId, true);

						var stickyOffset = getStickyOffset();
						var targetRect = target.getBoundingClientRect();
						var currentScrollY = window.pageYOffset || document.documentElement.scrollTop;
						var offsetPosition = targetRect.top + currentScrollY - stickyOffset;

						window.scrollTo({
							top: Math.max(0, Math.round(offsetPosition)),
							behavior: 'smooth'
						});

						if (window.history && window.history.replaceState) {
							window.history.replaceState(null, null, href);
						}
					}
				}
			});
		});

		// Scrollspy synchronization during natural page scroll
		var ticking = false;
		function onScroll() {
			if (isProgrammaticScroll) return;
			if (!ticking) {
				window.requestAnimationFrame(function() {
					if (isProgrammaticScroll) {
						ticking = false;
						return;
					}
					var sidebar = document.querySelector('.ocx-industries__sidebar');
					var isMobile = window.innerWidth <= 1199.98;
					var checkpoint = isMobile ? ((sidebar ? sidebar.offsetHeight : 106) + 24) : 180;

					// If scrolled near bottom of document, activate the last card
					var isBottom = (window.innerHeight + (window.pageYOffset || document.documentElement.scrollTop) >= document.documentElement.scrollHeight - 50);
					var activeId = cards[0].id;

					if (isBottom) {
						activeId = cards[cards.length - 1].id;
					} else {
						for (var i = 0; i < cards.length; i++) {
							var rect = cards[i].getBoundingClientRect();
							if (rect.top <= checkpoint) {
								activeId = cards[i].id;
							} else {
								break;
							}
						}
					}

					if (activeId) {
						setActive(activeId, true);
					}
					ticking = false;
				});
				ticking = true;
			}
		}

		window.addEventListener('scroll', onScroll, { passive: true });

		// Handle direct landing via anchor hash (e.g. /industries/#retail)
		if (window.location.hash) {
			var initialHash = window.location.hash.substring(1);
			var initialCard = document.getElementById(initialHash);
			if (initialCard) {
				setActive(initialHash, true);
				setTimeout(function() {
					var stickyOffset = getStickyOffset();
					var targetRect = initialCard.getBoundingClientRect();
					var currentScrollY = window.pageYOffset || document.documentElement.scrollTop;
					var offsetPosition = targetRect.top + currentScrollY - stickyOffset;
					window.scrollTo({
						top: Math.max(0, Math.round(offsetPosition)),
						behavior: 'smooth'
					});
				}, 120);
			}
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initIndustriesNav);
	} else {
		initIndustriesNav();
	}
})();
