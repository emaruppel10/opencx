/**
 * Industries Preview Interactive Auto-Loop & Scanner Slider
 *
 * Coordinates the interactive scanner slider (isometric buildings passing under a fixed
 * central viewfinder) with the right-hand industry list.
 *
 * - Left: Scanner slider with fixed center viewfinder frame and continuous smooth building loop.
 * - Right: Industry list with auto-looping active indicators and clickable anchor navigation.
 * - Supports hover pause, touch swipe, drag interaction, and viewport visibility pausing.
 */
(function() {
	'use strict';

	function initIndustriesPreviewLoop() {
		const section = document.querySelector('.ocx-industries-preview__section');
		const list = document.querySelector('.ocx-industries-preview__list');
		const scanner = document.querySelector('.ocx-industries-scanner');
		const track = document.getElementById('ocxIndustriesScannerTrack');

		if (!section || !list) return;

		const listItems = Array.from(list.querySelectorAll('.ocx-industries-preview__item'));
		if (!listItems.length) return;

		const N = listItems.length; // 6
		const ITEM_WIDTH = 154;

		let currentIndex = listItems.findIndex(item => item.classList.contains('ocx-industries-preview__item--active'));
		if (currentIndex === -1) {
			currentIndex = 0;
			listItems[0].classList.add('ocx-industries-preview__item--active');
		}

		let trackIndex = N + currentIndex; // Set 1 (middle set)
		let loopInterval = null;
		let isUserHovering = false;
		let isSectionVisible = true;
		let isDragging = false;
		let dragStartX = 0;
		let dragCurrentX = 0;
		let isTransitioning = false;
		const INTERVAL_MS = 3400; // 3.4 seconds per industry

		// Setup Infinite Scanner Track (Set 0 [clones], Set 1 [main], Set 2 [clones])
		let allTrackItems = [];
		if (track) {
			const baseItems = Array.from(track.querySelectorAll('.ocx-industries-scanner__item'));
			if (baseItems.length > 0) {
				track.innerHTML = '';
				for (let s = 0; s < 3; s++) {
					baseItems.forEach((item, idx) => {
						const clone = item.cloneNode(true);
						clone.dataset.set = String(s);
						clone.dataset.index = String(idx);
						track.appendChild(clone);
					});
				}
				allTrackItems = Array.from(track.querySelectorAll('.ocx-industries-scanner__item'));
			}
		}

		function getCenterOffset() {
			if (!scanner) return 0;
			return (scanner.offsetWidth / 2) - (ITEM_WIDTH / 2);
		}

		function updateScannerPosition(animate = true) {
			if (!scanner || !track) return;

			const centerOffset = getCenterOffset();
			const x = centerOffset - (trackIndex * ITEM_WIDTH);

			if (animate) {
				track.style.transition = 'transform 0.75s cubic-bezier(0.25, 1, 0.5, 1)';
				isTransitioning = true;
			} else {
				track.style.transition = 'none';
				isTransitioning = false;
			}

			track.style.transform = `translate3d(${x}px, 0, 0)`;

			if (allTrackItems.length) {
				allTrackItems.forEach((el, i) => {
					if (i === trackIndex) {
						el.classList.add('ocx-industries-scanner__item--active');
					} else {
						el.classList.remove('ocx-industries-scanner__item--active');
					}
				});
			}

			const hud = scanner.querySelector('#ocxScannerHud');
			if (hud) {
				hud.textContent = `IND // 0${currentIndex + 1}`;
			}
		}

		function syncListItems(nextIndex) {
			listItems.forEach((item, idx) => {
				if (idx === nextIndex) {
					item.classList.add('ocx-industries-preview__item--active');
				} else {
					item.classList.remove('ocx-industries-preview__item--active');
				}
			});
		}

		function setActiveIndex(nextIndex, isSequential = false) {
			if (nextIndex < 0 || nextIndex >= N) return;

			currentIndex = nextIndex;
			syncListItems(currentIndex);

			if (isSequential) {
				trackIndex++;
				updateScannerPosition(true);

				// If we have slid into the clone set (Set 2)
				if (trackIndex >= N * 2) {
					setTimeout(() => {
						trackIndex = N + currentIndex;
						updateScannerPosition(false);
					}, 760);
				}
			} else {
				// Direct jump (e.g. user clicked/hovered an industry item)
				trackIndex = N + currentIndex;
				updateScannerPosition(true);
			}
		}

		function nextSlide() {
			if (isUserHovering || !isSectionVisible || isDragging) return;
			const next = (currentIndex + 1) % N;
			setActiveIndex(next, true);
		}

		function prevSlide() {
			if (isDragging) return;
			currentIndex = (currentIndex - 1 + N) % N;
			syncListItems(currentIndex);

			trackIndex--;
			updateScannerPosition(true);

			// If we slid into Set 0
			if (trackIndex < N) {
				setTimeout(() => {
					trackIndex = N + currentIndex;
					updateScannerPosition(false);
				}, 760);
			}
		}

		function startLoop() {
			stopLoop();
			if (!isUserHovering && isSectionVisible && !isDragging) {
				loopInterval = setInterval(nextSlide, INTERVAL_MS);
			}
		}

		function stopLoop() {
			if (loopInterval) {
				clearInterval(loopInterval);
				loopInterval = null;
			}
		}

		// Initial alignment without animation
		updateScannerPosition(false);

		// Window resize handler to maintain exact center alignment
		let resizeTimer = null;
		window.addEventListener('resize', () => {
			clearTimeout(resizeTimer);
			resizeTimer = setTimeout(() => {
				updateScannerPosition(false);
			}, 100);
		});

		// Hover and click interactions on each right-hand list item
		listItems.forEach((item, index) => {
			item.addEventListener('mouseenter', () => {
				isUserHovering = true;
				stopLoop();
				setActiveIndex(index, false);
			});

			item.addEventListener('click', (e) => {
				const link = item.querySelector('a');
				if (!link) return;

				if (!e.target.closest('a')) {
					e.preventDefault();
					window.location.href = link.href;
				}
			});
		});

		list.addEventListener('mouseleave', () => {
			isUserHovering = false;
			startLoop();
		});

		// Scanner Container Hover & Touch / Drag Interaction
		if (scanner && track) {
			scanner.addEventListener('mouseenter', () => {
				isUserHovering = true;
				stopLoop();
			});

			scanner.addEventListener('mouseleave', () => {
				isUserHovering = false;
				startLoop();
			});

			// Touch & Mouse Dragging
			function onDragStart(e) {
				if (isTransitioning) return;
				isDragging = true;
				dragStartX = e.type.includes('touch') ? e.touches[0].clientX : e.clientX;
				dragCurrentX = dragStartX;
				stopLoop();
			}

			function onDragMove(e) {
				if (!isDragging) return;
				dragCurrentX = e.type.includes('touch') ? e.touches[0].clientX : e.clientX;
			}

			function onDragEnd() {
				if (!isDragging) return;
				isDragging = false;
				const deltaX = dragCurrentX - dragStartX;

				if (Math.abs(deltaX) > 40) {
					if (deltaX < 0) {
						nextSlide();
					} else {
						prevSlide();
					}
				} else {
					updateScannerPosition(true);
				}

				if (!isUserHovering) {
					startLoop();
				}
			}

			track.addEventListener('mousedown', onDragStart);
			window.addEventListener('mousemove', onDragMove);
			window.addEventListener('mouseup', onDragEnd);

			track.addEventListener('touchstart', onDragStart, { passive: true });
			track.addEventListener('touchmove', onDragMove, { passive: true });
			track.addEventListener('touchend', onDragEnd);
		}

		// Pause loop when section is outside viewport to save CPU / battery
		if ('IntersectionObserver' in window) {
			const observer = new IntersectionObserver((entries) => {
				entries.forEach((entry) => {
					isSectionVisible = entry.isIntersecting;
					if (isSectionVisible) {
						startLoop();
					} else {
						stopLoop();
					}
				});
			}, { threshold: 0.15 });

			observer.observe(section);
		} else {
			startLoop();
		}

		// Handle tab visibility change
		document.addEventListener('visibilitychange', () => {
			if (document.hidden) {
				stopLoop();
			} else if (isSectionVisible && !isUserHovering) {
				startLoop();
			}
		});

		startLoop();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initIndustriesPreviewLoop);
	} else {
		initIndustriesPreviewLoop();
	}
})();
