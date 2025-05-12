<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import HeaderSection from '@/Components/Common/HeaderSection.vue';
import ChangeRequestHistory from '@/Components/ChangeRequestHistory.vue';
import { ref, watch, getCurrentInstance } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';
import alerts from '@/utils/alerts';
import axios from 'axios';

const props = defineProps({
  identity: Object,
  userRole: String,
  requiredDocuments: Array,
});

const page = usePage();
const message = ref(null);
const errors = ref({});

const instance = getCurrentInstance();
const $t = instance?.proxy.$t;

const form = ref({
  email: props.identity?.email || '',
  name: props.identity?.name || '',
  address: props.identity?.address || '',
  phone: props.identity?.phone || '',
  documents: {},
});

const previews = ref({});

const fetchTextContent = async (path) => {
  try {
    const response = await axios.get(`/storage/${path}`, { responseType: 'text' });
    return response.data;
  } catch (error) {
    console.error('Error fetching text content:', error);
    return $t('Error loading text content');
  }
};

watch(
  () => props.identity,
  async (newIdentity) => {
    console.log('Identity received:', newIdentity);
    console.log('Identity_documents:', newIdentity?.identity_documents);
    console.log('Required documents:', props.requiredDocuments);
    if (newIdentity && newIdentity.identity_documents) {
      form.value.email = newIdentity.email || '';
      form.value.name = newIdentity.name || '';
      form.value.address = newIdentity.address || '';
      form.value.phone = newIdentity.phone || '';
      form.value.documents = {};
      const docs = Array.isArray(newIdentity.identity_documents) ? newIdentity.identity_documents :OUND;
      for (const doc of docs) {
        if (doc.type === 'text') {
          previews.value[doc.name] = await fetchTextContent(doc.path);
        } else {
          previews.value[doc.name] = `/storage/${doc.path}`;
        }
      }
      console.log('Previews initialized:', previews.value);
    }
  },
  { immediate: true }
);

const handleFileUpload = (event, docName) => {
  const file = event.target.files[0];
  if (!file) return;

  const validTypes = {
    pdf: 'application/pdf',
    image: ['image/jpeg', 'image/png'],
    text: 'text/plain',
  };

  const requiredType = props.requiredDocuments.find(doc => doc.name === docName)?.type;
  const isValid = requiredType === 'pdf' 
    ? file.type === validTypes.pdf 
    : requiredType === 'image' 
      ? validTypes.image.includes(file.type) 
      : requiredType === 'text' 
        ? file.type === validTypes.text 
        : false;

  if (!isValid) {
    toast.error($t(`Only ${requiredType} files are allowed for ${docName}`));
    return;
  }

  form.value.documents[docName] = file;

  if (requiredType === 'text') {
    const reader = new FileReader();
    reader.onload = (e) => {
      previews.value[docName] = e.target.result;
    };
    reader.readAsText(file);
  } else {
    previews.value[docName] = URL.createObjectURL(file);
  }

  if (props.identity.identity_documents.some(d => d.name === docName)) {
    toast.info($t(`Replacing existing document: ${docName}`));
  }
};

const deleteDocument = async (docName) => {
  console.log('Delete document called for:', docName);
  try {
    const result = await alerts.confirmDeleteDocument($t);
    console.log('Confirmation result:', result);
    if (result.isConfirmed) {
      const doc = props.identity.identity_documents.find(d => d.name === docName);
      if (doc) {
        console.log('Document to delete:', doc);
        router.delete(route('identities.documents.destroy', { identity: props.identity.id, index: doc.id }), {
          preserveState: true,
          preserveScroll: true,
          onSuccess: () => {
            console.log('Document deleted successfully');
            toast.success($t('Document deleted successfully'));
            router.reload({ only: ['identity'] });
            delete previews.value[docName];
            delete form.value.documents[docName];
          },
          onError: (err) => {
            toast.error($t('Error deleting document'));
            console.error('Error al eliminar:', err);
          },
        });
      } else {
        console.error('Documento no encontrado:', docName);
      }
    }
  } catch (error) {
    console.error('Error en confirmDeleteDocument:', error);
    toast.error($t('Error showing confirmation dialog'));
  }
};

