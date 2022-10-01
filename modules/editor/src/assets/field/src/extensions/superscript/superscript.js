import DefaultSuperscript from "@tiptap/extension-superscript";

const Superscript = DefaultSuperscript.extend({
  excludes: "subscript",
});

export default Superscript;
