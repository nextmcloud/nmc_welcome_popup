document.addEventListener('DOMContentLoaded', function() {
	OCA.NMC_Welcome_Popup = OCA.NMC_Welcome_Popup || {};
	window.OCP.Loader.loadScript('nmc_welcome_popup', 'nmc_welcome_popup-main.js').then(function() {
		OCA.NMC_Welcome_Popup.Modal.open(true);
	});
});
