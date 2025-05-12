<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import GoBackButton from "@/Components/Common/GoBackButton.vue";
import { Link, router } from '@inertiajs/vue3';
import { ref, getCurrentInstance } from 'vue';
import { toast } from 'vue3-toastify';
import alerts from '@/utils/alerts'; // Asegúrate de que la ruta sea correcta

const instance = getCurrentInstance();
const $t = instance?.proxy.$t; // Obtener $t para traducciones

const form = ref({
  type: '',
  required_documents: [{ name: '', type: 'pdf', description: '', sample: null }],
  terms_and_conditions: '',
});

const previews = ref({});

const addDocument = () => {
  form.value.required_documents.push({ name: '', type: 'pdf', description: '', sample: null, sample_path: null });
};

const removeDocument = (index) => {
  if (form.value.required_documents.length > 1) {
    form.value.required_documents.splice(index, 1);
  }
};

const handleFileUpload = (event, index) => {
  const file = event.target.files[0];
  if (!file) return;

  const validTypes = {
    pdf: 'application/pdf',
    image: ['image/jpeg', 'image/png'],
    text: 'text/plain',
  };

  const requiredType = form.value.required_documents[index].type;
  const isValid = requiredType === 'pdf' ? file.type === validTypes.pdf :
                  requiredType === 'image' ? validTypes.image.includes(file.type) :
                  requiredType === 'text' ? file.type === validTypes.text : false;

  if (!isValid) {
    toast.error($t('Only ${requiredType} files are allowed for ${name}', { requiredType, name: form.value.required_documents[index].name || 'this document' }));
    return;
  }

  form.value.required_documents[index].sample = file;
  previews.value[form.value.required_documents[index].name] = URL.createObjectURL(file);
};

const submit = () => {
  alerts.confirmCreate($t).then((result) => {
    if (result.isConfirmed) {
      const formData = new FormData();
      formData.append('type', form.value.type || '');
      formData.append('terms_and_conditions', form.value.terms_and_conditions || '');

      form.value.required_documents.forEach((doc, index) => {
        formData.append(`required_documents[${index}][name]`, doc.name || '');
        formData.append(`required_documents[${index}][type]`, doc.type || '');
        formData.append(`required_documents[${index}][description]`, doc.description || '');
        if (doc.sample) {
          formData.append(`required_documents[${index}][sample]`, doc.sample);
        }
      });

      router.post(route('identity-types.store'), formData, {
        onSuccess: () => {
          toast.success($t('Identity type created successfully'));
        },
        onError: (errors) => {
          console.log('Server errors:', errors);
          toast.error($t('Error creating identity type'));
        },
      });
    }
  });
};

const acceptTypes = {
  pdf: '.pdf',
  image: '.jpg,.jpeg,.png',
  text: '.txt',
};
</script>

<template>
  <AppLayout :title="$t('Create Identity Type')">
    <template #header>
      <div class="flex items-center space-x-2">
        <GoBackButton />
        <h1 class="font-semibold text-xl text-gray-800 leading-tight">{{ $t('Create Identity Type') }}</h1>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <form @submit.prevent="submit" enctype="multipart/form-data">
              <div class="mb-4">
                <label class="block text-gray-700">{{ $t('Type') }}</label>
                <input v-model="form.type" type="text" class="mt-1 block w-full border-gray-300 rounded-md" required />
              </div>

              <div class="mb-4">
                <label class="block text-gray-700">{{ $t('Terms and Conditions') }}</label>
                <textarea v-model="form.terms_and_conditions" class="mt-1 block w-full border-gray-300 rounded-md" required></textarea>
              </div>

              <div class="mb-4">
                <label class="block text-gray-700">{{ $t('Required Documents') }}</label>
                <div v-for="(doc, index) in form.required_documents" :key="index" class="flex space-x-2 mb-2 items-center">
                  <input v-model="doc.name" type="text" :placeholder="$t('Name')" class="border-gray-300 rounded-md" required />
                  <select v-model="doc.type" class="border-gray-300 rounded-md">
                    <option value="pdf">{{ $t('PDF') }}</option>
                    <option value="image">{{ $t('Image') }}</option>
                    <option value="text">{{ $t('Text') }}</option>
                  </select>
                  <input v-model="doc.description" type="text" :placeholder="$t('Description')" class="border-gray-300 rounded-md flex-1" />
                  <input 
                    type="file" 
                    @change="handleFileUpload($event, index)" 
                    :accept="acceptTypes[doc.type]" 
                    class="border-gray-300 rounded-md" 
                  />
                  <div v-if="previews[doc.name]" class="ml-2">
                    <embed v-if="doc.type === 'pdf'" :src="previews[doc.name]" type="application/pdf" class="w-20 h-20 rounded" />
                    <img v-else-if="doc.type === 'image'" :src="previews[doc.name]" class="w-20 h-20 object-cover rounded" />
                    <pre v-else-if="doc.type === 'text'" class="w-20 h-20 overflow-auto border rounded p-1 text-xs">{{ previews[doc.name] }}</pre>
                  </div>
                  <button 
                    type="button" 
                    @click="removeDocument(index)" 
                    class="w-6 h-6 flex items-center justify-center text-red-600 border-2 border-red-600 rounded-full hover:bg-red-100 transition" 
                    :disabled="form.required_documents.length === 1"
                  >
                    -
                  </button>
                </div>
                <button type="button" @click="addDocument" class="text-blue-600 hover:text-blue-900">{{ $t('+ Add Document') }}</button>
              </div>

              <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                {{ $t('Create') }}
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.border-blue-700 {
  border-color: #164C73;
}
.text-blue-700 {
  color: #164C73;
}
</style>