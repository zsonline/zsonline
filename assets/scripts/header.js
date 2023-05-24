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
