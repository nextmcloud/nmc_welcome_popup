/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!****************************!*\
  !*** ./src/js/activate.js ***!
  \****************************/
document.addEventListener('DOMContentLoaded', function () {
  OCA.NMC_Welcome_Popup = OCA.NMC_Welcome_Popup || {};
  window.OCP.Loader.loadScript('nmc_welcome_popup', 'nmc_welcome_popup-main.js').then(function () {
    OCA.NMC_Welcome_Popup.NcModal.open(true);
  });
});
/******/ })()
;
//# sourceMappingURL=nmc_welcome_popup-activate.js.map?v=7d05ed25e5c9ce955427