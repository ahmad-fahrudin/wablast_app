<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea, Badge, Pagination } from '@/components/ui';
import { type BreadcrumbItem } from '@/types';
import axios from 'axios';
import { XIcon, SearchIcon } from 'lucide-vue-next';

const props = defineProps({
  devices: Array,
  contacts: Array,
  groups: Array
});

const successMessage = ref('');
const errorMessage = ref('');
const searchContact = ref('');
const showContactSelector = ref(false);
const currentPage = ref(1);
const contactsPerPage = ref(10);
const paginatedContacts = ref([]);
const totalContacts = ref(0);
const isLoading = ref(false);

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Messages',
    href: '/messages',
  },
  {
    title: 'Send Text',
    href: '/messages/text',
  },
];

const form = useForm({
  deviceId: '',
  recipientType: 'contact',
  contacts: [],
  groups: [],
  message: '',
});

// Get paginated contacts from API
const fetchContacts = async (page = 1, search = '') => {
  isLoading.value = true;
  try {
    const response = await axios.get('/contacts-paginate', {
      params: {
        page,
        search,
        perPage: contactsPerPage.value
      }
    });

    paginatedContacts.value = response.data.data;
    totalContacts.value = response.data.total;
    currentPage.value = response.data.current_page;
  } catch (error) {
    console.error('Error fetching contacts:', error);
  } finally {
    isLoading.value = false;
  }
};

// When search input changes, fetch with new search term
const handleSearchChange = () => {
  currentPage.value = 1; // Reset to first page when searching
  fetchContacts(1, searchContact.value);
};

// When page changes in pagination
const handlePageChange = (page) => {
  currentPage.value = page;
  fetchContacts(page, searchContact.value);
};

// When contact selector is opened, load contacts
watch(showContactSelector, (newValue) => {
  if (newValue) {
    fetchContacts(currentPage.value, searchContact.value);
  }
});

// Get selected contacts
const selectedContacts = computed(() => {
  // Map the contact IDs to actual contact objects
  return form.contacts.map(contactId => {
    const id = typeof contactId === 'object' ? contactId.id : contactId;

    // First try to find the contact in the paginatedContacts (from API)
    let contact = paginatedContacts.value.find(c => c.id === id);

    // If not found in paginated contacts, try the initial contacts prop
    if (!contact && props.contacts && Array.isArray(props.contacts)) {
      contact = props.contacts.find(c => c.id === id);
    }

    // If still not found, create a minimal contact object with just the ID
    if (!contact) {
      console.log('Contact not found for ID:', id);
      return { id: id, name: `Contact ${id}` };
    }

    return contact;
  });
});

const broadcastGroupOptions = computed(() => {
  if (!props.groups || !Array.isArray(props.groups)) {
    return [];
  }

  return props.groups
    .filter(group => group.type === 'contact')
    .map(group => ({
      id: group.id,
      subject: group.subject,
      description: group.description
    }));
});

const whatsappGroupOptions = computed(() => {
  if (!props.groups || !Array.isArray(props.groups)) {
    return [];
  }

  return props.groups
    .filter(group => group.type === 'whatsapp')
    .map(group => ({
      id: group.id,
      subject: group.subject,
      description: group.description
    }));
});

// Helper functions for contact management
function addContact(contactId, contactObject = null) {
  console.log('Adding contact:', contactId);

  // Make sure we're working with consistent data type (always use the ID value)
  const id = typeof contactId === 'object' ? contactId.id : contactId;

  // Check if this contact is already selected
  const contactIds = form.contacts.map(c => typeof c === 'object' ? c.id : c)
  console.log('Current contact IDs:', contactIds);

  if (!contactIds.includes(id)) {
    console.log('Adding contact ID to form:', id);
    // If we have the full contact object, store it to avoid additional lookups
    if (contactObject) {
      form.contacts.push(contactObject);
    } else {
      form.contacts.push(id);
    }
  } else {
    console.log('Contact already added, skipping:', id);
  }
}

function removeContact(contactId) {
  const index = form.contacts.findIndex(c =>
    typeof c === 'object' ? c.id === contactId : c === contactId
  );
  if (index !== -1) {
    form.contacts.splice(index, 1);
  }
}

// Helper functions for group management
function addGroup(groupId) {
  if (!form.groups.includes(groupId)) {
    form.groups.push(groupId);
  }
}

function removeGroup(groupId) {
  const index = form.groups.indexOf(groupId);
  if (index !== -1) {
    form.groups.splice(index, 1);
  }
}

// Get selected groups
const selectedBroadcastGroups = computed(() => {
  return broadcastGroupOptions.value.filter(group => form.groups.includes(group.id));
});

const selectedWhatsappGroups = computed(() => {
  return whatsappGroupOptions.value.filter(group => form.groups.includes(group.id));
});

