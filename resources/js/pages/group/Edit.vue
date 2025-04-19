<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea, Badge, Switch } from '@/components/ui';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { XIcon } from 'lucide-vue-next';

const props = defineProps({
  group: Object,
  contacts: Array,
  groupTypes: Array,
  selectedContacts: Array
});

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Groups',
    href: '/groups',
  },
  {
    title: 'Edit',
    href: `/groups/${props.group.id}/edit`,
  },
];

const form = useForm({
  type: props.group.type,
  subject: props.group.subject,
  description: props.group.description || '',
  groupID: props.group.groupID || '',
  contact_ids: props.selectedContacts || []
});

const searchContact = ref('');
const showContactSelector = ref(false);

const filteredContacts = computed(() => {
  if (!searchContact.value) return props.contacts;
  const searchTerm = searchContact.value.toLowerCase();
  return props.contacts.filter(
    contact => contact.name.toLowerCase().includes(searchTerm) ||
               contact.phone.toLowerCase().includes(searchTerm)
  );
});

const selectedContactsData = computed(() => {
  return props.contacts.filter(contact => form.contact_ids.includes(contact.id));
});

function addContact(contactId) {
  if (!form.contact_ids.includes(contactId)) {
    form.contact_ids.push(contactId);
  }
}

function removeContact(contactId) {
  const index = form.contact_ids.indexOf(contactId);
  if (index !== -1) {
    form.contact_ids.splice(index, 1);
  }
}

function submitForm() {
  form.put(route('groups.update', props.group.id), {
    onSuccess: () => {
      router.visit(route('groups.index'));
    }
  });
}

const isWhatsAppGroup = computed(() => form.type === 'whatsapp');
</script>

<template>
  <Head title="Edit Group" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">Edit Group</h1>
      </div>

      <div class="bg-white dark:bg-gray-900 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
        <form @submit.prevent="submitForm">
          <div class="grid grid-cols-1 gap-6">
            <!-- Group Type -->
            <div>
              <Label for="type">Group Type</Label>
              <div class="mt-2 flex flex-wrap gap-3">
                <div
                  v-for="type in groupTypes"
                  :key="type.value"
                  class="flex items-center"
                >
                  <input
                    type="radio"
                    :id="type.value"
                    v-model="form.type"
                    :value="type.value"
                    class="mr-2"
                    disabled
                  />
                  <Label :for="type.value" class="cursor-pointer">{{ type.label }}</Label>
                </div>
              </div>
              <div v-if="form.errors.type" class="text-sm text-red-500 mt-1">{{ form.errors.type }}</div>
            </div>

            <!-- Subject -->
            <div>
              <Label for="subject">Subject</Label>
              <Input
                id="subject"
                v-model="form.subject"
                type="text"
                class="mt-1"
                :class="{ 'border-red-500': form.errors.subject }"
                placeholder="Group name"
              />
              <div v-if="form.errors.subject" class="text-sm text-red-500 mt-1">{{ form.errors.subject }}</div>
            </div>

            <!-- Description -->
            <div>
              <Label for="description">Description</Label>
              <Textarea
                id="description"
                v-model="form.description"
                class="mt-1"
                :class="{ 'border-red-500': form.errors.description }"
                placeholder="Group description"
                rows="3"
              />
              <div v-if="form.errors.description" class="text-sm text-red-500 mt-1">{{ form.errors.description }}</div>
            </div>

            <!-- Group ID Field (only for WhatsApp Group) -->
            <div v-if="isWhatsAppGroup">
              <Label for="groupID">WhatsApp Group ID</Label>
              <Input
                id="groupID"
                v-model="form.groupID"
                type="text"
                class="mt-1"
                :class="{ 'border-red-500': form.errors.groupID }"
                placeholder="WhatsApp group ID (optional)"
              />
              <div class="text-xs text-gray-500 mt-1">WhatsApp group ID (optional)</div>
              <div v-if="form.errors.groupID" class="text-sm text-red-500 mt-1">{{ form.errors.groupID }}</div>
            </div>

            <!-- Contacts Section -->
            <div>
              <div class="flex justify-between items-center">
                <Label>Contacts</Label>
                <Switch
                  v-model="showContactSelector"
                  class="data-[state=checked]:bg-blue-500"
                />
              </div>

              <!-- Contact Selector -->
              <div v-if="showContactSelector" class="mt-3 border border-gray-200 dark:border-gray-700 rounded-md p-4">
                <div class="mb-4">
                  <Input
                    v-model="searchContact"
                    type="text"
                    placeholder="Search contacts..."
                    class="w-full"
                  />
                </div>

                <div class="max-h-60 overflow-y-auto">
                  <div
                    v-for="contact in filteredContacts"
                    :key="contact.id"
                    class="flex justify-between items-center py-2 px-3 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-md cursor-pointer"
                    @click="addContact(contact.id)"
                  >
                    <div>
                      <div class="font-medium">{{ contact.name }}</div>
                      <div class="text-sm text-gray-500">{{ contact.phone }}</div>
                    </div>
                    <div>
                      <Button
                        type="button"
                        variant="ghost"
                        size="sm"
                        class="h-7 w-7 p-0"
                        @click.stop="addContact(contact.id)"
                      >
                        <span class="text-xs">+</span>
                      </Button>
                    </div>
                  </div>

                  <div v-if="filteredContacts.length === 0" class="py-3 text-center text-gray-500">
                    No contacts found
                  </div>
                </div>
              </div>

              <!-- Selected Contacts -->
              <div class="mt-3">
                <div class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">Selected Contacts: {{ selectedContactsData.length }}</div>
                <div class="flex flex-wrap gap-2">
                  <Badge
                    v-for="contact in selectedContactsData"
                    :key="contact.id"
                    variant="secondary"
                    class="flex items-center gap-1 pl-3 pr-1 py-1.5"
                  >
                    <span>{{ contact.name }}</span>
                    <button
                      type="button"
                      class="ml-1 h-4 w-4 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center hover:bg-gray-300 dark:hover:bg-gray-600"
                      @click="removeContact(contact.id)"
                    >
                      <XIcon class="h-3 w-3" />
                    </button>
                  </Badge>
                  <div v-if="selectedContactsData.length === 0" class="text-gray-500 text-sm">
                    No contacts selected
                  </div>
                </div>
                <div v-if="form.errors.contact_ids" class="text-sm text-red-500 mt-1">{{ form.errors.contact_ids }}</div>
              </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end gap-4">
              <Button
                type="button"
                variant="outline"
                :disabled="form.processing"
                @click="router.visit(route('groups.index'))"
              >
                Cancel
              </Button>
              <Button
                type="submit"
                :disabled="form.processing"
                :class="{ 'opacity-75 cursor-not-allowed': form.processing }"
              >
                {{ form.processing ? 'Updating...' : 'Update Group' }}
              </Button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
