<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { SearchIcon, DownloadIcon, EyeIcon, CheckCircleIcon, XCircleIcon, ClockIcon, XIcon, ChevronDownIcon } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';
import Swal from 'sweetalert2';

const props = defineProps({
  payments: Object,
  filters: Object
});

const page = usePage();
const user = page.props.auth.user;

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Invoice History',
        href: '/subscriptions/invoice',
    },
];

// Search functionality
const search = ref(props.filters?.search || '');

// Use debounce to avoid too many requests while typing
const debouncedSearch = debounce(() => {
  router.get(route('subscriptions.invoice'), { search: search.value }, {
    preserveState: true,
    preserveScroll: true,
    replace: true
  });
}, 500);

watch(search, debouncedSearch);

// Modal state for invoice details
const showModal = ref(false);
const selectedPayment = ref(null);
const showApprovalMenu = ref(false);

function viewInvoiceDetails(payment) {
  selectedPayment.value = payment;
  showModal.value = true;
  showApprovalMenu.value = false;
}

function closeModal() {
  showModal.value = false;
  setTimeout(() => {
    selectedPayment.value = null;
  }, 300); // Clear after animation completes
}

function formatDate(dateString) {
  if (!dateString) return '-';
  return new Date(dateString).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  });
}

function formatDateTime(dateString) {
  if (!dateString) return '-';
  return new Date(dateString).toLocaleString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
}

function formatPrice(price) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(price);
}

function getStatusClass(status) {
  switch(status) {
    case 'completed':
      return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
    case 'pending':
      return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
    case 'failed':
      return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
    default:
      return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
  }
}

function getStatusIcon(status) {
  switch(status) {
    case 'completed':
      return CheckCircleIcon;
    case 'pending':
      return ClockIcon;
    case 'failed':
      return XCircleIcon;
    default:
      return null;
  }
}

function getStatusLabel(status) {
  switch(status) {
    case 'completed':
      return 'Selesai';
    case 'pending':
      return 'Menunggu Persetujuan';
    case 'failed':
      return 'Gagal';
    default:
      return status;
  }
}

// Function to get storage image URL
function getStorageImageUrl(imagePath) {
  if (!imagePath) return null;
  return `/storage/${imagePath}`;
}

// Check if user is admin
function isAdmin() {
    if (!user || !user.roles) return false;

    // Handle array roles
    if (Array.isArray(user.roles)) {
        return user.roles.some(role =>
            (typeof role === 'string' && role.toLowerCase() === 'admin') ||
            (role.name && role.name.toLowerCase() === 'admin')
        );
    }

    // Handle object roles
    if (typeof user.roles === 'object') {
        return Object.values(user.roles).some(role =>
            (typeof role === 'string' && role.toLowerCase() === 'admin')
        );
    }

    // Handle string roles
    return typeof user.roles === 'string' && user.roles.toLowerCase().includes('admin');
}

// Handle approve payment
function approvePayment() {
  if (!selectedPayment.value) return;

  Swal.fire({
    title: 'Konfirmasi Persetujuan',
    text: 'Apakah Anda yakin ingin menyetujui pembayaran ini?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Ya, Setujui',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      router.post(route('subscriptions.invoice.status'), {
        payment_id: selectedPayment.value.id,
        status: 'completed'
      }, {
        onSuccess: () => {
          Swal.fire(
            'Berhasil!',
            'Pembayaran telah disetujui.',
            'success'
          );
          showApprovalMenu.value = false;
        }
      });
    }
  });
}

// Handle reject payment
function rejectPayment() {
  if (!selectedPayment.value) return;

  Swal.fire({
    title: 'Konfirmasi Penolakan',
    text: 'Apakah Anda yakin ingin menolak pembayaran ini?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Ya, Tolak',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      router.post(route('subscriptions.invoice.status'), {
        payment_id: selectedPayment.value.id,
        status: 'failed'
      }, {
        onSuccess: () => {
          Swal.fire(
            'Berhasil!',
            'Pembayaran telah ditolak.',
            'success'
          );
          showApprovalMenu.value = false;
        }
      });
    }
  });
}

// Toggle approval menu
function toggleApprovalMenu() {
  showApprovalMenu.value = !showApprovalMenu.value;
}
</script>

