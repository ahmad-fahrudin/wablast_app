<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
  class: {
    type: String,
    default: '',
  },
  disabled: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['update:modelValue']);

const toggle = () => {
  if (!props.disabled) {
    emit('update:modelValue', !props.modelValue);
  }
};

const state = computed(() => props.modelValue ? 'checked' : 'unchecked');

const switchClasses = computed(() => {
  return `peer inline-flex h-[24px] w-[44px] shrink-0 cursor-pointer items-center rounded-full
  border-2 border-transparent transition-colors focus-visible:outline-none focus-visible:ring-2
  focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background
  disabled:cursor-not-allowed disabled:opacity-50
  data-[state=checked]:bg-primary data-[state=unchecked]:bg-gray-200 dark:data-[state=unchecked]:bg-gray-700 ${props.class}`;
});

const thumbClasses = computed(() => {
  return `pointer-events-none block h-5 w-5 rounded-full bg-white shadow-lg ring-0
  transition-transform data-[state=checked]:translate-x-5 data-[state=unchecked]:translate-x-0`;
});
</script>

<template>
  <button
    type="button"
    role="switch"
    :aria-checked="modelValue"
    :data-state="state"
    :class="switchClasses"
    :disabled="disabled"
    @click="toggle"
  >
    <span
      :data-state="state"
      :class="thumbClasses"
    />
  </button>
</template>
