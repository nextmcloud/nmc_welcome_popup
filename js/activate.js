document.addEventListener('DOMContentLoaded', function() {
	window.OCP.Loader.loadScript('nmc_welcome_popup', 'nmc_welcome_popup-main.js').then(function() {
		OCA.NMC_Welcome_Popup.open(true);
	});
});
