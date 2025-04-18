<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { PlusIcon, PencilIcon, TrashIcon, SearchIcon } from 'lucide-vue-next';
import Swal from 'sweetalert2';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
  groups: Object,
  filters: Object
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Groups',
        href: '/groups',
    },
];

// Search functionality
const search = ref(props.filters?.search || '');

// Use debounce to avoid too many requests while typing
const debouncedSearch = debounce(() => {
  router.get(route('groups.index'), { search: search.value }, {
    preserveState: true,
    preserveScroll: true,
    replace: true
  });
}, 500);

watch(search, debouncedSearch);

function deleteGroup(id) {
  Swal.fire({
    title: 'Are you sure?',
    text: "You won't be able to revert this!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes, delete it!'
  }).then((result) => {
    if (result.isConfirmed) {
      router.delete(route('groups.destroy', id), {
        onSuccess: () => {
          Swal.fire(
            'Deleted!',
            'Group has been deleted.',
            'success'
          );
        },
        onError: () => {
          Swal.fire(
            'Error!',
            'There was a problem deleting the group.',
            'error'
          );
        }
      });
    }
  });
}
</script>

<template>
    <Head title="Group Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold">Group Management</h1>
                <Link :href="route('groups.create')">
                    <Button variant="default" class="flex items-center gap-2">
                        <PlusIcon class="h-4 w-4" />
                        Add Group
                    </Button>
                </Link>
            </div>

            <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border overflow-hidden">
                <div class="bg-gray-50 dark:bg-gray-800 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex flex-wrap items-center justify-end gap-4">
                        <div class="relative w-full md:w-auto md:min-w-[300px]">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <SearchIcon class="h-4 w-4 text-gray-400" />
                            </div>
                            <input
                                v-model="search"
                                type="text"
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-900 text-sm"
                                placeholder="Search groups..."
                            />
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-12">No.</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Subject</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Description</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-24">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="(item, index) in groups.data" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="px-4 py-2 whitespace-nowrap text-sm">{{ groups.from + index }}</td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm capitalize">{{ item.type }}</td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm">{{ item.subject }}</td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm">{{ item.description || '-' }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">
                                    <div class="flex space-x-1">
                                        <Link :href="route('groups.edit', item.id)">
                                            <Button variant="outline" size="sm" class="h-7 w-7 p-0">
                                                <PencilIcon class="h-3.5 w-3.5" />
                                                <span class="sr-only">Edit</span>
                                            </Button>
                                        </Link>
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            class="h-7 w-7 p-0 text-red-500 hover:text-red-600 dark:text-red-400 dark:hover:text-red-300"
                                            @click="deleteGroup(item.id)"
                                        >
                                            <TrashIcon class="h-3.5 w-3.5" />
                                            <span class="sr-only">Delete</span>
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="groups.data.length === 0">
                                <td colspan="5" class="px-4 py-3 text-center text-gray-500 dark:text-gray-400">
                                    No groups found
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                        <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                            Showing
                            <span class="font-medium mx-1">{{ groups.from || 0 }}</span>
                            to
                            <span class="font-medium mx-1">{{ groups.to || 0 }}</span>
                            of
                            <span class="font-medium mx-1">{{ groups.total }}</span>
                            results
                        </div>

                        <nav class="flex flex-wrap justify-center gap-2" aria-label="Pagination">
                            <template v-for="(link, key) in groups.links" :key="key">
                                <template v-if="link.label !== '&hellip;'">
                                    <Link
                                        v-if="link.url"
                                        :href="link.url"
                                        class="px-3 py-1 rounded border text-sm font-medium"
                                        :class="{
                                            'bg-blue-500 text-white border-blue-500 hover:bg-blue-600': link.active,
                                            'bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800': !link.active
                                        }"
                                        :preserve-scroll="true"
                                        :preserve-state="true"
                                    >
                                        <span v-if="link.label === '&laquo; Previous'">Previous</span>
                                        <span v-else-if="link.label === 'Next &raquo;'">Next</span>
                                        <span v-else v-html="link.label"></span>
                                    </Link>
                                    <span
                                        v-else
                                        class="px-3 py-1 rounded border text-sm font-medium text-gray-400 bg-gray-100 dark:bg-gray-800 border-gray-300 dark:border-gray-700 cursor-not-allowed"
                                    >
                                        <span v-if="link.label === '&laquo; Previous'">Previous</span>
                                        <span v-else-if="link.label === 'Next &raquo;'">Next</span>
                                        <span v-else v-html="link.label"></span>
                                    </span>
                                </template>
                                <span
                                    v-else
                                    class="px-3 py-1 rounded border text-sm font-medium text-gray-400 bg-gray-100 dark:bg-gray-800 border-gray-300 dark:border-gray-700"
                                >
                                    ...
                                </span>
                            </template>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
