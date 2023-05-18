window.ZS = {
  ...window.ZS,
  Anniversary: {
    openEmbed: (element) => {
      const node = element?.closest(".anniversary-timeline__node");
      const overlay = node?.querySelector(
        ".anniversary-timeline__embed-overlay"
      );

      if (!overlay) {
        return;
      }

      const article = overlay.querySelector(
        ".anniversary-timeline__embed-overlay__article"
      );
      if (article) {
        article.scrollTop = 0;
      }

      const body = document.querySelector("body");
      body.classList.add("overflow-hidden");

      overlay.classList.add("anniversary-timeline__embed-overlay--open");
    },
    closeEmbed: (element) => {
      const overlay = element?.closest(".anniversary-timeline__embed-overlay");

      if (!overlay) {
        return;
      }

      const body = document.querySelector("body");
      body.classList.remove("overflow-hidden");

      overlay.classList.remove("anniversary-timeline__embed-overlay--open");
    },
  },
};