const updateIdentity = async () => {
  const nameRegex = /^[a-zA-Z0-9\s]+$/;
  if (!form.value.name || !nameRegex.test(form.value.name)) {
    toast.error($t('Name must contain only letters, numbers, and spaces'));
    return;
  }

  const phoneRegex = /^[+()0-9 -]*$/;
  if (form.value.phone && !phoneRegex.test(form.value.phone)) {
    toast.error($t('Invalid phone format'));
    return;
  }

  const formData = new FormData();
  formData.append('email', form.value.email);
  formData.append('name', form.value.name);
  formData.append('address', form.value.address || '');
  formData.append('phone', form.value.phone || '');

  for (const [name, file] of Object.entries(form.value.documents)) {
    formData.append(`documents[${name}]`, file);
  }

  const result = await alerts.confirmSubmit($t);
  if (result.isConfirmed) {
    router.post(route('user.identities.update', props.identity.id), formData, {
      preserveState: true,
      preserveScroll: true,
      onSuccess: () => {
        form.value.documents = {};
        const docs = Array.isArray(props.identity.identity_documents) ? props.identity.identity_documents : [];
        previews.value = docs.reduce((acc, doc) => {
          acc[doc.name] = '/storage/' + doc.path;
          return acc;
        }, {});
        toast.success($t('Identity updated successfully'));
        router.visit(route('my-requests.index'));
      },
      onError: (err) => {
        errors.value = err;
        toast.error($t('Error updating identity'));
      },
    });
  }
};
</script>

