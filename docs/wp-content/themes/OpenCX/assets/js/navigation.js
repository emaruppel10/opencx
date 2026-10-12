/**
 * OpenCX Mobile Navigation & Sandwich Menu Behaviour
 *
 * Handles mobile drawer toggle, ARIA accessibility attributes,
 * keyboard trapping / ESC closure, and document scroll locking.
 */
(function () {
	'use strict';

	function initMobileNav() {
		const toggles = document.querySelectorAll('.ocx-mobile-toggle');
		if (!toggles.length) return;

		toggles.forEach(toggle => {
			const header = toggle.closest('.ocx-header');
			const drawer = header ? header.querySelector('.ocx-mobile-drawer') : document.querySelector('.ocx-mobile-drawer');
			if (!drawer) return;

			function openMenu() {
				toggle.setAttribute('aria-expanded', 'true');
				toggle.classList.add('is-active');
				drawer.classList.add('is-open');
				drawer.setAttribute('aria-hidden', 'false');
				document.body.classList.add('ocx-menu-open');
			}

			function closeMenu() {
				toggle.setAttribute('aria-expanded', 'false');
				toggle.classList.remove('is-active');
				drawer.classList.remove('is-open');
				drawer.setAttribute('aria-hidden', 'true');
				document.body.classList.remove('ocx-menu-open');
			}

			function toggleMenu(e) {
				e.stopPropagation();
				const isExpanded = toggle.getAttribute('aria-expanded') === 'true';
				if (isExpanded) {
					closeMenu();
				} else {
					openMenu();
				}
			}

			toggle.addEventListener('click', toggleMenu);

			// Close when clicking nav links
			const links = drawer.querySelectorAll('a');
			links.forEach(link => {
				link.addEventListener('click', () => {
					closeMenu();
				});
			});

			// Close on ESC key
			document.addEventListener('keydown', e => {
				if (e.key === 'Escape' && drawer.classList.contains('is-open')) {
					closeMenu();
					toggle.focus();
				}
			});

			// Close on resize if returning to desktop
			window.addEventListener('resize', () => {
				if (window.innerWidth >= 992 && drawer.classList.contains('is-open')) {
					closeMenu();
				}
			});
		});

		// Handle Mobile Submenu Accordions
		const mobileDropdownItems = document.querySelectorAll('.ocx-mobile-nav__item--has-dropdown');
		mobileDropdownItems.forEach(item => {
			const toggleBtn = item.querySelector('.ocx-mobile-nav__toggle');
			const submenu = item.querySelector('.ocx-mobile-nav__submenu');
			if (!toggleBtn || !submenu) return;

			toggleBtn.addEventListener('click', (e) => {
				e.preventDefault();
				e.stopPropagation();
				const isOpen = item.classList.contains('is-open');
				if (isOpen) {
					item.classList.remove('is-open');
					toggleBtn.setAttribute('aria-expanded', 'false');
					submenu.setAttribute('aria-hidden', 'true');
				} else {
					item.classList.add('is-open');
					toggleBtn.setAttribute('aria-expanded', 'true');
					submenu.setAttribute('aria-hidden', 'false');
				}
			});
		});

		// Handle Desktop Dropdown Accessibility & Click outside
		const desktopDropdowns = document.querySelectorAll('.ocx-nav-item--has-dropdown');
		desktopDropdowns.forEach(item => {
			const link = item.querySelector('.ocx-nav-link');
			const dropdown = item.querySelector('.ocx-nav-dropdown');
			if (!link || !dropdown) return;

			link.addEventListener('keydown', (e) => {
				if (e.key === 'ArrowDown' || e.key === 'Enter') {
					item.classList.add('is-open');
					link.setAttribute('aria-expanded', 'true');
					const firstLink = dropdown.querySelector('a');
					if (firstLink) firstLink.focus();
				}
			});

			item.addEventListener('mouseenter', () => {
				link.setAttribute('aria-expanded', 'true');
			});

			item.addEventListener('mouseleave', () => {
				link.setAttribute('aria-expanded', 'false');
				item.classList.remove('is-open');
			});
		});

		document.addEventListener('click', (e) => {
			desktopDropdowns.forEach(item => {
				if (!item.contains(e.target)) {
					item.classList.remove('is-open');
					const link = item.querySelector('.ocx-nav-link');
					if (link) link.setAttribute('aria-expanded', 'false');
				}
			});
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initMobileNav);
	} else {
		initMobileNav();
	}
})();
