const appName = "nmc_welcome_popup";
const appVersion = "1.1.1";
(function($, OC2, _2) {
  $(document).ready(function() {
    initLinkToClipboard();
    $("#endpoint-url").on("click", function() {
      $(this).select();
    });
  });
  function initLinkToClipboard() {
    var originalTitle = t("nmc_welcome_popup", "Copy to clipboard");
    $("#endpoint-url + .clipboardButton").tooltip({
      placement: "bottom",
      title: originalTitle,
      trigger: "hover"
    });
    var clipboard = new Clipboard(".clipboardButton");
    clipboard.on("success", function(e) {
      var $input = $(e.trigger);
      $input.tooltip("hide").attr("data-original-title", t("nmc_welcome_popup", "Copied!")).tooltip("fixTitle").tooltip({
        placement: "bottom",
        trigger: "manual"
      }).tooltip("show");
      _2.delay(function() {
        $input.tooltip("hide").attr("data-original-title", originalTitle).tooltip("fixTitle");
      }, 3e3);
    });
    clipboard.on("error", function(e) {
      var $input = $(e.trigger);
      var actionMsg = "";
      if (/iPhone|iPad/i.test(navigator.userAgent)) {
        actionMsg = t("nmc_welcome_popup", "Not supported!");
      } else if (/Mac/i.test(navigator.userAgent)) {
        actionMsg = t("nmc_welcome_popup", "Press ⌘-C to copy.");
      } else {
        actionMsg = t("nmc_welcome_popup", "Press Ctrl-C to copy.");
      }
      $input.tooltip("hide").attr("data-original-title", actionMsg).tooltip("fixTitle").tooltip({
        placement: "bottom",
        trigger: "manual"
      }).tooltip("show");
      _2.delay(function() {
        $input.tooltip("hide").attr("data-original-title", originalTitle).tooltip("fixTitle");
      }, 3e3);
    });
  }
})(jQuery, OC, _);
//# sourceMappingURL=nmc_welcome_popup-personal-settings.mjs.map
