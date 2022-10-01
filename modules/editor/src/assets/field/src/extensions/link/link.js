import DefaultLink from "@tiptap/extension-link";

const Link = DefaultLink.extend({
  addAttributes() {
    return {
      href: {
        default: null,
      },
    };
  },
});

export default Link;
