<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import HeaderSection from '@/Components/Common/HeaderSection.vue';
import { Link, usePage, useForm } from '@inertiajs/vue3';
import { getCurrentInstance, ref, onMounted } from 'vue';
import { toast } from 'vue3-toastify';
import alerts from '@/utils/alerts';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
  type: String,
  roleName: String,
});

const page = usePage();
const user = page.props.auth.user;

const instance = getCurrentInstance();
const $t = instance?.proxy.$t;

const form = useForm({
  type: props.type,
  email: '',
  name: '',
  address: '',
  phone: '',
  documents: {},
  terms_accepted: false,
});

const requiredDocuments = ref([]);
const termsUrl = ref('');
const previews = ref({});
const isLoading = ref(true);
const termsViewed = ref(false);
const showTermsModal = ref(false);
const termsContent = ref('');
const isAcceptingTerms = ref(false);

// Configurar axios con el token CSRF
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;

onMounted(async () => {
  try {
    const response = await axios.get(`/api/required-documents/${props.type}`);
    requiredDocuments.value = response.data.required_documents || [];
    termsUrl.value = response.data.terms_url || '';
  } catch (error) {
    toast.error($t('Error loading required documents'));
    console.error('Error loading required documents:', error);
  } finally {
    isLoading.value = false;
  }
});

const loadTerms = async () => {
  if (!termsUrl.value) {
    toast.error($t('Terms not available'));
    return;
  }
  try {
    const response = await axios.get(termsUrl.value);
    termsContent.value = response.data;
    showTermsModal.value = true;
  } catch (error) {
    toast.error($t('Error loading terms and conditions'));
    console.error('Error loading terms:', error);
  }
};

const acceptTerms = async () => {
  isAcceptingTerms.value = true;
  try {
    console.log('Enviando solicitud a /accept-terms para type:', props.type);
    const response = await axios.post('/accept-terms', { type: props.type });
    console.log('Respuesta de /accept-terms:', response.data);
    form.terms_accepted = true;
    termsViewed.value = true;
    showTermsModal.value = false;
  } catch (error) {
    console.error('Error en acceptTerms:', error.response?.data || error.message);
    toast.error($t('Error registering terms acceptance'));
  } finally {
    isAcceptingTerms.value = false;
  }
};

const handleFileUpload = (event, docName) => {
  const file = event.target.files[0];
  if (!file) return;

  const validTypes = {
    pdf: 'application/pdf',
    image: ['image/jpeg', 'image/png'],
    text: 'text/plain',
  };

  const requiredType = requiredDocuments.value.find(doc => doc.name === docName)?.type;
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

  form.documents[docName] = file;

  if (requiredType === 'text') {
    const reader = new FileReader();
    reader.onload = (e) => {
      previews.value[docName] = e.target.result;
    };
    reader.readAsText(file);
  } else {
    previews.value[docName] = URL.createObjectURL(file);
  }
};

const submit = async () => {
  const nameRegex = /^[a-zA-Z0-9\s]+$/;
  if (!form.name || !nameRegex.test(form.name)) {
    toast.error($t('Name must contain only letters, numbers, and spaces'));
    return;
  }

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(form.email)) {
    toast.error($t('Invalid email format'));
    return;
  }

  const phoneRegex = /^[+()0-9 -]*$/;
  if (form.phone && !phoneRegex.test(form.phone)) {
    toast.error($t('Invalid phone format'));
    return;
  }

  if (!form.terms_accepted || !termsViewed.value) {
    toast.error($t('You must accept the terms and conditions'));
    return;
  }

  if (isLoading.value || !requiredDocuments.value || Object.keys(form.documents).length !== requiredDocuments.value.length) {
    toast.error($t('All required documents must be uploaded'));
    return;
  }

  const result = await alerts.confirmSubmit($t);
  if (result.isConfirmed) {
    form.post(route('identity.request.store'), {
      forceFormData: true,
      onSuccess: () => {
        form.reset();
        previews.value = {};
        termsViewed.value = false;
        alerts.success($t, 'Request submitted successfully');
        router.visit(route('my-requests.index'));
      },
      onError: (errors) => {
        alerts.error($t, 'Error submitting request');
        console.error('Error al enviar solicitud:', errors);
      },
    });
  }
};

const cancel = async () => {
  const result = await alerts.confirmCancel($t);
  if (result.isConfirmed) {
    window.history.back();
  }
};
</script>

