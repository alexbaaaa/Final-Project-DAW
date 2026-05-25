(() => {
	const toggleButton = document.querySelector(".menu-toggle");
	const navigation = document.querySelector(".site-navigation");

	if (toggleButton && navigation) {
		toggleButton.addEventListener("click", () => {
			const isOpen = navigation.classList.toggle("is-open");
			toggleButton.setAttribute("aria-expanded", isOpen ? "true" : "false");
		});
	}

	const categorySelect = document.querySelector("#news-category");
	const filterForm = document.querySelector(".news-filter");

	if (categorySelect && filterForm) {
		categorySelect.addEventListener("change", () => {
			filterForm.submit();
		});
	}
})();
