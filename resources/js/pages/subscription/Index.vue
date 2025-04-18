<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { PlusIcon, SearchIcon, ShoppingCart, RefreshCwIcon } from 'lucide-vue-next';
import Swal from 'sweetalert2';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
  devices: Object,
  filters: Object
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Device Subscription',
        href: '/devices',
    },
];

// Search functionality
const search = ref(props.filters?.search || '');

// Use debounce to avoid too many requests while typing
const debouncedSearch = debounce(() => {
  router.get(route('subscriptions.index'), { search: search.value }, {
    preserveState: true,
    preserveScroll: true,
    replace: true
  });
}, 500);

watch(search, debouncedSearch);

function formatDate(dateString) {
  if (!dateString) return '-';
  return new Date(dateString).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  });
}

function handleScanQrCode(device) {
  if (!device.subscription) {
    Swal.fire({
      title: 'Subscription Required',
      text: 'You need to subscribe to a plan before scanning the QR code.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Go to Subscription'
    }).then((result) => {
      if (result.isConfirmed) {
        router.visit(route('subscriptions.index'));
      }
    });
  } else {
    router.visit(route('devices.qr-code', device.id));
  }
}
</script>

<template>
    <Head title="Device Subscription" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold">Device Subscription</h1>
                <Link :href="route('subscriptions.plan')">
                    <Button variant="default" class="flex items-center gap-2">
                        <PlusIcon class="h-4 w-4" />
                        Subscription New Device
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
                                placeholder="Search device name..."
                            />
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-12">No.</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Device ID</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Subscription</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Quota</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Expires On</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-24">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="(device, index) in devices.data" :key="device.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="px-4 py-2 whitespace-nowrap text-sm">{{ devices.from + index }}</td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm">{{ device.name }}</td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm font-mono">{{ device.deviceID }}</td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm">
                                    <span v-if="device.subscription"
                                          class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                        {{ device.subscription.name }}
                                    </span>
                                    <span v-else class="text-gray-400">-</span>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm">{{ device.quota }}</td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm">{{ formatDate(device.expired_at) }}</td>
                                <td class="px-4 py-2 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <Link
                                            v-if="!device.subscription"
                                            :href="route('subscriptions.plan', { device_id: device.id })"
                                            class="inline-flex items-center justify-center px-3 py-1 text-xs font-medium rounded-md text-white bg-black hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                                        >
                                            <PlusIcon class="h-3 w-3 mr-1" />
                                            Add
                                        </Link>
                                        <Link
                                            v-else
                                            :href="route('subscriptions.plan', { device_id: device.id })"
                                            class="inline-flex items-center justify-center px-3 py-1 text-xs font-medium rounded-md text-white bg-black hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                                        >
                                            <RefreshCwIcon class="h-3 w-3 mr-1" />
                                            Renew
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="devices.data.length === 0">
                                <td colspan="9" class="px-4 py-3 text-center text-gray-500 dark:text-gray-400">
                                    No devices found
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
                            <span class="font-medium mx-1">{{ devices.from || 0 }}</span>
                            to
                            <span class="font-medium mx-1">{{ devices.to || 0 }}</span>
                            of
                            <span class="font-medium mx-1">{{ devices.total }}</span>
                            results
                        </div>

                        <nav class="flex flex-wrap justify-center gap-2" aria-label="Pagination">
                            <template v-for="(link, key) in devices.links" :key="key">
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
