<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { Loader2Icon, AlertCircleIcon } from 'lucide-vue-next';

const props = defineProps({
  plan: Object,
  devices: {
    type: Array,
    default: () => []
  }
});

const paymentMethods = [
  { id: 'bank', name: 'Bank Transfer', description: 'Manual transfer to our bank account' },
  { id: 'midtrans', name: 'Credit Card / E-Wallet', description: 'Pay with credit card, GoPay, OVO, etc.' },
];

const selectedPaymentMethod = ref('');
const selectedDevice = ref('');
const isProcessing = ref(false);
const orderId = ref('');
const paymentProof = ref(null);
const uploadedFileName = ref('');
const isUploading = ref(false);

const breadcrumbs = [
  {
    title: 'Subscriptions',
    href: route('subscriptions.index'),
  },
  {
    title: 'Plans',
    href: route('subscriptions.plan'),
  },
  {
    title: 'Checkout',
  },
];

onMounted(() => {
  orderId.value = 'ORD' + Date.now().toString().slice(-8);

  // If there's only one device, select it by default
  if (props.devices.length === 1) {
    selectedDevice.value = props.devices[0].id;
  }
});

const formatPrice = (price) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(price);
};

const formatNumber = (number) => {
  return new Intl.NumberFormat('en-US').format(number);
};

const handleFileUpload = (event) => {
  const file = event.target.files[0];
  if (file) {
    isUploading.value = true;
    uploadedFileName.value = file.name;
    paymentProof.value = file;

    // Simulate upload process
    setTimeout(() => {
      isUploading.value = false;
    }, 1000);
  }
};

const goBack = () => {
  router.visit(route('subscriptions.plan'));
};

const processPayment = () => {
  if (!selectedDevice.value) {
    alert('Please select a device');
    return;
  }

  if (!selectedPaymentMethod.value) {
    alert('Please select a payment method');
    return;
  }

  isProcessing.value = true;

  // For bank transfer payment, validate that proof is uploaded
  if (selectedPaymentMethod.value === 'bank' && !paymentProof.value) {
    alert('Please upload payment proof');
    isProcessing.value = false;
    return;
  }

  // Create form data to handle file upload
  const formData = new FormData();
  formData.append('device_id', selectedDevice.value);
  formData.append('subscription_id', props.plan.id);
  formData.append('price', props.plan.price);
  formData.append('payment_method', selectedPaymentMethod.value);
  formData.append('order_id', orderId.value);

  // Only append payment proof for bank transfer
  if (selectedPaymentMethod.value === 'bank' && paymentProof.value) {
    formData.append('payment_proof', paymentProof.value);
  }

  // Submit payment to the backend
  router.post(route('subscriptions.payment'), formData, {
    onFinish: () => {
      isProcessing.value = false;
    },
    onSuccess: () => {
      router.visit(route('subscriptions.invoice'), {
        only: ['payments'],
      });
    },
  });
};
</script>

