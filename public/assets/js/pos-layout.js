/* Keep POS laptops in the full sidebar layout until the screen is genuinely small. */
(function () {
  if (document.documentElement.classList.contains('pos-layout') && window.Helpers) {
    window.Helpers.LAYOUT_BREAKPOINT = 768;
  }
})();
