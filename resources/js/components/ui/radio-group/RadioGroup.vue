<template>
  <div class="grid gap-2">
    <slot />
  </div>
</template>

<script setup lang="ts">
import { provide, toRef } from 'vue'
import type { InjectionKey } from 'vue'

const props = defineProps<{
  modelValue?: any
}>()

const emit = defineEmits(['update:modelValue'])

const name = 'RadioGroup'

provide('RadioGroup', {
  name,
  modelValue: toRef(props, 'modelValue'),
  updateModelValue: (value: any) => emit('update:modelValue', value)
})
</script>
