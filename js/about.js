document.addEventListener('DOMContentLoaded', function() {
	var aboutEntry = document.querySelector('#expanddiv li[data-id="nmc_welcome_popup-about"] a');
	if (aboutEntry) {
		aboutEntry.addEventListener('click', function (event) {
			event.stopPropagation();
			event.preventDefault();
			OCP.Loader.loadScript('nmc_welcome_popup', 'nmc_welcome_popup-main.js').then(function () {
				OCA.NMC_Welcome_Popup.Modal.open();
				OC.hideMenus(function () {
					return false;
				});
			});
			return true;
		});
	}
});
