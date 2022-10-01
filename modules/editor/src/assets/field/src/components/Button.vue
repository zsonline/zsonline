<script setup>
import { computed } from "vue";

import Icon from "@components/Icon.vue";
import Tooltip from "@components/Tooltip.vue";

const props = defineProps({
  label: {
    type: String,
    default: "",
  },
  icon: {
    type: String,
    default: "",
  },
  tooltip: {
    type: String,
    default: "",
  },
  action: {
    type: Function,
    default: () => {},
  },
  active: {
    type: Function,
    default: () => false,
  },
  size: {
    type: String,
    default: "medium",
    validator(value) {
      return ["small", "medium", "large"].includes(value);
    },
  },
});

const hasLabel = computed(() => {
  return !!props.label;
});

const hasIcon = computed(() => {
  return !!props.icon;
});

const hasTooltip = computed(() => {
  return !!props.tooltip;
});
</script>

<template>
  <Tooltip :content="props.tooltip" :disabled="!hasTooltip">
    <button
      :class="{
        active: props.active(),
        small: props.size == 'small',
        medium: props.size == 'medium',
        large: props.size == 'large',
      }"
      @click.prevent="props.action"
    >
      <Icon v-if="hasIcon" :name="props.icon" />
      <span v-if="hasLabel">{{ props.label }}</span>
    </button>
  </Tooltip>
</template>

<style lang="scss" scoped>
button {
  box-sizing: border-box;
  display: flex;
  flex-direction: row;
  gap: 0.75em;
  align-items: center;
  margin: 0;
  font-family: sans-serif;
  color: var(--interface-foreground-subdued);
  cursor: pointer;
  background-color: var(--interface-background-subdued);
  border: none;
  border-radius: 5px;

  &:hover,
  &:focus,
  &:active,
  &.active {
    color: var(--interface-foreground-subdued-alt);
    background-color: var(--interface-background-subdued-alt);
  }

  &.small {
    height: 3.25em;
    padding: 1em;
    font-size: 0.7rem;
  }

  &,
  &.medium {
    height: 3.25em;
    padding: 1em;
    font-size: 0.8rem;
  }

  &.large {
    height: 3.5em;
    padding: 1.25em;
    font-size: 0.9rem;
  }

  .icon {
    width: 1.25em;
    height: 1.25em;
  }
}
</style>
