<script setup>
import { ref, watch } from "vue";

import BlockMenu from "@components/BlockMenu.vue";
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
    tooltip: "Paragraph",
    icon: "paragraph",
    action: () => props.editor.chain().focus().setParagraph().run(),
    active: () => props.editor.isActive("paragraph"),
  },
  {
    tooltip: "Heading 3",
    icon: "heading3",
    action: () =>
      props.editor.chain().focus().toggleHeading({ level: 3 }).run(),
    active: () => props.editor.isActive("heading", { level: 3 }),
  },
  {
    tooltip: "Heading 4",
    icon: "heading4",
    action: () =>
      props.editor.chain().focus().toggleHeading({ level: 4 }).run(),
    active: () => props.editor.isActive("heading", { level: 4 }),
  },
  {
    tooltip: "Ordered List",
    icon: "orderedList",
    action: () => props.editor.chain().focus().toggleOrderedList().run(),
    active: () => props.editor.isActive("orderedList"),
  },
  {
    tooltip: "Unordered List",
    icon: "unorderedList",
    action: () => props.editor.chain().focus().toggleBulletList().run(),
    active: () => props.editor.isActive("bulletList"),
  },
];

const controlButtons = [
  {
    tooltip: "Undo",
    icon: "undo",
    action: () => props.editor.chain().focus().undo().run(),
  },
  {
    tooltip: "Redo",
    icon: "redo",
    action: () => props.editor.chain().focus().redo().run(),
  },
];

// Set menu position manually depending on the setting
const menu = ref(null);

const offset = {
  classic: "50px",
  preview: "-27px",
  slideout: "-25px",
};

watch(menu, () => {
  if (!menu.value) {
    return;
  }

  if (menu.value.closest(".slideout-container")) {
    // Slideout
    menu.value.style.top = offset.slideout;
  } else {
    // Classic editor
    menu.value.style.top = offset.classic;

    // Preview
    Garnish.on(Craft.Preview, "open", () => {
      menu.value.style.top = offset.preview;
    });

    Garnish.on(Craft.Preview, "close", () => {
      menu.value.style.top = offset.classic;
    });
  }
});
</script>

<template>
  <nav v-if="editor" ref="menu" class="top-menu">
    <div class="left">
      <BlockMenu :editor="editor" />
      <ButtonGroup>
        <Button
          v-for="(item, index) in buttons"
          :key="index"
          v-bind="item"
          size="large"
        />
      </ButtonGroup>
    </div>
    <div class="right">
      <ButtonGroup>
        <Button
          v-for="(item, index) in controlButtons"
          :key="index"
          v-bind="item"
          size="large"
        />
      </ButtonGroup>
    </div>
  </nav>
</template>

<style lang="scss" scoped>
.top-menu {
  position: sticky;
  top: 0;
  z-index: 1;
  display: flex;
  justify-content: space-between;
  padding: 0.5em;
  background-color: var(--interface-background);
  border-bottom: solid 1px var(--interface-border);

  .left {
    display: flex;
    gap: 2em;
  }
}
</style>
