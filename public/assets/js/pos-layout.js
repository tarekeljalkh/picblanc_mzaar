/* Keep POS laptops in the full sidebar layout even when Windows scaling makes
   the browser's CSS viewport narrower than the physical laptop screen. */
(function () {
  if (document.documentElement.classList.contains('pos-layout') && window.Helpers) {
    const laptopBreakpoint = 640;
    const touchBreakpoint = 768;
    const finePointer = window.matchMedia('(pointer: fine)');

    const updatePosBreakpoint = function () {
      const isLaptopLayout = finePointer.matches && window.innerWidth >= laptopBreakpoint;
      window.Helpers.LAYOUT_BREAKPOINT = isLaptopLayout ? laptopBreakpoint : touchBreakpoint;
    };

    updatePosBreakpoint();
    window.addEventListener('resize', updatePosBreakpoint);
    if (finePointer.addEventListener) {
      finePointer.addEventListener('change', updatePosBreakpoint);
    }
  }
})();