<template>
  <AppLayout :title="$t('Rol Request')">
    <div class="p-6 bg-neutral-3 dark:bg-neutral-1 min-h-screen">
      <HeaderSection
        :title="$t('Request New Identity')"
        :show-back-button="true"
      />

      <div v-if="isLoading" class="mb-4 p-4 bg-secondary-2 dark:bg-secondary-2 text-neutral-0 dark:text-neutral-0 rounded-lg">
        {{ $t('Loading required documents...') }}
      </div>
      <div v-if="page.props.flash.message" class="mb-4 p-4 bg-secondary-1 dark:bg-secondary-1 text-neutral-0 dark:text-neutral-0 rounded-lg">
        {{ page.props.flash.message }}
      </div>
      <div v-if="form.errors && Object.keys(form.errors).length" class="mb-4 p-4 bg-secondary-3 dark:bg-secondary-3 text-neutral-0 dark:text-neutral-0 rounded-lg">
        <p v-for="(error, key) in form.errors" :key="key">{{ error }}</p>
      </div>

      <div class="bg-neutral-0 dark:bg-neutral-2 border border-neutral-4 dark:border-neutral-2 rounded-lg shadow-sm p-6">
        <form @submit.prevent="submit" class="space-y-6" enctype="multipart/form-data">
          <p class="text-neutral-1 dark:text-neutral-0">
            {{ $t('Welcome') }}, {{ user.name }}. {{ $t('Requesting the identity') }}: <strong class="text-main-1">{{ roleName || type }}</strong>
          </p>

          <div>
            <label class="block text-sm font-medium text-neutral-1 dark:text-neutral-0 mb-2">{{ $t('Email') }}</label>
            <input
              v-model="form.email"
              type="email"
              class="appearance-none border border-neutral-4 dark:border-neutral-2 rounded px-3 py-2 w-full text-neutral-2 dark:text-neutral-0 bg-neutral-0 dark:bg-neutral-2 focus:outline-none focus:ring-2 focus:ring-main-1"
              required
              :aria-label="$t('Email')"
            />
            <span v-if="form.errors.email" class="text-secondary-3 dark:text-secondary-3 text-sm">{{ form.errors.email }}</span>
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
            <span v-if="form.errors.name" class="text-secondary-3 dark:text-secondary-3 text-sm">{{ form.errors.name }}</span>
          </div>

          <div>
            <label class="block text-sm font-medium text-neutral-1 dark:text-neutral-0 mb-2">{{ $t('Address') }}</label>
            <input
              v-model="form.address"
              type="text"
              class="appearance-none border border-neutral-4 dark:border-neutral-2 rounded px-3 py-2 w-full text-neutral-2 dark:text-neutral-0 bg-neutral-0 dark:bg-neutral-2 focus:outline-none focus:ring-2 focus:ring-main-1"
              :aria-label="$t('Address')"
            />
            <span v-if="form.errors.address" class="text-secondary-3 dark:text-secondary-3 text-sm">{{ form.errors.address }}</span>
          </div>

          <div>
            <label class="block text-sm font-medium text-neutral-1 dark:text-neutral-0 mb-2">{{ $t('Phone') }}</label>
            <input
              v-model="form.phone"
              type="tel"
              class="appearance-none border border-neutral-4 dark:border-neutral-2 rounded px-3 py-2 w-full text-neutral-2 dark:text-neutral-0 bg-neutral-0 dark:bg-neutral-2 focus:outline-none focus:ring-2 focus:ring-main-1"
              :aria-label="$t('Phone')"
            />
            <span v-if="form.errors.phone" class="text-secondary-3 dark:text-secondary-3 text-sm">{{ form.errors.phone }}</span>
          </div>

          <div v-if="!isLoading" v-for="doc in requiredDocuments" :key="doc.name" class="space-y-2">
            <label class="block text-sm font-medium text-neutral-1 dark:text-neutral-0">{{ $t(doc.name) }} ({{ doc.type }})</label>
            <input
              type="file"
              :accept="doc.type === 'pdf' ? 'application/pdf' : doc.type === 'image' ? 'image/jpeg,image/png' : 'text/plain'"
              @change="handleFileUpload($event, doc.name)"
              class="appearance-none border border-neutral-4 dark:border-neutral-2 rounded px-3 py-2 w-full text-neutral-2 dark:text-neutral-0 bg-neutral-0 dark:bg-neutral-2 focus:outline-none focus:ring-2 focus:ring-main-1"
              :aria-label="$t('Upload') + ' ' + doc.name"
            />
            <p class="text-sm text-neutral-2 dark:text-neutral-0">{{ doc.description }}</p>
            <span v-if="form.errors[`documents.${doc.name}`]" class="text-secondary-3 dark:text-secondary-3 text-sm">{{ form.errors[`documents.${doc.name}`] }}</span>
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
          </div>

          <div v-if="!isLoading">
            <label class="block text-sm font-medium text-neutral-1 dark:text-neutral-0 mb-2">{{ $t('Terms and Conditions') }}</label>
            <a
              v-if="termsUrl"
              @click="loadTerms"
              class="text-main-1 dark:text-main-1 hover:underline cursor-pointer"
              :aria-label="$t('Read Terms and Conditions')"
            >
              {{ $t('Read Terms and Conditions') }}
            </a>
            <span v-else class="text-secondary-3 dark:text-secondary-3 text-sm">{{ $t('Terms not available') }}</span>
            <label class="flex items-center mt-2">
              <input
                v-model="form.terms_accepted"
                type="checkbox"
                class="mr-2 border-neutral-4 dark:border-neutral-2 text-main-1 focus:ring-main-1"
                :disabled="!termsViewed"
                :aria-label="$t('I accept the terms and conditions')"
              />
              <span class="text-neutral-1 dark:text-neutral-0">{{ $t('I accept the terms and conditions') }}</span>
              <span v-if="isAcceptingTerms" class="text-neutral-2 dark:text-neutral-0 text-sm ml-2">{{ $t('Registering acceptance...') }}</span>
            </label>
            <span v-if="form.errors.terms_accepted" class="text-secondary-3 dark:text-secondary-3 text-sm">{{ form.errors.terms_accepted }}</span>
          </div>

          <div class="flex gap-4 justify-end">
            <button
              type="submit"
              :disabled="form.processing || isLoading"
              class="px-4 py-2 bg-main-1 dark:bg-main-1 text-neutral-0 dark:text-neutral-0 rounded-lg hover:bg-main-0 dark:hover:bg-main-0 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
              :aria-label="$t('Request')"
            >
              {{ $t('Request') }}
            </button>
            <button
              type="button"
              @click="cancel"
              class="px-4 py-2 bg-neutral-4 dark:bg-neutral-2 text-neutral-2 dark:text-neutral-0 rounded-lg hover:bg-neutral-3 dark:hover:bg-neutral-1"
              :aria-label="$t('Cancel')"
            >
              {{ $t('Cancel') }}
            </button>
          </div>
        </form>
      </div>

      <!-- Modal de términos con animación -->
      <Transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div v-if="showTermsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <Transition
            enter-active-class="transition ease-out duration-300 transform"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-200 transform"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
          >
            <div class="bg-neutral-0 dark:bg-neutral-2 p-6 rounded-lg max-w-3xl w-full max-h-[80vh] flex flex-col">
              <h2 class="text-lg font-semibold text-neutral-1 dark:text-neutral-0 mb-4">{{ $t('Terms and Conditions') }}</h2>
              <div class="prose dark:prose-invert max-h-[60vh] overflow-auto text-neutral-2 dark:text-neutral-0" v-html="termsContent"></div>
              <div class="flex justify-end gap-4 mt-4">
                <button
                  @click="acceptTerms"
                  class="px-4 py-2 bg-main-1 dark:bg-main-1 text-neutral-0 dark:text-neutral-0 rounded-lg hover:bg-main-0 dark:hover:bg-main-0 transition-colors duration-200"
                >
                  {{ $t('Accept') }}
                </button>
                <button
                  @click="showTermsModal = false"
                  class="px-4 py-2 bg-neutral-4 dark:bg-neutral-2 text-neutral-2 dark:text-neutral-0 rounded-lg hover:bg-neutral-3 dark:hover:bg-neutral-1"
                >
                  {{ $t('Close') }}
                </button>
              </div>
            </div>
          </Transition>
        </div>
      </Transition>
    </div>
  </AppLayout>
</template>

<style scoped>
form {
  border-radius: 0.5rem;
  overflow: hidden;
}
</style>