const sendMessage = async () => {
  form.clearErrors();

  // Validate form
  let isValid = true;

  if (!form.deviceId) {
    form.setError('deviceId', 'Please select a device');
    isValid = false;
  }

  if (!form.recipientType) {
    form.setError('recipientType', 'Please select recipient type');
    isValid = false;
  }

  if (form.recipientType === 'contact' && form.contacts.length === 0) {
    form.setError('contacts', 'Please select at least one contact');
    isValid = false;
  }

  if ((form.recipientType === 'broadcast' || form.recipientType === 'group') && form.groups.length === 0) {
    form.setError('groups', 'Please select at least one group');
    isValid = false;
  }

  if (!form.message.trim()) {
    form.setError('message', 'Please enter a message');
    isValid = false;
  }

  if (!isValid) {
    return;
  }

  form.processing = true;
  errorMessage.value = '';
  successMessage.value = '';

  try {
    const payload = {
      deviceId: form.deviceId,
      recipientType: form.recipientType,
      recipients: form.recipientType === 'contact'
        ? form.contacts.map(contact => typeof contact === 'object' ? contact.id : contact)
        : form.groups.map(group => typeof group === 'object' ? group.id : group),
      message: form.message
    };

    const response = await axios.post('/api/messages/send', payload);

    if (response.data.success) {
      successMessage.value = response.data.message;
      // Reset form values
      form.message = '';
      form.contacts = [];
      form.groups = [];
    } else {
      errorMessage.value = response.data.message || 'Failed to send message';
    }
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'An error occurred while sending the message';
    console.error('Error sending message:', error);
  } finally {
    form.processing = false;
  }
};
</script>

