async function loadAds() {
  // Load ads
  const response = await fetch("/actions/site/ads/random");
  const jsonAds = await response.json();

  // Create DOM nodes for each ad
  const ads = [];
  for (const jsonAd of jsonAds) {
    const fragment = document.createDocumentFragment();

    const label = document.createElement("span");
    label.classList.add("ad-declaration");
    label.innerText = "Werbung";
    fragment.appendChild(label);

    const link = document.createElement("a");
    link.href = jsonAd.url;
    link.target = "_blank";
    fragment.appendChild(link);

    const image = document.createElement("img");
    image.height = jsonAd.images["2x"].height;
    image.width = jsonAd.images["2x"].width;
    image.sizes = "(min-width: 1440px) 600px, 300px";
    image.srcset = `${jsonAd.images["1x"].url} 300w, ${jsonAd.images["2x"].url} 600w`;
    image.dataset.slug = jsonAd.slug;
    link.appendChild(image);

    // Track view events
    const observer = new IntersectionObserver((entries, observer) => {
      if (entries.length != 1 || entries[0].isIntersecting) {
        // eslint-disable-next-line no-undef
        gtag("event", "ad_view", {
          slug: entries[0].target.dataset.slug,
        });

        observer.disconnect();
      }
    });
    observer.observe(image);

    // Track click events
    link.addEventListener("click", (e) => {
      // eslint-disable-next-line no-undef
      gtag("event", "ad_click", {
        slug: e.target.dataset.slug,
      });
    });

    ads.push(fragment);
  }

  return ads;
}

async function insertAdsIntoHome(ads, home) {
  const homeBlocks = home.querySelectorAll(".home-section");
  if (homeBlocks.length == 0) {
    return;
  }

  // Calculate the ad placement: The first ad is positioned after the first home
  // block and the second ad halfway down the page
  const indices = [0, Math.ceil(homeBlocks.length / 2) - 1];

  for (const ad of ads) {
    if (indices.length == 0) {
      break;
    }

    // Create DOM node
    const block = document.createElement("div");
    block.classList.add(
      "home-section",
      "home-section--colored-next",
      "home-section--colored-prev",
      "ad-block",
      "ad-block--home",
    );
    block.appendChild(ad);

    // Insert ad node
    const index = indices.shift();
    homeBlocks[index].parentNode.insertBefore(
      block,
      homeBlocks[index].nextSibling,
    );

    // Remove margins of the previous and next home blocks
    if (block.previousElementSibling.classList.contains("home-section")) {
      block.previousElementSibling.classList.remove(
        "home-section--colored-next",
      );
    }

    if (block.nextElementSibling.classList.contains("home-section")) {
      block.nextElementSibling.classList.remove("home-section--colored-prev");
    }
  }
}

async function insertAdsIntoArticle(ads, article) {
  // Calculate potential ad positions, between two paragraphs or a paragraph and
  // a heading
  const potentialIndices = [];
  for (let i = 0; i < article.length - 1; i++) {
    const currentTag = article[i].tagName.toLowerCase();
    const nextTag = article[i + 1].tagName.toLowerCase();

    if (
      currentTag === "p" &&
      (nextTag === "p" || nextTag === "h3" || nextTag === "h4")
    ) {
      potentialIndices.push(i);
    }
  }

  let indices = potentialIndices;
  if (potentialIndices.length > 2) {
    // Choose the best ad placement: The first ad is positioned near the top and
    // the second ad halfway down the page
    indices = [
      potentialIndices[Math.floor(potentialIndices.length / 6)],
      potentialIndices[Math.floor((2 * potentialIndices.length) / 3)],
    ];
  }

  for (let i = 0; i < ads.length; i++) {
    if (indices.length == 0) {
      break;
    }

    // Create DOM node
    const block = document.createElement("div");
    block.classList.add("content-block", "ad-block", "ad-block--article");
    block.appendChild(ads[i]);

    // Insert ad node
    const index = indices.shift() + i;
    article[index].parentNode.insertBefore(block, article[index].nextSibling);
  }
}

async function insertAds() {
  const home = document.querySelector(".home");
  const article = document.querySelector(".content.progress-target");

  // Check that we are on the article or home page
  if (!home && !article) {
    return;
  }

  // Load ads
  const ads = await loadAds();

  // Insert ads into the home page
  if (home) {
    await insertAdsIntoHome(ads, home);
  }

  // Insert ads into the article pages
  if (article) {
    await insertAdsIntoArticle(ads, article.children);
  }
}

insertAds();
