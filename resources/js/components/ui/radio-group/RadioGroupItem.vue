<template>
  <div class="flex items-center space-x-2">
    <input
      :id="id"
      type="radio"
      :name="radioGroupName"
      class="sr-only"
      :value="value"
      :checked="radioGroupValue === value"
      @change="onChange"
      v-bind="$attrs"
    />
    <slot />
  </div>
</template>

<script setup lang="ts">
import { inject, computed } from 'vue'

const props = defineProps<{
  value: any
  id?: string
  name?: string
}>()

const { name: radioGroupName, modelValue, updateModelValue } = inject('RadioGroup', {
  name: null,
  modelValue: { value: undefined },
  updateModelValue: () => {}
})

const radioGroupValue = computed(() => modelValue.value)

const onChange = () => {
  updateModelValue(props.value)
}
</script>
