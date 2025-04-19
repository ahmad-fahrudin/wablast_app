<!-- Pagination.vue -->
<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps({
  total: {
    type: Number,
    required: true
  },
  perPage: {
    type: Number,
    required: true
  },
  currentPage: {
    type: Number,
    required: true
  }
});

const emit = defineEmits(['page-change']);

const totalPages = computed(() => Math.ceil(props.total / props.perPage));

const pages = computed(() => {
  const visiblePages = 5;
  const halfVisible = Math.floor(visiblePages / 2);
  let startPage = Math.max(props.currentPage - halfVisible, 1);
  let endPage = Math.min(startPage + visiblePages - 1, totalPages.value);

  if (endPage - startPage + 1 < visiblePages) {
    startPage = Math.max(endPage - visiblePages + 1, 1);
  }

  return Array.from({ length: endPage - startPage + 1 }, (_, i) => startPage + i);
});

function changePage(page: number) {
  if (page < 1 || page > totalPages.value || page === props.currentPage) {
    return;
  }
  emit('page-change', page);
}
</script>

<template>
  <div v-if="totalPages > 1" class="flex items-center justify-center gap-1">
    <!-- Previous Button -->
    <button
      type="button"
      @click="changePage(currentPage - 1)"
      :disabled="currentPage === 1"
      :class="[
        'relative inline-flex items-center px-2 py-2 text-sm font-medium rounded-md',
        currentPage === 1
          ? 'text-gray-400 cursor-not-allowed'
          : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800'
      ]"
    >
      <span class="sr-only">Previous</span>
      <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
      </svg>
    </button>

    <!-- First Page Button (when not visible in the range) -->
    <button
      v-if="!pages.includes(1) && pages.length > 0"
      type="button"
      @click="changePage(1)"
      :class="[
        'relative inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-md',
        'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800'
      ]"
    >
      1
    </button>

    <!-- Ellipsis if needed -->
    <span v-if="pages.length > 0 && pages[0] > 2" class="text-gray-500">...</span>

    <!-- Page Numbers -->
    <button
      v-for="page in pages"
      :key="page"
      type="button"
      @click="changePage(page)"
      :class="[
        'relative inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-md',
        page === currentPage
          ? 'bg-blue-500 text-white'
          : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800'
      ]"
    >
      {{ page }}
    </button>

    <!-- Ellipsis if needed -->
    <span v-if="pages.length > 0 && pages[pages.length - 1] < totalPages - 1" class="text-gray-500">...</span>

    <!-- Last Page Button (when not visible in the range) -->
    <button
      v-if="!pages.includes(totalPages) && totalPages > 1 && pages.length > 0"
      type="button"
      @click="changePage(totalPages)"
      :class="[
        'relative inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-md',
        'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800'
      ]"
    >
      {{ totalPages }}
    </button>

    <!-- Next Button -->
    <button
      type="button"
      @click="changePage(currentPage + 1)"
      :disabled="currentPage === totalPages"
      :class="[
        'relative inline-flex items-center px-2 py-2 text-sm font-medium rounded-md',
        currentPage === totalPages
          ? 'text-gray-400 cursor-not-allowed'
          : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800'
      ]"
    >
      <span class="sr-only">Next</span>
      <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
      </svg>
    </button>
  </div>
</template>
