/* Keep POS laptops in the full sidebar layout even when Windows scaling makes
   the browser's CSS viewport narrower than the physical laptop screen. */
(function () {
  if (document.documentElement.classList.contains('pos-layout') && window.Helpers) {
    const posBreakpoint = 640;

    window.Helpers.LAYOUT_BREAKPOINT = posBreakpoint;
  }
})();
