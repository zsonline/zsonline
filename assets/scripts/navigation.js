window.ZS = {
  ...window.ZS,
  Navigation: {
    Main: new (class {
      constructor() {
        this.body = document.querySelector("body");
        this.menu = document.querySelector("#header__navigation-main");
        this.overlay = document.querySelector(
          "#header__navigation-main__overlay"
        );

        this.menu.style.right = `-${this.menu.clientWidth}px`;
      }
      open() {
        if (!this.menu || !this.overlay) {
          return;
        }

        this.menu.style.right = "0";
        this.menu.scrollTop = 0;

        this.body.classList.add("overflow-hidden");
        this.menu.classList.add("header__navigation-main--open");
        this.overlay.classList.add("header__navigation-main__overlay--open");
      }

      close() {
        if (!this.menu || !this.overlay) {
          return;
        }

        this.menu.style.right = `-${this.menu.clientWidth}px`;

        this.body.classList.remove("overflow-hidden");
        this.menu.classList.remove("header__navigation-main--open");
        this.overlay.classList.remove("header__navigation-main__overlay--open");
      }
    })(),
    toggleSection: (element) => {
      const section = element?.closest(".navigation__section");
      const subsection = section?.querySelector(".navigation__subsection");

      if (!section || !subsection) {
        return;
      }

      // Set initial height value for smooth animation
      if (!subsection.style.height) {
        subsection.style.height = "0px";
      }

      section.classList.toggle("navigation__section--open");
      const isOpen = subsection.classList.toggle(
        "navigation__subsection--open"
      );

      // Required for opening animation
      if (isOpen) {
        subsection.style.height = `${subsection.scrollHeight}px`;
      } else {
        subsection.style.height = "0px";
      }
    },
  },
};
