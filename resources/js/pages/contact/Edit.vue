<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardFooter } from '@/components/ui/card';
import { Head, useForm, Link } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { UserIcon, PhoneIcon } from 'lucide-vue-next';

// Receive contact data from the controller
const props = defineProps({
  contact: Object
});

const breadcrumbs = [
    {
        title: 'Contacts',
        href: route('contacts.index'),
    },
    {
        title: 'Edit',
        href: route('contacts.edit', props.contact.id),
    },
];

// Initialize form with existing contact data
const form = useForm({
  name: props.contact.name,
  phone: props.contact.phone,
  is_active: props.contact.is_active,
});

function submit() {
  form.put(route('contacts.update', props.contact.id), {
    onSuccess: () => {
      Swal.fire({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        icon: 'success',
        title: 'Contact updated successfully'
      })
    },
    onError: () => {
      Swal.fire({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        icon: 'error',
        title: 'Failed to update contact'
      });
    },
  });
}
</script>

<template>
  <Head title="Edit Contact" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <div class="flex justify-between items-center mb-2">
        <h1 class="text-2xl font-bold">Edit Contact</h1>
      </div>

      <Card class="overflow-hidden border-gray-200 dark:border-gray-800">
        <form @submit.prevent="submit">
          <CardContent class="p-6">
            <div class="grid gap-6">
              <!-- Contact Information -->
              <div class="space-y-4">
                <h3 class="text-md font-medium flex items-center gap-2">
                  Contact Information
                </h3>

                <div class="space-y-2">
                  <Label for="name" class="font-medium">Contact Name</Label>
                  <div class="flex items-center">
                    <UserIcon class="h-4 w-4 mr-2 text-gray-400" />
                    <Input
                      id="name"
                      v-model="form.name"
                      type="text"
                      placeholder="Enter contact name"
                      :error="form.errors.name"
                      required
                      class="bg-white dark:bg-gray-900"
                    />
                  </div>
                  <p v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</p>
                </div>

                <div class="space-y-2">
                  <Label for="phone" class="font-medium">Phone Number</Label>
                  <div class="flex items-center">
                    <PhoneIcon class="h-4 w-4 mr-2 text-gray-400" />
                    <Input
                      id="phone"
                      v-model="form.phone"
                      type="text"
                      placeholder="Enter phone number (with country code)"
                      :error="form.errors.phone"
                      required
                      class="bg-white dark:bg-gray-900"
                    />
                  </div>
                  <p v-if="form.errors.phone" class="text-sm text-red-500">{{ form.errors.phone }}</p>
                  <p class="text-xs text-gray-500">Example: 628123456789 (without + or spaces)</p>
                </div>
              </div>
            </div>
          </CardContent>

          <CardFooter class="flex justify-between">
            <Link :href="route('contacts.index')">
              <Button type="button" variant="outline">Cancel</Button>
            </Link>
            <Button type="submit" :disabled="form.processing">Update Contact</Button>
          </CardFooter>
        </form>
      </Card>
    </div>
  </AppLayout>
</template>