<template>
    <Head title="Invoice History" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold">Invoice History</h1>
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
                                placeholder="Search invoice..."
                            />
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-12">No.</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Invoice Date</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Device</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Subscription</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Amount</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-24">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="(payment, index) in payments.data" :key="payment.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="px-4 py-2 whitespace-nowrap text-sm">{{ payments.from + index }}</td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm">{{ formatDate(payment.created_at) }}</td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm">
                                    {{ payment.device ? payment.device.name : '-' }}
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm">
                                    <span v-if="payment.subscription"
                                          class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                        {{ payment.subscription.name }}
                                    </span>
                                    <span v-else class="text-gray-400">-</span>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm font-medium">{{ formatPrice(payment.price) }}</td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm">
                                    <div class="flex items-center">
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2 py-1 rounded-full text-xs font-medium"
                                            :class="getStatusClass(payment.status)"
                                        >
                                            <component :is="getStatusIcon(payment.status)" class="h-3 w-3" />
                                            {{ getStatusLabel(payment.status) }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap">
                                    <div class="flex space-x-2">
                                        <button
                                            @click="viewInvoiceDetails(payment)"
                                            class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-input bg-background p-0 hover:bg-accent hover:text-accent-foreground relative group"
                                            title="View Invoice Details">
                                            <EyeIcon class="h-3.5 w-3.5" />
                                            <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap pointer-events-none">
                                                View Details
                                            </span>
                                        </button>
                                        <!-- <Link
                                            href="#"
                                            class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-input bg-background p-0 hover:bg-accent hover:text-accent-foreground relative group"
                                            title="Download Invoice">
                                            <DownloadIcon class="h-3.5 w-3.5" />
                                            <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap pointer-events-none">
                                                Download Invoice
                                            </span>
                                        </Link> -->
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="payments.data.length === 0">
                                <td colspan="7" class="px-4 py-3 text-center text-gray-500 dark:text-gray-400">
                                    No payment history found
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
                            <span class="font-medium mx-1">{{ payments.from || 0 }}</span>
                            to
                            <span class="font-medium mx-1">{{ payments.to || 0 }}</span>
                            of
                            <span class="font-medium mx-1">{{ payments.total }}</span>
                            results
                        </div>

                        <nav class="flex flex-wrap justify-center gap-2" aria-label="Pagination">
                            <template v-for="(link, key) in payments.links" :key="key">
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

        <!-- Invoice Details Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50">
            <div class="relative bg-white dark:bg-gray-900 rounded-lg shadow-lg w-full max-w-3xl max-h-[90vh] overflow-y-auto">
                <!-- Modal header -->
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t border-gray-200 dark:border-gray-700">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                        Invoice Detail
                    </h3>
                    <button
                        @click="closeModal"
                        type="button"
                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                    >
                        <XIcon class="w-4 h-4" />
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>

                <!-- Modal body -->
                <div class="p-4 md:p-5 space-y-4">
                    <div v-if="selectedPayment" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Invoice Number</div>
                                <div class="mt-1 text-base font-semibold">#{{ selectedPayment.id }}</div>
                            </div>

                            <div>
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Invoice Date</div>
                                <div class="mt-1">{{ formatDateTime(selectedPayment.created_at) }}</div>
                            </div>

                            <div>
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</div>
                                <div class="mt-1 flex items-center gap-2">
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium"
                                        :class="getStatusClass(selectedPayment.status)"
                                    >
                                        <component :is="getStatusIcon(selectedPayment.status)" class="h-3.5 w-3.5" />
                                        {{ getStatusLabel(selectedPayment.status) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Device</div>
                                <div class="mt-1">{{ selectedPayment.device ? selectedPayment.device.name : '-' }}</div>
                            </div>

                            <div>
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Subscription Plan</div>
                                <div class="mt-1">
                                    <span
                                        v-if="selectedPayment.subscription"
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300"
                                    >
                                        {{ selectedPayment.subscription.name }}
                                    </span>
                                    <span v-else class="text-gray-400">-</span>
                                </div>
                            </div>

                            <div>
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Phone Number</div>
                                <div class="mt-1">{{ selectedPayment.device ? selectedPayment.device.phone : '-' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                        <h4 class="text-lg font-medium mb-3">Payment Details</h4>
                        <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
                            <div class="flex flex-col space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Subscription Plan</span>
                                    <span class="font-medium">{{ selectedPayment?.subscription?.name || '-' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Message Limit</span>
                                    <span class="font-medium">{{ selectedPayment?.subscription?.limit.toLocaleString() || '-' }} messages</span>
                                </div>
                                <div class="border-t border-gray-200 dark:border-gray-700 my-2"></div>
                                <div class="flex justify-between items-center">
                                    <span class="text-lg font-semibold">Total Amount</span>
                                    <span class="text-lg font-bold">{{ formatPrice(selectedPayment?.price) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="selectedPayment?.image" class="border-t border-gray-200 dark:border-gray-700 pt-4">
                        <h4 class="text-lg font-medium mb-3">Payment Proof</h4>
                        <div class="flex justify-center">
                            <div class="w-full max-w-md">
                                <img
                                    :src="getStorageImageUrl(selectedPayment.image)"
                                    alt="Payment Proof"
                                    class="w-full h-auto rounded-lg shadow-md object-contain bg-gray-100 dark:bg-gray-800 p-2"
                                    @click="window.open(getStorageImageUrl(selectedPayment.image), '_blank')"
                                    style="max-height: 300px; cursor: pointer;"
                                />
                                <p class="text-xs text-center mt-2 text-gray-500 dark:text-gray-400">Click on image to view full size</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal footer -->
                <div class="flex items-center p-4 md:p-5 border-t border-gray-200 dark:border-gray-700 rounded-b gap-3">
                    <button
                        @click="closeModal"
                        type="button"
                        class="py-2.5 px-5 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700"
                    >
                        Close
                    </button>
                    <div v-if="isAdmin() && selectedPayment?.status === 'pending'" class="flex gap-3">
                        <button
                            @click="approvePayment"
                            class="py-2.5 px-5 text-sm font-medium text-white focus:outline-none bg-green-500 rounded-lg border border-green-600 hover:bg-green-600 focus:z-10 focus:ring-4 focus:ring-green-100 dark:focus:ring-green-700 dark:bg-green-800 dark:text-green-400 dark:border-green-600 dark:hover:text-white dark:hover:bg-green-700"
                        >
                            Approve
                        </button>
                        <button
                            @click="rejectPayment"
                            class="py-2.5 px-5 text-sm font-medium text-white focus:outline-none bg-red-500 rounded-lg border border-red-600 hover:bg-red-600 focus:z-10 focus:ring-4 focus:ring-red-100 dark:focus:ring-red-700 dark:bg-red-800 dark:text-red-400 dark:border-red-600 dark:hover:text-white dark:hover:bg-red-700"
                        >
                            Reject
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Animation for modal */
.modal-enter-active, .modal-leave-active {
  transition: opacity 0.3s ease;
}
.modal-enter-from, .modal-leave-to {
  opacity: 0;
}
</style>
