<script setup>
import { ref } from "vue";
import { DotsVerticalIcon } from "vue-tabler-icons";

import HandleMenu from "@extensions/handle/components/HandleMenu.vue";

const props = defineProps({
  getPos: {
    type: Function,
    default: () => null,
  },
  editor: {
    type: Object,
    default: () => null,
  },
});

const emit = defineEmits(["activate", "deactivate", "hide"]);

const isOpen = ref(false);

const open = () => {
  isOpen.value = true;
  emit("activate");
};

const close = () => {
  isOpen.value = false;
  emit("deactivate");
};

const handleClick = () => {
  if (isOpen.value) {
    close();
  } else {
    open();
  }
};
</script>

<template>
  <div>
    <button
      :class="['handle', { active: isOpen }]"
      @click.prevent="handleClick"
    >
      <DotsVerticalIcon />
    </button>

    <HandleMenu
      v-if="isOpen"
      :get-pos="props.getPos"
      :editor="props.editor"
      @hide="emit('hide')"
    />
  </div>
</template>

<style lang="scss" scoped>
.handle {
  box-sizing: border-box;
  display: flex;
  align-items: center;
  padding: 5px 0;
  margin: -2px 0 0 0;
  color: var(--handle-foreground);
  cursor: pointer;
  background-color: var(--handle-background);
  border: none;
  border: solid 1px var(--handle-background);
  border-radius: 3px;

  &:hover,
  &:focus {
    color: var(--handle-foreground-alt);
    background-color: var(--handle-background-alt);
  }

  &:active,
  &.active {
    color: var(--handle-foreground-alt);
    background-color: var(--handle-background-alt);
    border: solid 1px var(--interface-border);
    border-color: var(--interface-border);
  }

  &.active {
    border-bottom-color: var(--handle-background) !important;
    border-bottom-right-radius: 0px;
    border-bottom-left-radius: 0px;
  }

  svg {
    width: 18px;
    height: 18px;
  }
}
</style>
