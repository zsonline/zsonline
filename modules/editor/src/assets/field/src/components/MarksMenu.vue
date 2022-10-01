<script setup>
import { computed } from "vue";

import { isTextSelection } from "@tiptap/core";
import { BubbleMenu } from "@tiptap/vue-3";

import useMarks from "@composables/useMarks.js";

import Button from "@components/Button.vue";
import ButtonGroup from "@components/ButtonGroup.vue";

const props = defineProps({
  editor: {
    type: Object,
    default: null,
  },
});

const buttons = [
  {
    tooltip: "Italic",
    icon: "italic",
    mark: "italic",
    action: () => props.editor.chain().focus().toggleItalic().run(),
    active: () => props.editor.isActive("italic"),
  },
  {
    tooltip: "Bold",
    icon: "bold",
    mark: "bold",
    action: () => props.editor.chain().focus().toggleBold().run(),
    active: () => props.editor.isActive("bold"),
  },
  {
    tooltip: "Subscript",
    icon: "subscript",
    mark: "subscript",
    action: () => props.editor.chain().focus().toggleSubscript().run(),
    active: () => props.editor.isActive("subscript"),
  },
  {
    tooltip: "Superscript",
    icon: "superscript",
    mark: "superscript",
    action: () => props.editor.chain().focus().toggleSuperscript().run(),
    active: () => props.editor.isActive("superscript"),
  },
  {
    tooltip: "Link",
    icon: "link",
    mark: "link",
    action: () => handleLink(),
    active: () => props.editor.isActive("link"),
  },
];

const { getAvailableMarks } = useMarks(props);

const availableButtons = computed(() => {
  const marks = getAvailableMarks(props.editor);

  const availableButtons = buttons.filter((button) => {
    return marks.includes(button.mark);
  });

  if (availableButtons.length > 0) {
    availableButtons.push({
      tooltip: "Clear",
      icon: "clear",
      action: () => props.editor.chain().focus().unsetAllMarks().run(),
    });
  }

  return availableButtons;
});

// ShouldShow is adapted from tiptap's default shouldShow function. The
// definition can be found in tiptap's source code:
// /packages/extension-bubble-menu/src/bubble-menu-plugin.ts#L43
const shouldShow = ({ view, state, from, to }) => {
  const { doc, selection } = state;
  const { empty } = selection;

  // Double-clicking an empty paragraph returns a node size of 2. Thus, we also
  // need to check for an empty text size.
  const isEmptyTextBlock =
    !doc.textBetween(from, to).length && isTextSelection(state.selection);

  if (!view.hasFocus() || empty || isEmptyTextBlock) {
    return false;
  }

  // Check whether one of the selected nodes does not support any marks based on
  // their schema definition. If so, do not show the menu.
  const availableMarks = getAvailableMarks(props.editor, state);
  return availableMarks.length > 0;
};

const handleLink = () => {
  // If a link is already set, remove it
  if (props.editor.getAttributes("link").href) {
    props.editor.chain().focus().extendMarkRange("link").unsetLink().run();
    return;
  }

  // If no link is set yet, show a prompt
  const url = window.prompt("URL");
  if (!url) {
    return;
  }

  props.editor
    .chain()
    .focus()
    .extendMarkRange("link")
    .setLink({ href: url, target: "" })
    .run();
};
</script>

<template>
  <BubbleMenu
    v-if="props.editor"
    :editor="props.editor"
    :should-show="shouldShow"
    class="bubble-menu"
  >
    <ButtonGroup>
      <Button
        v-for="(item, index) in availableButtons"
        :key="index"
        v-bind="item"
        size="medium"
      />
    </ButtonGroup>
  </BubbleMenu>
</template>

<style lang="scss" scoped>
.bubble-menu {
  padding: 0.5em;
  background-color: var(--interface-background);
  border: solid 1px var(--interface-border);
  border-radius: 5px;
}
</style>
