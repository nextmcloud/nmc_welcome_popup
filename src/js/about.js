window.onload = function() { // can also use window.addEventListener('load', (event) => {
	OCA.NMC_Welcome_Popup = OCA.NMC_Welcome_Popup || {};

	var aboutEntry = document.querySelector('a#nmc_welcome_popup-about');

	if (aboutEntry) {
		aboutEntry.addEventListener('click', function (event) {
			event.stopPropagation();
			event.preventDefault();
			OCP.Loader.loadScript('nmc_welcome_popup', 'nmc_welcome_popup-main.mjs').then(function () {
				OCA.NMC_Welcome_Popup.NcModal.open();
				OC.hideMenus(function () {
					return false;
				});
			});
			return true;
		});
	}
};
