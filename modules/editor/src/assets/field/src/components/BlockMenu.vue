<script setup>
import { ref } from "vue";

import Button from "@components/Button.vue";

const props = defineProps({
  editor: {
    type: Object,
    default: null,
  },
});

const button = ref(null);
const menu = ref(null);
const isEnabled = ref(false);

const enable = () => {
  isEnabled.value = true;
  window.addEventListener("click", handleClickOutside);
};
const disable = () => {
  isEnabled.value = false;
  window.removeEventListener("click", handleClickOutside);
};
const toggle = () => {
  if (isEnabled.value) {
    disable();
  } else {
    enable();
  }
};

const handleClickOutside = (e) => {
  if (isEnabled.value) {
    if (!menu.value.contains(e.target) && !button.value.contains(e.target)) {
      disable();
    }
  }
};

const blockButtons = [
  {
    label: "Quote",
    icon: "quote",
    action: () => props.editor.chain().focus().insertQuote().run(),
  },
  {
    label: "Note",
    icon: "note",
    action: () => props.editor.chain().focus().insertNote().run(),
  },
  {
    label: "Image",
    icon: "image",
    action: () => props.editor.chain().focus().insertImage().run(),
  },
  {
    label: "Gallery",
    icon: "gallery",
    action: () => props.editor.chain().focus().insertGallery().run(),
  },
  {
    label: "Embed",
    icon: "embed",
    action: () => props.editor.chain().focus().insertEmbed().run(),
  },
  {
    label: "File",
    icon: "file",
    action: () => props.editor.chain().focus().insertFile().run(),
  },
];
</script>

<template>
  <div>
    <div ref="button">
      <Button tooltip="Add Block" icon="add" :action="toggle" size="large" />
    </div>

    <nav v-show="isEnabled" ref="menu">
      <Button
        v-for="(item, index) in blockButtons"
        :key="index"
        v-bind="item"
        size="large"
        @click="toggle"
      />
    </nav>
  </div>
</template>

<style lang="scss" scoped>
nav {
  position: absolute;
  top: 4em;
  z-index: 2;
  display: grid;
  grid-template-columns: auto auto;
  gap: 0.5em;
  padding: 0.5em;
  background-color: var(--interface-background);
  border: solid 1px var(--interface-border);
  border-radius: 5px;

  :deep(button) {
    width: 100%;
  }
}
</style>