<template>
  <Head title="Send Text Message" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">Send WhatsApp Text Message</h1>
      </div>

      <!-- Success and Error Messages -->
      <div v-if="successMessage" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
        {{ successMessage }}
      </div>

      <div v-if="errorMessage" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
        {{ errorMessage }}
      </div>

      <!-- Form Container -->
      <div class="bg-white dark:bg-gray-900 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6">
        <form @submit.prevent="sendMessage">
          <div class="grid grid-cols-1 gap-6">
            <!-- Device Selection -->
            <div>
              <Label for="deviceId">Select Device</Label>
              <select
                id="deviceId"
                v-model="form.deviceId"
                class="w-full mt-1 rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Select a device</option>
                <option
                  v-for="device in devices"
                  :key="device.id"
                  :value="device.deviceID"
                  :disabled="!device.is_connected"
                >
                  {{ device.name }} {{ !device.is_connected ? '(Disconnected)' : '' }}
                </option>
              </select>
              <div v-if="form.errors.deviceId" class="text-sm text-red-500 mt-1">{{ form.errors.deviceId }}</div>
            </div>

            <!-- Recipient Type Selection -->
            <div>
              <Label>Send To</Label>
              <div class="mt-2 flex flex-wrap gap-3">
                <div class="flex items-center">
                  <input
                    type="radio"
                    id="type-contact"
                    v-model="form.recipientType"
                    value="contact"
                    class="mr-2"
                  />
                  <Label for="type-contact" class="cursor-pointer">Individual Contacts</Label>
                </div>
                <div class="flex items-center">
                  <input
                    type="radio"
                    id="type-broadcast"
                    v-model="form.recipientType"
                    value="broadcast"
                    class="mr-2"
                  />
                  <Label for="type-broadcast" class="cursor-pointer">Contact Groups</Label>
                </div>
                <div class="flex items-center">
                  <input
                    type="radio"
                    id="type-group"
                    v-model="form.recipientType"
                    value="group"
                    class="mr-2"
                  />
                  <Label for="type-group" class="cursor-pointer">WhatsApp Groups</Label>
                </div>
              </div>
              <div v-if="form.errors.recipientType" class="text-sm text-red-500 mt-1">{{ form.errors.recipientType }}</div>
            </div>

            <!-- Contacts Section (when Individual Contacts is selected) -->
            <div v-if="form.recipientType === 'contact'">
              <div class="flex justify-between items-center">
                <Label>Contacts</Label>
                <Button
                  type="button"
                  variant="outline"
                  size="sm"
                  @click="showContactSelector = !showContactSelector"
                >
                  {{ showContactSelector ? 'Hide' : 'Show' }} Contact Selector
                </Button>
              </div>

              <!-- Contact Selector with Pagination -->
              <div v-if="showContactSelector" class="mt-3 border border-gray-200 dark:border-gray-700 rounded-md p-4">
                <!-- Search Box -->
                <div class="mb-4 relative">
                  <Input
                    v-model="searchContact"
                    type="text"
                    placeholder="Search contacts..."
                    class="w-full pr-10"
                    @input="handleSearchChange"
                  />
                  <SearchIcon class="absolute right-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                </div>

                <!-- Loading Indicator -->
                <div v-if="isLoading" class="py-4 text-center text-gray-500">
                  Loading contacts...
                </div>

                <!-- Contacts List -->
                <div v-else class="max-h-60 overflow-y-auto">
                  <div
                    v-for="contact in paginatedContacts"
                    :key="contact.id"
                    class="flex justify-between items-center py-2 px-3 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-md cursor-pointer"
                    @click="addContact(contact.id, contact)"
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
                        @click.stop="addContact(contact.id, contact)"
                      >
                        <span class="text-xs">+</span>
                      </Button>
                    </div>
                  </div>

                  <div v-if="paginatedContacts.length === 0 && !isLoading" class="py-3 text-center text-gray-500">
                    No contacts found
                  </div>
                </div>

                <!-- Pagination -->
                <div v-if="totalContacts > contactsPerPage" class="mt-4 flex justify-center">
                  <Pagination
                    :total="totalContacts"
                    :per-page="contactsPerPage"
                    :current-page="currentPage"
                    @page-change="handlePageChange"
                  />
                </div>
              </div>

              <!-- Selected Contacts -->
              <div class="mt-3">
                <div class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">Selected Contacts: {{ selectedContacts.length }}</div>
                <div class="flex flex-wrap gap-2">
                  <Badge
                    v-for="contact in selectedContacts"
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
                  <div v-if="selectedContacts.length === 0" class="text-gray-500 text-sm">
                    No contacts selected
                  </div>
                </div>
                <div v-if="form.errors.contacts" class="text-sm text-red-500 mt-1">{{ form.errors.contacts }}</div>
              </div>
            </div>

            <!-- Broadcast Groups Selection -->
            <div v-if="form.recipientType === 'broadcast'">
              <Label>Select Contact Groups</Label>
              <div v-if="broadcastGroupOptions.length === 0" class="mt-2 text-gray-500">
                No broadcast groups available
              </div>
              <div v-else class="mt-2">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                  <div
                    v-for="group in broadcastGroupOptions"
                    :key="group.id"
                    class="border border-gray-200 dark:border-gray-700 rounded-md p-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800"
                    :class="{'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800': form.groups.includes(group.id)}"
                    @click="form.groups.includes(group.id) ? removeGroup(group.id) : addGroup(group.id)"
                  >
                    <div class="font-medium">{{ group.subject }}</div>
                    <div class="text-sm text-gray-500">{{ group.description }}</div>
                  </div>
                </div>
              </div>
              <div class="mt-3">
                <div class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">Selected Groups: {{ selectedBroadcastGroups.length }}</div>
                <div class="flex flex-wrap gap-2">
                  <Badge
                    v-for="group in selectedBroadcastGroups"
                    :key="group.id"
                    variant="secondary"
                    class="flex items-center gap-1 pl-3 pr-1 py-1.5"
                  >
                    <span>{{ group.subject }}</span>
                    <button
                      type="button"
                      class="ml-1 h-4 w-4 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center hover:bg-gray-300 dark:hover:bg-gray-600"
                      @click="removeGroup(group.id)"
                    >
                      <XIcon class="h-3 w-3" />
                    </button>
                  </Badge>
                </div>
              </div>
              <div v-if="form.errors.groups" class="text-sm text-red-500 mt-1">{{ form.errors.groups }}</div>
            </div>

            <!-- WhatsApp Groups Selection -->
            <div v-if="form.recipientType === 'group'">
              <Label>Select WhatsApp Groups</Label>
              <div v-if="whatsappGroupOptions.length === 0" class="mt-2 text-gray-500">
                No WhatsApp groups available
              </div>
              <div v-else class="mt-2">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                  <div
                    v-for="group in whatsappGroupOptions"
                    :key="group.id"
                    class="border border-gray-200 dark:border-gray-700 rounded-md p-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800"
                    :class="{'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800': form.groups.includes(group.id)}"
                    @click="form.groups.includes(group.id) ? removeGroup(group.id) : addGroup(group.id)"
                  >
                    <div class="font-medium">{{ group.subject }}</div>
                    <div class="text-sm text-gray-500">{{ group.description }}</div>
                  </div>
                </div>
              </div>
              <div class="mt-3">
                <div class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">Selected Groups: {{ selectedWhatsappGroups.length }}</div>
                <div class="flex flex-wrap gap-2">
                  <Badge
                    v-for="group in selectedWhatsappGroups"
                    :key="group.id"
                    variant="secondary"
                    class="flex items-center gap-1 pl-3 pr-1 py-1.5"
                  >
                    <span>{{ group.subject }}</span>
                    <button
                      type="button"
                      class="ml-1 h-4 w-4 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center hover:bg-gray-300 dark:hover:bg-gray-600"
                      @click="removeGroup(group.id)"
                    >
                      <XIcon class="h-3 w-3" />
                    </button>
                  </Badge>
                </div>
              </div>
              <div v-if="form.errors.groups" class="text-sm text-red-500 mt-1">{{ form.errors.groups }}</div>
            </div>

            <!-- Message Content -->
            <div>
              <Label for="message">Message</Label>
              <Textarea
                id="message"
                v-model="form.message"
                class="mt-1"
                :class="{ 'border-red-500': form.errors.message }"
                placeholder="Enter your message here..."
                rows="6"
              />
              <div v-if="form.errors.message" class="text-sm text-red-500 mt-1">{{ form.errors.message }}</div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
              <Button
                type="submit"
                :disabled="form.processing"
                :class="{ 'opacity-75 cursor-not-allowed': form.processing }"
              >
                {{ form.processing ? 'Sending...' : 'Send Message' }}
              </Button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
