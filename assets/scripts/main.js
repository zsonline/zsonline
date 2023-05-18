import "@scripts/anniversary";
import { insertAds } from "@scripts/ads";
import { Gallery } from "@scripts/gallery";
import { Lightbox } from "@scripts/lightbox";

import { Navigation as NavigationMain } from "@scripts/navigation.js";

// Initialise images
var images = document.querySelectorAll("img");
for (const image of images) {
  image.classList.add("image--loading");

  if (image.complete) {
    image.classList.remove("image--loading");
  } else {
    image.addEventListener("load", () =>
      image.classList.remove("image--loading")
    );
  }
}

// Initialise all galleries
const galleries = document.getElementsByClassName("content-gallery");
for (const gallery of galleries) {
  new Gallery(gallery);
}

// Initialise lightbox
new Lightbox();

// Ads
insertAds();

// Header
const header = document.querySelector(".header");
const headerRule = header.querySelector(".header__rule");
const progress = header.querySelector(".progress");
const progressTarget = document.querySelector(".progress-target");

window.addEventListener("scroll", () => {
  // Update header class depending on whether the scroll position is at the
  // top of the page or within the page
  if (window.scrollY == 0) {
    header.classList.add("header--scroll-top");
    header.classList.remove("header--scroll-within");
  } else {
    header.classList.add("header--scroll-within");
    header.classList.remove("header--scroll-top");
  }

  // Update content reading progress
  if (progressTarget) {
    const scrollPosition =
      window.scrollY + Math.round(0.5 * window.innerHeight);

    if (progressTarget.offsetTop > scrollPosition) {
      headerRule.classList.remove("header__rule--bright");
      progress.style.width = 0;
    } else {
      headerRule.classList.add("header__rule--bright");

      const contentScrollPosition = scrollPosition - progressTarget.offsetTop;
      const contentProgress = Math.min(
        Math.round((100 * contentScrollPosition) / progressTarget.offsetHeight),
        100
      );

      progress.style.width = `${contentProgress}%`;
    }
  }
});

// Initialize global object once the HTML document is parsed
window.ZS = {
  ...window.ZS,
  Comments: {
    toggleReplyForm: (element) => {
      const reply = element?.closest(".comments__reply");
      const replyForm = reply?.querySelector(".comments__reply__form");

      const isVisible = replyForm.classList.toggle(
        "comments__reply__form--visible"
      );

      if (isVisible) {
        reply.classList.add("comments__replies--indent");
      } else if (!reply.classList.contains("comments__reply--replies")) {
        reply.classList.remove("comments__replies--indent");
      }
    },
  },
  Navigation: {
    Main: new NavigationMain(),
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

// Enable hot module replacement
if (import.meta.hot) {
  import.meta.hot.accept();
}