<template>
  <AppLayout :title="$t('Rol Request')">
    <div class="container mx-auto p-4 bg-neutral-3 dark:bg-neutral-1 min-h-screen">
      <HeaderSection
        :title="$t('Edit Identity') + ' - ' + identity.role_name"
        :show-back-button="true"
      />

      <div v-if="message" class="mb-4 p-4 bg-secondary-1 dark:bg-secondary-1 text-neutral-0 dark:text-neutral-0 rounded-lg">
        {{ message }}
      </div>
      <div v-if="Object.keys(errors).length" class="mb-4 p-4 bg-secondary-3 dark:bg-secondary-3 text-neutral-0 dark:text-neutral-0 rounded-lg">
        <p v-for="(error, key) in errors" :key="key">{{ error }}</p>
      </div>

      <div class="bg-neutral-0 dark:bg-neutral-2 border border-neutral-4 dark:border-neutral-2 rounded-lg shadow-sm p-6">
        <form @submit.prevent="updateIdentity" class="space-y-6">
          <p class="text-neutral-1 dark:text-neutral-0">{{ $t('Editing identity') }}: <strong class="text-main-1">{{ identity.role_name }}</strong></p>

          <div>
            <label class="block text-sm font-medium text-neutral-1 dark:text-neutral-0 mb-2">{{ $t('Email') }}</label>
            <input
              v-model="form.email"
              type="email"
              class="appearance-none border border-neutral-4 dark:border-neutral-2 rounded px-3 py-2 w-full text-neutral-2 dark:text-neutral-0 bg-neutral-0 dark:bg-neutral-2 focus:outline-none focus:ring-2 focus:ring-main-1"
              required
              :aria-label="$t('Email')"
            />
            <span v-if="errors.email" class="text-secondary-3 dark:text-secondary-3 text-sm">{{ errors.email }}</span>
          </div>

          <div>
            <label class="block text-sm font-medium text-neutral-1 dark:text-neutral-0 mb-2">{{ $t('Name') }}</label>
            <input
              v-model="form.name"
              type="text"
              class="appearance-none border border-neutral-4 dark:border-neutral-2 rounded px-3 py-2 w-full text-neutral-2 dark:text-neutral-0 bg-neutral-0 dark:bg-neutral-2 focus:outline-none focus:ring-2 focus:ring-main-1"
              required
              :aria-label="$t('Name')"
            />
            <span v-if="errors.name" class="text-secondary-3 dark:text-secondary-3 text-sm">{{ errors.name }}</span>
          </div>

          <div>
            <label class="block text-sm font-medium text-neutral-1 dark:text-neutral-0 mb-2">{{ $t('Address') }}</label>
            <input
              v-model="form.address"
              type="text"
              class="appearance-none border border-neutral-4 dark:border-neutral-2 rounded px-3 py-2 w-full text-neutral-2 dark:text-neutral-0 bg-neutral-0 dark:bg-neutral-2 focus:outline-none focus:ring-2 focus:ring-main-1"
              :aria-label="$t('Address')"
            />
            <span v-if="errors.address" class="text-secondary-3 dark:text-secondary-3 text-sm">{{ errors.address }}</span>
          </div>

          <div>
            <label class="block text-sm font-medium text-neutral-1 dark:text-neutral-0 mb-2">{{ $t('Phone') }}</label>
            <input
              v-model="form.phone"
              type="tel"
              class="appearance-none border border-neutral-4 dark:border-neutral-2 rounded px-3 py-2 w-full text-neutral-2 dark:text-neutral-0 bg-neutral-0 dark:bg-neutral-2 focus:outline-none focus:ring-2 focus:ring-main-1"
              :aria-label="$t('Phone')"
            />
            <span v-if="errors.phone" class="text-secondary-3 dark:text-secondary-3 text-sm">{{ errors.phone }}</span>
          </div>

          <ChangeRequestHistory :requests="identity.change_requests" />

          <div v-for="doc in requiredDocuments" :key="doc.name" class="space-y-2">
            <label class="block text-sm font-medium text-neutral-1 dark:text-neutral-0">{{ $t(doc.name) }} ({{ doc.type }})</label>
            <input
              type="file"
              :accept="doc.type === 'pdf' ? 'application/pdf' : doc.type === 'image' ? 'image/jpeg,image/png' : 'text/plain'"
              @change="handleFileUpload($event, doc.name)"
              class="appearance-none border border-neutral-4 dark:border-neutral-2 rounded px-3 py-2 w-full text-neutral-2 dark:text-neutral-0 bg-neutral-0 dark:bg-neutral-2 focus:outline-none focus:ring-2 focus:ring-main-1"
              :aria-label="$t('Upload') + ' ' + doc.name"
            />
            <p class="text-sm text-neutral-2 dark:text-neutral-0">{{ doc.description }}</p>
            <span v-if="errors[`documents.${doc.name}`]" class="text-secondary-3 dark:text-secondary-3 text-sm">{{ errors[`documents.${doc.name}`] }}</span>
            <div v-if="previews[doc.name]" class="mt-2 max-w-lg mx-auto">
              <embed
                v-if="doc.type === 'pdf'"
                :src="previews[doc.name]"
                type="application/pdf"
                class="w-full rounded aspect-[4/3] md:aspect-[16/9]"
              />
              <img
                v-else-if="doc.type === 'image'"
                :src="previews[doc.name]"
                class="w-full rounded object-contain aspect-[4/3] md:aspect-[16/9]"
              />
              <pre
                v-else-if="doc.type === 'text'"
                class="w-full h-64 overflow-auto p-2 bg-neutral-3 dark:bg-neutral-2 rounded"
              >{{ previews[doc.name] }}</pre>
            </div>
            <button
              v-if="identity.identity_documents && identity.identity_documents.some(d => d.name === doc.name)"
              type="button"
              @click="deleteDocument(doc.name)"
              class="mt-2 px-4 py-2 bg-secondary-3 dark:bg-secondary-3 text-neutral-0 dark:text-neutral-0 rounded-lg hover:bg-secondary-2 dark:hover:bg-secondary-2"
              :aria-label="$t('Delete') + ' ' + doc.name"
            >
              {{ $t('Delete') }}
            </button>
          </div>

          <div class="flex gap-4 justify-end">
            <button
              type="submit"
              class="px-4 py-2 bg-main-1 dark:bg-main-1 text-neutral-0 dark:text-neutral-0 rounded-lg hover:bg-main-0 dark:hover:bg-main-0 transition-colors duration-200"
              :aria-label="$t('Save')"
            >
              {{ $t('Save') }}
            </button>
            <Link
              :href="route('my-requests.index')"
              class="px-4 py-2 bg-neutral-4 dark:bg-neutral-2 text-neutral-2 dark:text-neutral-0 rounded-lg hover:bg-neutral-3 dark:hover:bg-neutral-1"
              :aria-label="$t('Cancel')"
            >
              {{ $t('Cancel') }}
            </Link>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
form {
  border-radius: 0.5rem;
  overflow: hidden;
}
</style>