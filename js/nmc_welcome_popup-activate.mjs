const appName = "nmc_welcome_popup";
const appVersion = "1.1.1";
document.addEventListener("DOMContentLoaded", function() {
  OCA.NMC_Welcome_Popup = OCA.NMC_Welcome_Popup || {};
  OCP.Loader.loadScript("nmc_welcome_popup", "nmc_welcome_popup-main.mjs").then(function() {
    OCA.NMC_Welcome_Popup.NcModal.open();
    OC.hideMenus(function() {
      return false;
    });
  });
});
//# sourceMappingURL=nmc_welcome_popup-activate.mjs.map
