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
import { XIcon, SearchIcon, PlusIcon } from 'lucide-vue-next';
import Swal from 'sweetalert2';

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
const manualPhone = ref('');
const manualName = ref('');
const saveManualContact = ref(true); // Default to save the contact

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

// Add a manual contact (phone number) to the selected contacts
function addManualContact() {
  if (!manualPhone.value.trim()) {
    Swal.fire({
      title: 'Error',
      text: 'Please enter a phone number',
      icon: 'error',
      confirmButtonText: 'OK'
    });
    return;
  }

  // Basic validation for phone number - should be numeric and have at least 10 digits
  const phoneRegex = /^\d{10,15}$/;
  if (!phoneRegex.test(manualPhone.value.replace(/\D/g, ''))) {
    Swal.fire({
      title: 'Invalid Phone Number',
      text: 'Please enter a valid phone number (10-15 digits)',
      icon: 'warning',
      confirmButtonText: 'OK'
    });
    return;
  }

  // Standardize phone number format
  let phoneNumber = manualPhone.value.replace(/\D/g, '');
  
  // Use provided name or generate a display name from the phone number if none provided
  const displayName = manualName.value.trim() || phoneNumber;

  // Check if the number already exists in contacts
  let existingContact = null;
  if (props.contacts && Array.isArray(props.contacts)) {
    existingContact = props.contacts.find(c => c.phone && c.phone.replace(/\D/g, '') === phoneNumber);
  }

  if (existingContact) {
    // If it exists in our contacts, use that contact object
    addContact(existingContact.id, existingContact);
    
    Swal.fire({
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 2000,
      timerProgressBar: true,
      icon: 'info',
      title: 'Contact already exists in your contacts'
    });
  } else {
    // Create a new contact object with the phone number
    const newContact = {
      id: 'manual-' + Date.now(), // Use a temporary ID with timestamp
      name: displayName,
      phone: phoneNumber,
      isManual: true, // Flag to identify manually added contacts
      shouldSave: saveManualContact.value // Flag to indicate if we should save this as a new contact
    };
    form.contacts.push(newContact);
    
    // If the user wants to save this as a new contact, create it in the database
    if (saveManualContact.value) {
      axios.post('/contacts', {
        name: displayName,
        phone: phoneNumber
      }).then(response => {
        // Update the temporary contact with the real contact ID
        const index = form.contacts.findIndex(c => 
          typeof c === 'object' && c.isManual && c.phone === phoneNumber
        );
        
        if (index !== -1 && response.data.contact) {
          // Replace the manual contact with the saved one
          form.contacts[index] = response.data.contact;
          
          Swal.fire({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true,
            icon: 'success',
            title: 'New contact created successfully'
          });
        }
      }).catch(error => {
        console.error('Error creating contact:', error);
        Swal.fire({
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 2000,
          timerProgressBar: true,
          icon: 'error',
          title: 'Failed to create contact'
        });
      });
    }
  }

  // Clear the input fields
  manualPhone.value = '';
  manualName.value = '';
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
  // Get existing WhatsApp groups
  const existingGroups = whatsappGroupOptions.value.filter(group =>
    form.groups.includes(group.id)
  );

  return existingGroups;
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
    Swal.fire({
      title: 'Validation Error',
      text: 'Please check the form for errors',
      icon: 'error',
      confirmButtonText: 'OK'
    });
    return;
  }

  form.processing = true;
  errorMessage.value = '';
  successMessage.value = '';

  try {
    // Prepare recipients based on type
    let recipients;
    if (form.recipientType === 'contact') {
      // For manual contacts, we need to handle them differently
      recipients = form.contacts.map(contact => {
        if (typeof contact === 'object') {
          if (contact.isManual) {
            // For manually added contacts, use the phone number directly
            return { id: contact.id, phone: contact.phone };
          }
          return contact.id;
        }
        return contact;
      });
    } else {
      recipients = form.groups;
    }

    const payload = {
      deviceId: form.deviceId,
      recipientType: form.recipientType,
      recipients: recipients,
      message: form.message
    };

    const response = await axios.post('/api/messages/send', payload);

    if (response.data.success) {
      Swal.fire({
        title: 'Success!',
        text: response.data.message || 'Message sent successfully',
        icon: 'success',
        confirmButtonText: 'OK'
      });

      // Reset form values
      form.message = '';
      form.contacts = [];
      form.groups = [];
    } else if (response.data.quotaExhausted) {
      // Handle quota exhaustion
      Swal.fire({
        title: 'Quota Exhausted',
        text: 'Your message quota has been exhausted. Would you like to purchase more?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Go to Subscription',
        cancelButtonText: 'Not Now'
      }).then((result) => {
        if (result.isConfirmed) {
          router.visit('/subscriptions');
        }
      });
    } else {
      Swal.fire({
        title: 'Error',
        text: response.data.message || 'Failed to send message',
        icon: 'error',
        confirmButtonText: 'OK'
      });
    }
  } catch (error) {
    // Check if this is a quota exhaustion error
    if (error.response?.data?.quotaExhausted) {
      Swal.fire({
        title: 'Quota Exhausted',
        text: 'Your message quota has been exhausted. Would you like to purchase more?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Go to Subscription',
        cancelButtonText: 'Not Now'
      }).then((result) => {
        if (result.isConfirmed) {
          router.visit('/subscriptions');
        }
      });
    } else {
      Swal.fire({
        title: 'Error',
        text: error.response?.data?.message || 'An error occurred while sending the message',
        icon: 'error',
        confirmButtonText: 'OK'
      });
    }
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
                <div class="flex gap-2">
                  <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    @click="showContactSelector = !showContactSelector"
                  >
                    {{ showContactSelector ? 'Hide' : 'Show' }} Contact Selector
                  </Button>
                </div>
              </div>

              <!-- Manual Contact Input -->
              <div class="mt-3">
                <div class="flex items-end gap-2 mb-2">
                  <div class="flex-1">
                    <Label for="manualName">Contact Name</Label>
                    <Input
                      id="manualName"
                      v-model="manualName"
                      type="text"
                      placeholder="Enter contact name"
                      class="mt-1"
                    />
                  </div>
                  <div class="flex-1">
                    <Label for="manualPhone">Phone Number</Label>
                    <Input
                      id="manualPhone"
                      v-model="manualPhone"
                      type="text"
                      placeholder="Enter phone number (e.g., 62812345678)"
                      class="mt-1"
                    />
                  </div>
                  <Button
                    type="button"
                    variant="secondary"
                    size="sm"
                    @click="addManualContact"
                    class="mb-0.5"
                  >
                    <PlusIcon class="h-4 w-4 mr-1" />
                    Add
                  </Button>
                </div>
                <div class="flex items-center mt-1 text-sm text-gray-600 dark:text-gray-400">
                  <input
                    type="checkbox"
                    id="saveContact"
                    v-model="saveManualContact"
                    class="mr-2"
                  />
                  <Label for="saveContact" class="cursor-pointer">Save as a new contact</Label>
                </div>
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
              <div>
                <Label>Select WhatsApp Groups</Label>
              </div>

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
