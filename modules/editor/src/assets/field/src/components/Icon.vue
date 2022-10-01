<template>
  <component :is="icon" class="icon" />
</template>

<script>
import { computed, defineComponent } from "vue";

import {
  AlignLeftIcon,
  ArrowBackUpIcon,
  ArrowDownIcon,
  ArrowForwardUpIcon,
  ArrowUpIcon,
  ArrowsVerticalIcon,
  BlockquoteIcon,
  BoldIcon,
  BrandYoutubeIcon,
  CaretDownIcon,
  CodeIcon,
  ClearFormattingIcon,
  DragDropIcon,
  FileIcon,
  H3Icon,
  H4Icon,
  InfoCircleIcon,
  ItalicIcon,
  LinkIcon,
  ListIcon,
  ListNumbersIcon,
  MinusIcon,
  NoteIcon,
  PhotoIcon,
  PlusIcon,
  QuestionMarkIcon,
  SlideshowIcon,
  SubscriptIcon,
  SuperscriptIcon,
  TrashIcon,
  XIcon,
} from "vue-tabler-icons";

export default defineComponent({
  components: {
    AlignLeftIcon,
    ArrowBackUpIcon,
    ArrowDownIcon,
    ArrowForwardUpIcon,
    ArrowUpIcon,
    ArrowsVerticalIcon,
    BlockquoteIcon,
    BoldIcon,
    BrandYoutubeIcon,
    CaretDownIcon,
    CodeIcon,
    ClearFormattingIcon,
    DragDropIcon,
    FileIcon,
    H3Icon,
    H4Icon,
    InfoCircleIcon,
    ItalicIcon,
    LinkIcon,
    ListIcon,
    ListNumbersIcon,
    MinusIcon,
    NoteIcon,
    PhotoIcon,
    PlusIcon,
    QuestionMarkIcon,
    SlideshowIcon,
    SubscriptIcon,
    SuperscriptIcon,
    TrashIcon,
    XIcon,
  },
  props: {
    name: {
      type: String,
      required: true,
    },
  },
  setup(props) {
    const icons = {
      add: "PlusIcon",
      move: "ArrowsVerticalIcon",
      up: "ArrowUpIcon",
      down: "ArrowDownIcon",
      delete: "TrashIcon",
      dropdown: "CaretDownIcon",
      close: "XIcon",

      paragraph: "AlignLeftIcon",
      heading3: "H3Icon",
      heading4: "H4Icon",
      orderedList: "ListNumbersIcon",
      unorderedList: "ListIcon",

      undo: "ArrowBackUpIcon",
      redo: "ArrowForwardUpIcon",

      italic: "ItalicIcon",
      bold: "BoldIcon",
      link: "LinkIcon",
      subscript: "SubscriptIcon",
      superscript: "SuperscriptIcon",
      clear: "ClearFormattingIcon",

      code: "CodeIcon",
      quote: "BlockquoteIcon",
      note: "NoteIcon",
      image: "PhotoIcon",
      gallery: "SlideshowIcon",
      embed: "BrandYoutubeIcon",
      file: "FileIcon",
    };

    const icon = computed(() => {
      let iconName = "QuestionMarkIcon";

      if (props.name in icons) {
        iconName = icons[props.name];
      }

      return iconName;
    });

    return { icon };
  },
});
</script>
