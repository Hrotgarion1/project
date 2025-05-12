<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import GoBackButton from "@/Components/Common/GoBackButton.vue";
import { ref, getCurrentInstance } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';
import alerts from '@/utils/alerts'; // Importamos alerts.js

const props = defineProps({
  identity: Object,
});

const instance = getCurrentInstance(); // Obtenemos la instancia del componente
const $t = instance?.proxy.$t; // Accedemos a $t desde la instancia

const form = useForm({
  type: props.identity.type,
  email: props.identity.email,
  name: props.identity.name,
  address: props.identity.address || '',
  documents: [],
});

const handleFileUpload = (event) => {
  const files = Array.from(event.target.files);
  const invalidFiles = files.filter(file => !file.type.includes('pdf'));
  if (invalidFiles.length > 0) {
    toast.error($t('Only PDF files')); // Validación de tipo de archivo
    form.documents = files.filter(file => file.type.includes('pdf')); // Solo PDFs válidos
    return;
  }
  form.documents = files;
};

const submit = async () => {
  // Validación del campo name (requerido, letras, números y espacios)
  const nameRegex = /^[a-zA-Z0-9\s]+$/;
  if (!form.name || !nameRegex.test(form.name)) {
    toast.error($t('Name must contain only letters, numbers, and spaces'));
    return;
  }

  const result = await alerts.confirmUpdate($t);

  if (result.isConfirmed) {
    form.post(route('identities.update', { identity: props.identity.id }), {
      onSuccess: () => {
        form.reset('documents'); // Reseteamos solo los documentos
        alerts.success($t, 'Identity updated successfully'); // Usamos SweetAlert para éxito
        router.visit(route('identity-panel.index')); // Redirigimos al panel
      },
      onError: (errors) => {
        alerts.error($t, 'Error updating identity'); // Usamos SweetAlert para error
        console.error('Error al actualizar:', errors);
      },
    });
  }
};
</script>

<template>
  <AppLayout :title="$t('Edit Identity')">
    <template #header>
      <div class="flex items-center space-x-2">
        <GoBackButton />
      <h1 class="font-semibold text-xl text-gray-800 leading-tight">{{ $t('Edit Identity') }}</h1>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="p-6 bg-white border-b border-gray-200">
          <form @submit.prevent="submit" enctype="multipart/form-data" class="space-y-4">
            <div>
              <label class="block text-gray-700">{{ $t('Requested Role') }}</label>
              <input v-model="form.type" type="text" class="w-full border rounded px-3 py-2" required />
              <span v-if="form.errors.type" class="text-red-500 text-sm">{{ form.errors.type }}</span>
            </div>

            <div>
              <label class="block text-gray-700">{{ $t('Email') }}</label>
              <input v-model="form.email" type="email" class="w-full border rounded px-3 py-2" required />
              <span v-if="form.errors.email" class="text-red-500 text-sm">{{ form.errors.email }}</span>
            </div>

            <div>
              <label class="block text-gray-700">{{ $t('Name') }}</label>
              <input v-model="form.name" type="text" class="w-full border rounded px-3 py-2" required />
              <span v-if="form.errors.name" class="text-red-500 text-sm">{{ form.errors.name }}</span>
            </div>

            <div>
              <label class="block text-gray-700">{{ $t('Address') }}</label>
              <input v-model="form.address" type="text" class="w-full border rounded px-3 py-2" />
              <span v-if="form.errors.address" class="text-red-500 text-sm">{{ form.errors.address }}</span>
            </div>

            <div>
              <label class="block text-gray-700">{{ $t('Documents') }}</label>
              <input type="file" multiple @change="handleFileUpload" class="w-full" accept="application/pdf" />
              <p class="text-sm text-gray-500">{{ $t('Only PDF files') }}</p>
              <span v-if="form.errors.documents" class="text-red-500 text-sm">{{ form.errors.documents }}</span>
            </div>

            <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
              {{ $t('Save') }}
            </button>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>