const appName = "nmc_welcome_popup";
const appVersion = "1.1.1";
window.onload = function() {
  OCA.NMC_Welcome_Popup = OCA.NMC_Welcome_Popup || {};
  var aboutEntry = document.querySelector("#nmc_welcome_popup-about button");
  if (aboutEntry) {
    aboutEntry.addEventListener("click", function(event) {
      event.stopPropagation();
      event.preventDefault();
      OCP.Loader.loadScript("nmc_welcome_popup", "nmc_welcome_popup-main.mjs").then(function() {
        OCA.NMC_Welcome_Popup.NcModal.open();
        OC.hideMenus(function() {
          return false;
        });
      });
      return true;
    });
  }
};
//# sourceMappingURL=nmc_welcome_popup-about.mjs.map
