class Navigation {
  constructor() {
    this.body = document.querySelector("body");
    this.menu = document.querySelector("#header__navigation-main");
    this.overlay = document.querySelector("#header__navigation-main__overlay");

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
}

export { Navigation };
