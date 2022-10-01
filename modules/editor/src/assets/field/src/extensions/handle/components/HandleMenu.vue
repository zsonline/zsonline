<script setup>
import { getParentNodeFromPos } from "@extensions/helpers.js";

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

const emit = defineEmits(["hide"]);

// Get position when the component is mounted. This happens when the handle is
// clicked. Hence, the position is accurate.
const pos = props.getPos();

const actions = [
  {
    label: "Delete",
    action: () => {
      const { before, after } = getParentNodeFromPos(pos);

      // Delete the node
      props.editor.view.dispatch(
        props.editor.view.state.tr.delete(before, after)
      );

      emit("hide");
    },
  },
];
</script>

<template>
  <div class="handle-menu">
    <button
      v-for="(item, index) in actions"
      :key="index"
      @click.prevent="item.action"
    >
      {{ item.label }}
    </button>
  </div>
</template>

<style lang="scss" scoped>
.handle-menu {
  display: flex;
  flex-direction: column;
  gap: 1px;
  color: var(--interface-foreground);
  background-color: var(--interface-border);
  border: solid 1px var(--interface-border);
  border-radius: 0 3px 3px 3px;

  button {
    box-sizing: border-box;
    display: flex;
    align-items: center;
    width: 100%;
    padding: 5px 10px;
    margin: 0;
    cursor: pointer;
    background-color: transparent;
    background-color: var(--interface-background);
    border: none;

    &:hover,
    &:focus,
    &:active {
      background-color: var(--interface-background-alt);
    }
  }
}
</style>
