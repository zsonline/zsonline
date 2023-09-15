import Swiper from "swiper";
import { Keyboard, Mousewheel, Navigation, Pagination } from "swiper/modules";

class Lightbox {
  constructor() {
    // Query lightbox images. If none are defined, we do not initialise
    this.images = document.querySelectorAll("img[data-lightbox]");
    if (this.images.length == 0) {
      return;
    }

    // Query lightbox DOM nodes
    this.body = document.querySelector("body");
    this.lightbox = document.querySelector(".lightbox");
    this.caption = this.lightbox.querySelector("figcaption");

    // Initialise lightbox
    this.initialiseCloseButton();
    this.initialiseImages();

    // Initialise swiper
    this.swiper = new Swiper(this.lightbox, {
      keyboard: {
        enabled: true,
        onlyInViewport: true,
      },
      modules: [Keyboard, Mousewheel, Navigation, Pagination],
      mousewheel: true,
      navigation: {
        nextEl: ".swiper-next",
        prevEl: ".swiper-prev",
      },
      pagination: {
        el: ".swiper-pagination",
        type: "fraction",
      },
      slidesPerView: 1,
    });

    this.swiper.on("slideChange", ({ realIndex }) => {
      this.updateCaption(realIndex);
    });

    this.updateCaption(0);
  }

  // Iterate all lightbox images and clone the data-lightbox attribute into
  // swiper slides.
  initialiseImages() {
    const wrapper = this.lightbox.querySelector(".swiper-wrapper");

    for (const image of this.images) {
      const slide = document.createElement("div");
      slide.classList.add("swiper-slide");
      slide.innerHTML = new DOMParser().parseFromString(
        image.dataset.lightbox,
        "text/html",
      ).documentElement.textContent;

      wrapper.appendChild(slide);

      // Add event listener to each lightbox image, which opens the lightbox
      // at the image's index
      image.addEventListener("click", (e) => {
        const index = Array.prototype.indexOf.call(this.images, e.target);
        this.open(index);
      });
    }
  }

  // Add event listener to close button.
  initialiseCloseButton() {
    const closeButton = this.lightbox.querySelector(".lightbox__header button");
    closeButton.addEventListener("click", () => {
      this.close();
    });
  }

  // Copy the caption of the current index into the lightbox caption node.
  updateCaption(index) {
    const image = this.images[index];
    const caption = image.parentNode.querySelector("figcaption");

    if (!caption) {
      this.caption.innerHTML = "";
      return;
    }

    this.caption.innerHTML = caption.innerHTML;
  }

  // Open the lightbox and slide to the selected image
  open(index = 0) {
    this.swiper.slideTo(index, 0);

    this.lightbox.classList.add("open");
    this.body.classList.add("overflow-hidden");
  }

  // Close the lightbox
  close() {
    this.body.classList.remove("overflow-hidden");
    this.lightbox.classList.remove("open");
  }
}

new Lightbox();
