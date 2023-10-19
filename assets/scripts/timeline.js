window.ZS = {
  ...window.ZS,
  Timeline: {
    openEmbed: (element) => {
      const node = element?.closest(".timeline__node");
      const overlay = node?.querySelector(".timeline__embed-overlay");

      if (!overlay) {
        return;
      }

      const article = overlay.querySelector(
        ".timeline__embed-overlay__article",
      );
      if (article) {
        article.scrollTop = 0;
      }

      const body = document.querySelector("body");
      body.classList.add("overflow-hidden");

      overlay.classList.add("timeline__embed-overlay--open");
    },
    closeEmbed: (element) => {
      const overlay = element?.closest(".timeline__embed-overlay");

      if (!overlay) {
        return;
      }

      const body = document.querySelector("body");
      body.classList.remove("overflow-hidden");

      overlay.classList.remove("timeline__embed-overlay--open");
    },
  },
};