<template>
  <Head title="Checkout" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-white">Checkout: {{ plan.name }} Plan</h2>
      </div>
    </template>

    <div class="py-6">
      <div class="mx-auto max-w-3xl">
        <div class="overflow-hidden rounded-xl border border-border bg-card shadow">
          <div class="p-6 border-b border-border bg-primary/10">
            <p class="text-2xl font-bold text-primary">
              {{ formatPrice(plan.price) }}
              <span class="text-sm font-normal text-muted-foreground">/month</span>
            </p>
          </div>

          <div class="p-6">
            <div class="mb-8 space-y-6">
              <div>
                <h3 class="text-lg font-medium text-foreground mb-4">Plan Details</h3>
                <div class="space-y-3">
                  <div class="flex justify-between pb-2 border-b border-border">
                    <span class="text-muted-foreground">Message Quota</span>
                    <span class="font-medium text-foreground">{{ formatNumber(plan.quota) }}</span>
                  </div>
                  <div class="flex justify-between pb-2 border-b border-border">
                    <span class="text-muted-foreground">Plan</span>
                    <span class="font-medium text-foreground">{{ plan.name }}</span>
                  </div>
                  <div class="flex justify-between pb-2 border-b border-border">
                    <span class="text-muted-foreground">Validity</span>
                    <span class="font-medium text-foreground">30 days</span>
                  </div>
                  <div class="flex justify-between pb-2 border-b border-border">
                    <span class="text-muted-foreground">Total</span>
                    <span class="font-bold text-primary">{{ formatPrice(plan.price) }}</span>
                  </div>
                </div>
              </div>

              <Separator />

              <!-- Device Selection Section -->
              <div v-if="devices && devices.length > 0">
                <h3 class="text-lg font-medium text-foreground mb-4">Select Device</h3>
                <p class="text-sm text-muted-foreground mb-4">Choose which device you want to apply this subscription to:</p>

                <div class="space-y-3">
                  <div v-for="device in devices" :key="device.id"
                       class="flex items-center p-3 rounded-lg border cursor-pointer"
                       :class="selectedDevice === device.id ? 'border-primary bg-primary/5' : 'border-border hover:bg-accent'"
                       @click="selectedDevice = device.id">
                    <div class="min-w-5 mr-3">
                      <div class="h-5 w-5 rounded-full border flex items-center justify-center"
                           :class="selectedDevice === device.id ? 'border-primary' : 'border-muted-foreground'">
                        <div v-if="selectedDevice === device.id" class="h-3 w-3 rounded-full bg-primary"></div>
                      </div>
                    </div>
                    <div>
                      <p class="font-medium text-foreground">{{ device.name }}</p>
                      <p class="text-sm text-muted-foreground">{{ device.phone }}</p>
                    </div>
                    <div v-if="device.is_connected" class="ml-auto px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                      Connected
                    </div>
                    <div v-else class="ml-auto px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">
                      Not Connected
                    </div>
                  </div>
                </div>

                <div v-if="devices.length === 0" class="p-4 rounded-lg border border-yellow-200 bg-yellow-50 flex items-center space-x-2">
                  <AlertCircleIcon class="h-5 w-5 text-yellow-500" />
                  <p class="text-sm text-yellow-700">You don't have any devices without a subscription. <a href="/devices/create" class="text-primary underline">Create a device</a> first.</p>
                </div>
              </div>

              <Separator />

              <div>
                <h3 class="text-lg font-medium text-foreground mb-4">Payment Method</h3>
                <RadioGroup v-model="selectedPaymentMethod">
                  <div class="space-y-3">
                    <div v-for="method in paymentMethods" :key="method.id">
                      <RadioGroupItem
                        :value="method.id"
                        :id="method.id"
                        class="peer sr-only"
                      />
                      <Label
                        :for="method.id"
                        class="flex items-center rounded-lg border border-border p-4 cursor-pointer transition-all peer-checked:border-primary peer-checked:bg-primary/5 hover:bg-accent"
                      >
                        <div class="min-w-5 mr-3">
                          <div class="h-5 w-5 rounded-full border flex items-center justify-center" :class="selectedPaymentMethod === method.id ? 'border-primary' : 'border-muted-foreground'">
                            <div v-if="selectedPaymentMethod === method.id" class="h-3 w-3 rounded-full bg-primary"></div>
                          </div>
                        </div>
                        <div>
                          <p class="font-medium text-foreground">{{ method.name }}</p>
                          <p class="text-sm text-muted-foreground">{{ method.description }}</p>
                        </div>
                      </Label>
                    </div>
                  </div>
                </RadioGroup>
              </div>

              <Separator v-if="selectedPaymentMethod" />

              <div v-if="selectedPaymentMethod === 'bank'">
                <h3 class="text-lg font-medium text-foreground mb-4">Payment Details</h3>
                <div class="p-4 rounded-lg border border-border bg-accent/40 mb-4">
                  <p class="font-semibold mb-2 text-foreground">Bank Transfer Instructions:</p>
                  <ol class="list-decimal list-inside space-y-1 text-muted-foreground">
                    <li>Transfer the exact amount: {{ formatPrice(plan.price) }}</li>
                    <li>Include your Order ID as reference: #{{ orderId }}</li>
                    <li>After payment, upload your proof of payment below</li>
                  </ol>

                  <div class="mt-4 p-4 border border-primary/20 rounded-lg bg-primary/5 shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                      <p class="font-medium text-foreground text-lg">Bank Account Details</p>
                      <span class="px-2 py-1 bg-primary/10 text-primary text-xs rounded-md">Order #{{ orderId }}</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 mt-3">
                      <div class="text-muted-foreground">Bank Name:</div>
                      <div class="font-medium text-foreground">BCA</div>

                      <div class="text-muted-foreground">Account Number:</div>
                      <div class="font-medium text-foreground select-all">1234567890</div>

                      <div class="text-muted-foreground">Account Name:</div>
                      <div class="font-medium text-foreground">WA Blast Services</div>

                      <div class="text-muted-foreground">Amount:</div>
                      <div class="font-medium text-primary">{{ formatPrice(plan.price) }}</div>
                    </div>
                  </div>
                </div>

                <div class="mt-6">
                  <Label for="payment-proof" class="block mb-2">Upload Payment Proof</Label>
                  <div class="border-2 border-dashed border-border rounded-lg p-4 text-center hover:bg-accent/30 transition-colors cursor-pointer">
                    <input
                      id="payment-proof"
                      type="file"
                      accept="image/*,.pdf"
                      class="hidden"
                      @change="handleFileUpload"
                    />
                    <label for="payment-proof" class="cursor-pointer w-full h-full block">
                      <div v-if="!uploadedFileName" class="flex flex-col items-center justify-center py-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-muted-foreground mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="text-sm font-medium text-foreground">Click to upload payment proof</p>
                        <p class="text-xs text-muted-foreground mt-1">JPG, PNG, or PDF (max. 2MB)</p>
                      </div>
                      <div v-else class="flex items-center justify-center py-4">
                        <div v-if="isUploading" class="flex items-center">
                          <Loader2Icon class="h-5 w-5 text-primary animate-spin mr-2" />
                          <span class="text-sm text-muted-foreground">Uploading...</span>
                        </div>
                        <div v-else class="flex items-center">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                          </svg>
                          <span class="text-sm font-medium text-foreground">{{ uploadedFileName }}</span>
                          <button
                            type="button"
                            class="ml-2 text-sm text-red-500 underline"
                            @click.prevent="uploadedFileName = ''; paymentProof = null"
                          >
                            Remove
                          </button>
                        </div>
                      </div>
                    </label>
                  </div>
                  <p class="text-xs text-muted-foreground mt-2">
                    Your payment will be verified by our admin team. Once approved, your subscription will be activated.
                  </p>
                </div>
              </div>

              <div v-if="selectedPaymentMethod === 'midtrans'">
                <h3 class="text-lg font-medium text-foreground mb-4">Payment Details</h3>
                <div class="p-4 rounded-lg border border-border bg-accent/40">
                  <p class="text-muted-foreground">You will be redirected to Midtrans payment gateway to complete your payment securely.</p>
                </div>
              </div>
            </div>

            <div class="flex justify-between pt-4">
              <Button variant="outline" @click="goBack">
                Back to Plans
              </Button>
              <Button
                @click="processPayment"
                :disabled="!selectedPaymentMethod || !selectedDevice || isProcessing"
                :class="{ 'opacity-50 cursor-not-allowed': !selectedPaymentMethod || !selectedDevice || isProcessing }"
              >
                <Loader2Icon v-if="isProcessing" class="mr-2 h-4 w-4 animate-spin" />
                {{ isProcessing ? 'Processing...' : 'Complete Payment' }}
              </Button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
