<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Separator } from '@/components/ui/separator';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
  plans: Array
});

const selectedPlan = ref(null);
const breadcrumbs = [
  {
    title: 'Subscriptions',
    href: route('subscriptions.index'),
  },
  {
    title: 'Plans',
  },
];

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

const showPlanDetails = (plan) => {
  selectedPlan.value = plan;
};

const buyPlan = (plan) => {
  router.visit(route('subscriptions.checkout', { id: plan.id }));
};
</script>

<template>
  <Head title="Subscription Plans" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-white">Subscription Plans</h2>
      </div>
    </template>

    <div class="py-6">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="plan in plans" :key="plan.id"
               class="overflow-hidden rounded-xl border border-border bg-card shadow-sm transition-all duration-200 hover:shadow-md">
            <div class="p-6 bg-primary/10 border-b border-border">
              <h3 class="text-xl font-bold text-foreground mb-2">{{ plan.name }}</h3>
              <p class="text-2xl font-bold text-primary mb-1">
                {{ formatPrice(plan.price) }}
                <span class="text-sm font-normal text-muted-foreground">/month</span>
              </p>
              <p class="text-sm text-muted-foreground">
                <span class="font-medium">Message Quota:</span> {{ formatNumber(plan.quota) }}
              </p>
            </div>

            <div class="p-6">
              <div class="space-y-3 mb-6">
                <div v-for="(detail, index) in plan.details?.slice(0, 5) || []" :key="index"
                     class="flex items-center text-sm">
                  <div v-if="detail.is_checklist" class="mr-2 h-4 w-4 text-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                      <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                  </div>
                  <div v-else class="mr-2 h-4 w-4 text-muted-foreground">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                      <path d="M18 6 6 18"></path>
                      <path d="m6 6 12 12"></path>
                    </svg>
                  </div>
                  <span :class="detail.is_checklist ? 'text-foreground' : 'text-muted-foreground'">{{ detail.item }}</span>
                </div>
              </div>

              <div class="flex gap-3">
                <Button
                  variant="outline"
                  class="flex-1"
                  @click="showPlanDetails(plan)"
                >
                  Details
                </Button>
                <Button
                  class="flex-1"
                  @click="buyPlan(plan)"
                >
                  Buy Now
                </Button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Plan Details Modal -->
    <Dialog :open="!!selectedPlan" @update:open="selectedPlan = null">
      <DialogContent class="sm:max-w-[525px]">
        <DialogHeader>
          <DialogTitle>{{ selectedPlan?.name }} Plan Details</DialogTitle>
          <DialogDescription>
            Get more information about this subscription plan
          </DialogDescription>
        </DialogHeader>
        <div v-if="selectedPlan" class="py-4">
          <div class="mb-4">
            <p class="text-2xl font-bold text-primary">
              {{ formatPrice(selectedPlan.price) }}
              <span class="text-sm font-normal text-muted-foreground">/month</span>
            </p>
            <p class="text-sm text-foreground mt-2">
              <span class="font-medium">Message Quota:</span> {{ formatNumber(selectedPlan.quota) }}
            </p>
          </div>

          <Separator />

          <div class="pt-4">
            <h4 class="font-medium text-foreground mb-3">Features</h4>
            <div class="grid grid-cols-1 gap-y-2">
              <div v-for="(detail, index) in selectedPlan.details" :key="index"
                   class="flex items-center text-sm">
                <div v-if="detail.is_checklist" class="mr-2 h-4 w-4 text-primary">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                    <polyline points="20 6 9 17 4 12"></polyline>
                  </svg>
                </div>
                <div v-else class="mr-2 h-4 w-4 text-muted-foreground">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                    <path d="M18 6 6 18"></path>
                    <path d="m6 6 12 12"></path>
                  </svg>
                </div>
                <span :class="detail.is_checklist ? 'text-foreground' : 'text-muted-foreground'">{{ detail.item }}</span>
              </div>
            </div>
          </div>
        </div>
        <DialogFooter>
          <Button variant="outline" @click="selectedPlan = null">Close</Button>
          <Button @click="buyPlan(selectedPlan)">Buy Now</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>
