<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps({
  modelValue: {
    type: String,
    default: '',
  },
  class: {
    type: String,
    default: '',
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  placeholder: {
    type: String,
    default: '',
  },
  rows: {
    type: [String, Number],
    default: 4,
  },
});

const emit = defineEmits(['update:modelValue']);

const classes = computed(() => {
  return `flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2
  text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none
  focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2
  disabled:cursor-not-allowed disabled:opacity-50 ${props.class}`;
});

function onInput(event: Event) {
  emit('update:modelValue', (event.target as HTMLTextAreaElement).value);
}
</script>

<template>
  <textarea
    :value="modelValue"
    @input="onInput"
    :class="classes"
    :disabled="disabled"
    :placeholder="placeholder"
    :rows="rows"
  ></textarea>
</template>

<script lang="ts">
export { default as Textarea } from './textarea.vue';
</script>
