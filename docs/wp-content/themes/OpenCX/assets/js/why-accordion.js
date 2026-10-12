/**
 * Why OpenCX Accordion Interaction
 *
 * Allows expanding and collapsing timeline items with smooth interaction.
 */
document.addEventListener('DOMContentLoaded', () => {
	const items = document.querySelectorAll('.ocx-why__item');
	if (!items.length) return;

	items.forEach(item => {
		item.addEventListener('click', (e) => {
			if (e.target.closest('a')) return;

			const wasActive = item.classList.contains('is-active');

			// Close all other items in the same list or section
			items.forEach(other => {
				if (other !== item) {
					other.classList.remove('is-active');
				}
			});

			// Toggle clicked item
			if (wasActive) {
				item.classList.remove('is-active');
			} else {
				item.classList.add('is-active');
			}
		});
	});
});
