/**
 * Industries Preview Interactive Auto-Loop & Scanner Slider
 *
 * Coordinates the interactive scanner slider (isometric buildings passing under a fixed
 * central viewfinder) with the right-hand industry list.
 *
 * - Left: Scanner slider with fixed center viewfinder frame and continuous smooth building loop.
 * - Right: Industry list with auto-looping active indicators and clickable anchor navigation.
 * - Mobile & Touch: Fluid 1:1 finger swipe & horizontal drag on the scanner.
 *   As the user drags their finger horizontally, the icons move in real-time under the fixed
 *   central viewfinder, and the industry currently in focus under the viewfinder is immediately
 *   selected in the list below.
 * - Supports momentum flick, tap-to-center, hover pause, mouse drag, and viewport visibility pausing.
 */
(function() {
	'use strict';

	function initIndustriesPreviewLoop() {
		const section = document.querySelector('.ocx-industries-preview__section');
		const list = document.querySelector('.ocx-industries-preview__list');
		const scanner = document.querySelector('.ocx-industries-scanner');
		const track = document.getElementById('ocxIndustriesScannerTrack');

		if (!section || !list || !scanner || !track) return;

		const listItems = Array.from(list.querySelectorAll('.ocx-industries-preview__item'));
		if (!listItems.length) return;

		const N = listItems.length; // 6
		const ITEM_WIDTH = 154;
		const SET_COUNT = 5;
		const MIDDLE_SET = 2; // Set 2 is the middle set (0, 1, 2, 3, 4)
		const INTERVAL_MS = 3400; // 3.4 seconds auto-advance

		let currentIndex = listItems.findIndex(item => item.classList.contains('ocx-industries-preview__item--active'));
		if (currentIndex === -1) {
			currentIndex = 0;
			listItems[0].classList.add('ocx-industries-preview__item--active');
		}

		let trackIndex = (MIDDLE_SET * N) + currentIndex;
		let loopInterval = null;
		let resumeTimeout = null;
		let snapNormalizeTimer = null;
		let isUserHovering = false;
		let isSectionVisible = true;
		let isTransitioning = false;

		// Pointer & Touch drag state
		let isPointerDown = false;
		let isDragging = false;
		let dragStartX = 0;
		let dragStartY = 0;
		let dragStartTrackX = 0;
		let lastDragX = 0;
		let lastDragTime = 0;
		let dragVelocity = 0;

		// Build 5 Cloned Sets for Seamless Infinite Scrolling
		let allTrackItems = [];
		const baseItems = Array.from(track.querySelectorAll('.ocx-industries-scanner__item'));
		if (baseItems.length > 0) {
			track.innerHTML = '';
			for (let s = 0; s < SET_COUNT; s++) {
				baseItems.forEach((item, idx) => {
					const clone = item.cloneNode(true);
					clone.dataset.set = String(s);
					clone.dataset.index = String(idx);
					track.appendChild(clone);
				});
			}
			allTrackItems = Array.from(track.querySelectorAll('.ocx-industries-scanner__item'));
		}

		function getItemWidth() {
			if (allTrackItems.length && allTrackItems[0].offsetWidth > 0) {
				return allTrackItems[0].offsetWidth;
			}
			return ITEM_WIDTH;
		}

		function getCenterOffset() {
			const itemW = getItemWidth();
			return (scanner.offsetWidth / 2) - (itemW / 2);
		}

		function getCurrentTrackX() {
			const style = window.getComputedStyle(track);
			const transform = style.transform || style.webkitTransform;
			if (transform && transform !== 'none') {
				if (typeof DOMMatrixReadOnly !== 'undefined') {
					const matrix = new DOMMatrixReadOnly(transform);
					return matrix.m41;
				}
				const match = transform.match(/matrix(?:3d)?\((.+)\)/);
				if (match) {
					const values = match[1].split(', ');
					return parseFloat(values.length === 16 ? values[12] : values[4]) || 0;
				}
			}
			const centerOffset = getCenterOffset();
			return centerOffset - (trackIndex * getItemWidth());
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

		function updateActiveVisuals(targetTrackIdx, baseIdx) {
			if (allTrackItems.length) {
				allTrackItems.forEach((el, i) => {
					if (i === targetTrackIdx) {
						el.classList.add('ocx-industries-scanner__item--active');
					} else {
						el.classList.remove('ocx-industries-scanner__item--active');
					}
				});
			}

			const hud = scanner.querySelector('#ocxScannerHud');
			if (hud) {
				hud.textContent = `IND // 0${baseIdx + 1}`;
			}

			syncListItems(baseIdx);
		}

		function updateScannerPosition(animate = true, duration = 450) {
			const centerOffset = getCenterOffset();
			const itemW = getItemWidth();
			const x = centerOffset - (trackIndex * itemW);

			if (snapNormalizeTimer) {
				clearTimeout(snapNormalizeTimer);
				snapNormalizeTimer = null;
			}

			if (animate) {
				track.style.transition = `transform ${duration}ms cubic-bezier(0.25, 1, 0.5, 1)`;
				isTransitioning = true;
			} else {
				track.style.transition = 'none';
				isTransitioning = false;
			}

			track.style.transform = `translate3d(${x}px, 0, 0)`;
			updateActiveVisuals(trackIndex, currentIndex);

			if (animate) {
				snapNormalizeTimer = setTimeout(() => {
					isTransitioning = false;
					// Normalize back into middle set seamlessly
					const currentSet = Math.floor(trackIndex / N);
					if (currentSet !== MIDDLE_SET) {
						trackIndex = (MIDDLE_SET * N) + currentIndex;
						const normX = getCenterOffset() - (trackIndex * getItemWidth());
						track.style.transition = 'none';
						track.style.transform = `translate3d(${normX}px, 0, 0)`;
						updateActiveVisuals(trackIndex, currentIndex);
					}
				}, duration + 20);
			}
		}

		function setActiveIndex(nextIndex, isSequential = false) {
			if (nextIndex < 0 || nextIndex >= N) return;

			currentIndex = nextIndex;

			if (isSequential) {
				trackIndex++;
				updateScannerPosition(true, 550);
			} else {
				trackIndex = (MIDDLE_SET * N) + currentIndex;
				updateScannerPosition(true, 450);
			}
		}

		function nextSlide() {
			if (isUserHovering || !isSectionVisible || isDragging || isPointerDown) return;
			const next = (currentIndex + 1) % N;
			setActiveIndex(next, true);
		}

		function prevSlide() {
			if (isDragging || isPointerDown) return;
			currentIndex = (currentIndex - 1 + N) % N;
			trackIndex--;
			updateScannerPosition(true, 550);
		}

		function startLoop() {
			stopLoop();
			if (!isUserHovering && isSectionVisible && !isDragging && !isPointerDown) {
				loopInterval = setInterval(nextSlide, INTERVAL_MS);
			}
		}

		function stopLoop() {
			if (loopInterval) {
				clearInterval(loopInterval);
				loopInterval = null;
			}
			if (resumeTimeout) {
				clearTimeout(resumeTimeout);
				resumeTimeout = null;
			}
		}

		function startLoopAfterDelay(delay = 6000) {
			stopLoop();
			resumeTimeout = setTimeout(() => {
				resumeTimeout = null;
				if (!isUserHovering && isSectionVisible && !isDragging && !isPointerDown) {
					startLoop();
				}
			}, delay);
		}

		// Initial alignment without transition
		updateScannerPosition(false);

		// Handle Window Resize to maintain accurate centering
		let resizeTimer = null;
		window.addEventListener('resize', () => {
			clearTimeout(resizeTimer);
			resizeTimer = setTimeout(() => {
				updateScannerPosition(false);
			}, 80);
		});

		// -------------------------------------------------------------------------
		// TOUCH & MOUSE DRAG INTERACTION (Fluid 1:1 Tracking & Real-Time Sync)
		// -------------------------------------------------------------------------
		function onDragStart(clientX, clientY) {
			stopLoop();

			if (snapNormalizeTimer) {
				clearTimeout(snapNormalizeTimer);
				snapNormalizeTimer = null;
			}

			// Capture current physical position and halt ongoing transition
			const curX = getCurrentTrackX();
			track.style.transition = 'none';
			track.style.transform = `translate3d(${curX}px, 0, 0)`;
			isTransitioning = false;

			isPointerDown = true;
			isDragging = false;
			dragStartX = clientX;
			dragStartY = clientY;
			dragStartTrackX = curX;
			lastDragX = clientX;
			lastDragTime = performance.now();
			dragVelocity = 0;
		}

		function onDragMove(clientX, clientY, e) {
			if (!isPointerDown) return;

			const deltaX = clientX - dragStartX;
			const deltaY = clientY - dragStartY;

			if (!isDragging) {
				// Deadzone filter before determining gesture intent
				if (Math.hypot(deltaX, deltaY) < 6) return;

				// If gesture is primarily vertical, abort drag to allow normal page scrolling
				if (Math.abs(deltaY) >= Math.abs(deltaX)) {
					isPointerDown = false;
					isDragging = false;
					scanner.classList.remove('is-dragging');
					startLoopAfterDelay(4000);
					return;
				}

				// Horizontal intent locked in
				isDragging = true;
				scanner.classList.add('is-dragging');
			}

			if (isDragging) {
				if (e && e.cancelable) {
					e.preventDefault();
				}

				const now = performance.now();
				const dt = now - lastDragTime;
				if (dt > 10) {
					dragVelocity = (clientX - lastDragX) / dt;
					lastDragX = clientX;
					lastDragTime = now;
				}

				const itemW = getItemWidth();
				const centerOffset = getCenterOffset();
				const setW = N * itemW;
				let currentTrackX = dragStartTrackX + deltaX;

				// Infinite virtual wrapping during drag: prevents ever running out of items
				const middleX = centerOffset - (MIDDLE_SET * setW);
				while (currentTrackX - middleX > setW) {
					currentTrackX -= setW;
					dragStartTrackX -= setW;
				}
				while (middleX - currentTrackX > setW) {
					currentTrackX += setW;
					dragStartTrackX += setW;
				}

				track.style.transition = 'none';
				track.style.transform = `translate3d(${currentTrackX}px, 0, 0)`;

				// Calculate item currently passing under the central fixed viewfinder in real-time
				const currentFloat = (centerOffset - currentTrackX) / itemW;
				const nearestTrackIdx = Math.round(currentFloat);
				const nearestBaseIdx = ((nearestTrackIdx % N) + N) % N;

				if (nearestBaseIdx !== currentIndex) {
					currentIndex = nearestBaseIdx;
					syncListItems(currentIndex);
				}

				updateActiveVisuals(nearestTrackIdx, currentIndex);
			}
		}

		function onDragEnd(clientX, e) {
			if (!isPointerDown && !isDragging) return;

			const wasDragging = isDragging;
			isPointerDown = false;
			isDragging = false;
			scanner.classList.remove('is-dragging');

			const totalDeltaX = clientX - dragStartX;
			const itemW = getItemWidth();
			const centerOffset = getCenterOffset();
			const currentTrackX = getCurrentTrackX();
			const currentFloat = (centerOffset - currentTrackX) / itemW;
			const nearestTrackIdx = Math.round(currentFloat);

			if (!wasDragging || Math.abs(totalDeltaX) < 6) {
				// Tap gesture: if tapped directly on an icon in the track, center it
				const targetEl = e ? (e.target || (e.changedTouches && e.changedTouches[0] && document.elementFromPoint(clientX, dragStartY))) : null;
				const itemEl = targetEl ? targetEl.closest('.ocx-industries-scanner__item') : null;
				if (itemEl && allTrackItems.includes(itemEl)) {
					const tappedIdx = allTrackItems.indexOf(itemEl);
					trackIndex = tappedIdx;
					currentIndex = ((trackIndex % N) + N) % N;
					updateScannerPosition(true, 420);
				} else {
					trackIndex = nearestTrackIdx;
					currentIndex = ((trackIndex % N) + N) % N;
					updateScannerPosition(true, 320);
				}
			} else {
				// Drag / Swipe gesture with momentum
				let targetIdx = nearestTrackIdx;

				if (dragVelocity < -0.28) { // Fast flick left (advance towards next)
					targetIdx = Math.max(targetIdx, Math.ceil(currentFloat));
					if (targetIdx === Math.round(currentFloat) && currentFloat < targetIdx + 0.18) {
						targetIdx++;
					}
				} else if (dragVelocity > 0.28) { // Fast flick right (retreat towards previous)
					targetIdx = Math.min(targetIdx, Math.floor(currentFloat));
					if (targetIdx === Math.round(currentFloat) && currentFloat > targetIdx - 0.18) {
						targetIdx--;
					}
				}

				trackIndex = targetIdx;
				currentIndex = ((trackIndex % N) + N) % N;
				updateScannerPosition(true, 420);
			}

			if (!isUserHovering) {
				startLoopAfterDelay(6000);
			}
		}

		// Attach Touch Listeners
		scanner.addEventListener('touchstart', (e) => {
			if (e.touches.length !== 1) return;
			const touch = e.touches[0];
			onDragStart(touch.clientX, touch.clientY);
		}, { passive: true });

		window.addEventListener('touchmove', (e) => {
			if (!isPointerDown) return;
			const touch = e.touches[0];
			onDragMove(touch.clientX, touch.clientY, e);
		}, { passive: false });

		window.addEventListener('touchend', (e) => {
			if (!isPointerDown && !isDragging) return;
			const touch = (e.changedTouches && e.changedTouches[0]) ? e.changedTouches[0] : null;
			const clientX = touch ? touch.clientX : lastDragX;
			onDragEnd(clientX, e);
		});

		window.addEventListener('touchcancel', (e) => {
			if (!isPointerDown && !isDragging) return;
			const touch = (e.changedTouches && e.changedTouches[0]) ? e.changedTouches[0] : null;
			const clientX = touch ? touch.clientX : lastDragX;
			onDragEnd(clientX, e);
		});

		// Attach Mouse Listeners (Drag on Desktop)
		scanner.addEventListener('mousedown', (e) => {
			if (e.button !== 0) return;
			onDragStart(e.clientX, e.clientY);
		});

		window.addEventListener('mousemove', (e) => {
			if (!isPointerDown) return;
			onDragMove(e.clientX, e.clientY, e);
		});

		window.addEventListener('mouseup', (e) => {
			if (!isPointerDown && !isDragging) return;
			onDragEnd(e.clientX, e);
		});

		// Hover pause on scanner
		scanner.addEventListener('mouseenter', () => {
			isUserHovering = true;
			stopLoop();
		});

		scanner.addEventListener('mouseleave', () => {
			isUserHovering = false;
			if (!isDragging && !isPointerDown) {
				startLoop();
			}
		});

		// Wheel / trackpad horizontal scrolling
		let wheelDebounceTimer = null;
		scanner.addEventListener('wheel', (e) => {
			if (Math.abs(e.deltaX) > Math.abs(e.deltaY) && Math.abs(e.deltaX) > 12) {
				e.preventDefault();
				stopLoop();
				if (wheelDebounceTimer) clearTimeout(wheelDebounceTimer);
				wheelDebounceTimer = setTimeout(() => {
					if (e.deltaX > 0) {
						nextSlide();
					} else {
						prevSlide();
					}
					startLoopAfterDelay(5000);
				}, 40);
			}
		}, { passive: false });

		// -------------------------------------------------------------------------
		// RIGHT-HAND INDUSTRY LIST INTERACTION
		// -------------------------------------------------------------------------
		listItems.forEach((item, index) => {
			item.addEventListener('mouseenter', () => {
				isUserHovering = true;
				stopLoop();
				setActiveIndex(index, false);
			});

			item.addEventListener('click', (e) => {
				// On mobile/touch: if tapping an inactive item, activate it and center scanner first!
				if (currentIndex !== index) {
					e.preventDefault();
					stopLoop();
					setActiveIndex(index, false);
					startLoopAfterDelay(6000);
					return;
				}

				// If already active and clicked outside the direct link, follow link
				const link = item.querySelector('a');
				if (link && !e.target.closest('a')) {
					e.preventDefault();
					window.location.href = link.href;
				}
			});
		});

		list.addEventListener('mouseleave', () => {
			isUserHovering = false;
			if (!isDragging && !isPointerDown) {
				startLoop();
			}
		});

		// Pause loop when section is outside viewport to save battery/CPU
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
