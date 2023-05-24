import Swiper, { Mousewheel, Navigation, Pagination } from "swiper";

class Gallery {
  constructor(element) {
    // Query gallery DOM elements
    this.caption = element.querySelector(".content-gallery__meta figcaption");

    // Initialise swiper
    this.swiper = new Swiper(element, {
      centeredSlides: true,
      modules: [Mousewheel, Navigation, Pagination],
      mousewheel: {
        eventsTarget: ".gallery-controls",
      },
      navigation: {
        nextEl: ".swiper-next",
        prevEl: ".swiper-prev",
      },
      pagination: {
        el: ".swiper-pagination",
        type: "fraction",
      },
      slidesPerView: "auto",
      spaceBetween: 20,
    });

    this.swiper.on("slideChange", ({ realIndex }) => {
      this.updateCaption(realIndex);
    });

    this.updateCaption(0);
  }

  // Copy the caption of the current index into the gallery caption node
  updateCaption(index) {
    const slide = this.swiper.slides[index];
    const caption = slide.querySelector("figcaption");

    if (!caption) {
      this.caption.innerHTML = "";
      return;
    }

    this.caption.innerHTML = caption.innerHTML;
  }
}

// Initialise all galleries
const galleries = document.getElementsByClassName("content-gallery");
for (const gallery of galleries) {
  new Gallery(gallery);
}
