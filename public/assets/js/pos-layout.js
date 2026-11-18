/* Keep POS laptops in the full sidebar layout even when Windows scaling makes
   the browser's CSS viewport narrower than the physical laptop screen. */
(function () {
  if (document.documentElement.classList.contains('pos-layout') && window.Helpers) {
    const posBreakpoint = 640;
    const root = document.documentElement;

    window.Helpers.LAYOUT_BREAKPOINT = posBreakpoint;

    const updatePosLayout = function () {
      const isDesktopLayout = window.innerWidth >= posBreakpoint;

      root.classList.toggle('pos-desktop-layout', isDesktopLayout);

      if (isDesktopLayout) {
        root.classList.remove('layout-menu-collapsed', 'layout-menu-expanded');
      }
    };

    updatePosLayout();
    window.addEventListener('resize', updatePosLayout);
  }
})();
