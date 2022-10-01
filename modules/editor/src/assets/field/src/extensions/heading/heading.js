import DefaultHeading from "@tiptap/extension-heading";

// The heading node is identical to the default heading node except that no
// marks other than subscript and superscript can be applied to it.
const Heading = DefaultHeading.extend({
  marks: "subscript superscript",
});

export default Heading;
