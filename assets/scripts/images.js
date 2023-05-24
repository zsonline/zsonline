// Append loading class to images, which is removed again once the image is
// loaded to trigger a fade-in animation
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
