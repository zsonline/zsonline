<script setup>
import Popper from "vue3-popper";

const props = defineProps({
  content: {
    type: String,
    required: true,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
});
</script>

<template>
  <Popper
    :content="props.content"
    :disabled="props.disabled"
    :hover="true"
    :interactive="false"
    placement="top"
  >
    <slot />
  </Popper>
</template>

<style lang="scss" scoped>
:deep(.popper) {
  --popper-theme-padding: 0.25em 0.5em;
  --popper-theme-border-radius: 5px;
  --popper-theme-text-color: var(--interface-background);
  --popper-theme-background-color: var(--interface-foreground);

  font-family: sans-serif;
  font-size: 0.75rem;
}
</style>
