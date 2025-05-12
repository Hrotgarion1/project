<template>
  <div class="space-y-4">
    <!-- Modo Single -->
    <div v-if="mode === 'single'" class="border border-dashed border-gray-300 p-4 rounded-md">
      <input
        type="file"
        ref="fileInput"
        class="hidden"
        :accept="allowedMimes"
        @change="handleSingleFile"
      />
      <button
        @click="fileInput.click()"
        class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700"
      >
        {{ $t('media.select_file') }}
      </button>
      <!-- Previsualización -->
      <div v-if="preview" class="mt-4">
        <img
          v-if="preview.type.startsWith('image/')"
          :src="preview.url"
          class="w-32 h-32 object-cover rounded"
        />
        <div v-else class="w-32 h-32 bg-gray-200 flex items-center justify-center text-xs">
          {{ preview.name }}
        </div>
        <div class="mt-2">
          <button
            @click="uploadSingleFile"
            class="bg-green-600 text-white px-4 py-2 rounded shadow hover:bg-green-700"
          >
            {{ $t('media.upload') }}
          </button>
          <button
            @click="preview = null; selectedFile = null"
            class="bg-gray-600 text-white px-4 py-2 rounded shadow hover:bg-gray-700 ml-2"
          >
            {{ $t('media.cancel') }}
          </button>
        </div>
      </div>
      <!-- Imágenes subidas -->
      <div v-if="props.initialMedia.length" class="mt-4">
        <div v-for="media in props.initialMedia" :key="media.id" class="relative w-24 h-24">
          <template v-if="media.isImage && media.url">
            <img
              :src="media.url"
              class="object-cover w-full h-full rounded"
              @error="handleImageError(media)"
              @load="console.log('Image loaded:', media.url)"
            />
          </template>
          <template v-else>
            <div class="w-full h-full bg-gray-200 flex items-center justify-center text-xs">
              {{ media.file_name || 'No name' }}
            </div>
          </template>
          <button
            @click="removeMedia(media.id)"
            class="absolute top-0 right-0 bg-red-600 text-white px-1 text-xs rounded-bl"
          >
            X
          </button>
        </div>
      </div>
    </div>

    <!-- Modo Multiple -->
    <div
      v-if="mode === 'multiple'"
      class="border border-dashed border-gray-300 p-4 rounded-md"
      @dragover.prevent="dragActive = true"
      @dragleave.prevent="dragActive = false"
      @drop.prevent="handleDrop"
      :class="dragActive ? 'bg-blue-50 border-blue-400' : ''"
    >
      <input
        type="file"
        ref="fileInput"
        class="hidden"
        multiple
        :accept="allowedMimes"
        @change="handleFiles"
      />
      <button
        @click="fileInput.click()"
        class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700"
      >
        {{ $t('media.select_files') }}
      </button>
      <!-- Previsualización de archivos en proceso -->
      <div v-if="mediaStore.previews.length" class="flex flex-wrap gap-2 mt-4">
        <div v-for="(preview, i) in mediaStore.previews" :key="i" class="w-20 h-20 rounded overflow-hidden">
          <img
            v-if="preview.type.startsWith('image/')"
            :src="preview.url"
            class="w-full h-full object-cover opacity-50"
          />
          <div v-else class="w-full h-full bg-gray-200 flex items-center justify-center text-xs">
            {{ preview.name }}
          </div>
        </div>
      </div>
      <!-- Galería de medios -->
      <draggable
        v-model="mediaStore.media"
        item-key="id"
        class="flex flex-wrap gap-2 mt-4"
        handle=".handle"
        @end="onOrderChange"
      >
        <template #item="{ element, index }">
          <div class="relative w-24 h-24 cursor-pointer handle">
            <img
              v-if="element.isImage && element.url"
              :src="element.url"
              class="object-cover w-full h-full rounded"
              @click="openLightbox(index)"
              @error="handleImageError(element)"
              @load="console.log('Image loaded:', element.url)"
            />
            <iframe
              v-else-if="element.isPdf"
              :src="element.url"
              class="w-full h-full"
            />
            <iframe
              v-else-if="element.isYoutube"
              :src="`https://www.youtube.com/embed/${element.youtubeId}`"
              class="w-full h-full"
              frameborder="0"
              allowfullscreen
            />
            <div v-else class="w-full h-full bg-gray-100 flex items-center justify-center text-xs">
              {{ element.file_name || 'No name' }}
            </div>
            <button
              @click.stop="removeMedia(element.id)"
              class="absolute top-0 right-0 bg-red-600 text-white px-1 text-xs rounded-bl"
            >
              X
            </button>
          </div>
        </template>
      </draggable>
      <!-- Lightbox para imágenes -->
      <VueEasyLightbox
        :visible="showLightbox"
        :imgs="mediaStore.media.filter(m => m.isImage).map(m => m.url)"
        :index="lightboxIndex"
        @hide="showLightbox = false"
      />
    </div>

    <!-- Modo YouTube -->
    <div v-if="mode === 'youtube'" class="border border-dashed border-gray-300 p-4 rounded-md">
      <input
        type="text"
        v-model="youtubeUrl"
        placeholder="Ingresa la URL de YouTube"
        class="border rounded px-2 py-1 w-full"
        @input="validateYoutubeUrl"
      />
      <p v-if="youtubeError" class="text-red-500 text-sm mt-1">{{ youtubeError }}</p>
      <button
        @click="addYoutubeMedia"
        :disabled="!isValidYoutubeUrl"
        class="mt-2 bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700"
      >
        {{ $t('media.add_youtube') }}
      </button>
      <!-- Previsualización de YouTube -->
      <div v-if="youtubePreview" class="mt-4">
        <iframe
          :src="youtubePreview"
          class="w-full h-48 rounded"
          frameborder="0"
          allowfullscreen
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import draggable from 'vuedraggable';
import VueEasyLightbox from 'vue-easy-lightbox';
import { toast } from 'vue3-toastify';
import { useMediaStore } from '@/stores/media';
import { getCurrentInstance } from 'vue';

const instance = getCurrentInstance();
const $t = instance?.proxy.$t;

const mediaStore = useMediaStore();

const props = defineProps({
  identityId: [String, Number],
  mediableType: {
    type: String,
    default: 'App\\Models\\TypeA',
  },
  initialMedia: Array,
  uploadUrl: String,
  deleteUrlBase: String,
  reorderUrl: String,
  mode: {
    type: String,
    default: 'multiple',
    validator: value => ['single', 'multiple', 'youtube'].includes(value),
  },
  role: {
    type: String,
    default: 'gallery',
    validator: value => ['logo', 'header', 'gallery'].includes(value),
  },
  allowedMimes: {
    type: String,
    default: 'image/jpeg,image/png,application/pdf',
  },
  minDimensions: {
    type: Object,
    default: () => ({}),
  },
  maxDimensions: {
    type: Object,
    default: () => ({}),
  },
});

const emit = defineEmits(['media-updated']);

const dragActive = ref(false);
const fileInput = ref(null);
const preview = ref(null);
const selectedFile = ref(null);
const showLightbox = ref(false);
const lightboxIndex = ref(0);
const youtubeUrl = ref('');
const youtubeError = ref('');
const isValidYoutubeUrl = ref(false);
const youtubePreview = ref('');

// Inicializar medios desde initialMedia
watch(() => props.initialMedia, (newMedia) => {
  console.log('initialMedia updated:', newMedia);
  mediaStore.setMedia(newMedia || []);
}, { immediate: true });

// Manejar errores de carga de imagen
const handleImageError = (media) => {
  console.error('Error loading image:', {
    url: media.url,
    isImage: media.isImage,
    file_type: media.file_type,
    file_name: media.file_name,
  });
  toast.error($t('media.image_load_failed'));
};

// Validar dimensiones de la imagen
const validateImageDimensions = (file) => {
  return new Promise((resolve) => {
    if (!file.type.startsWith('image/')) return resolve(true);
    const img = new Image();
    img.src = URL.createObjectURL(file);
    img.onload = () => {
      const { width, height } = img;
      const { minWidth, minHeight } = props.minDimensions;
      const { maxWidth, maxHeight } = props.maxDimensions;
      if (minWidth && width < minWidth) return resolve(`Ancho mínimo: ${minWidth}px`);
      if (minHeight && height < minHeight) return resolve(`Alto mínimo: ${minHeight}px`);
      if (maxWidth && width > maxWidth) return resolve(`Ancho máximo: ${maxWidth}px`);
      if (maxHeight && height > maxHeight) return resolve(`Alto máximo: ${maxHeight}px`);
      resolve(true);
    };
    img.onerror = () => resolve(true);
  });
};

// Manejar archivo único
const handleSingleFile = async (event) => {
  const file = event.target.files[0];
  if (!file) {
    toast.error($t('media.no_file_selected'));
    return;
  }

  console.log('Selected file:', file);

  const dimensionError = await validateImageDimensions(file);
  if (dimensionError !== true) {
    toast.error(dimensionError);
    return;
  }

  if (!(file instanceof Blob)) {
    toast.error($t('media.invalid_file_type'));
    return;
  }

  preview.value = {
    name: file.name,
    type: file.type,
    url: URL.createObjectURL(file),
  };
  selectedFile.value = file;
};

// Subir archivo único
const uploadSingleFile = async () => {
  if (!selectedFile.value) {
    toast.error($t('media.no_file_selected'));
    return;
  }

  if (!props.uploadUrl) {
    toast.error($t('media.upload_url_missing'));
    return;
  }

  const formData = new FormData();
  formData.append('file', selectedFile.value);
  formData.append('folder', props.role);
  formData.append('role', props.role);

  console.log('Sending formData to:', props.uploadUrl);

  const result = await mediaStore.addMedia(formData, props.uploadUrl);
  console.log('Add media result:', result);

  if (!result.success) {
    const errorMessage = result.details?.file?.[0] || $t(`media.${result.error}`);
    toast.error(errorMessage);
    preview.value = null;
    selectedFile.value = null;
  } else {
    toast.success($t('media.upload_success'));
    preview.value = null;
    selectedFile.value = null;
    emit('media-updated');
  }
};

// Manejar múltiples archivos
const handleFiles = async (event) => {
  const files = [...event.target.files];
  for (const file of files) {
    const dimensionError = await validateImageDimensions(file);
    if (dimensionError !== true) {
      toast.error(dimensionError);
      continue;
    }

    const previewItem = {
      name: file.name,
      type: file.type,
      url: URL.createObjectURL(file),
    };
    mediaStore.previews.push(previewItem);

    if (!props.uploadUrl) {
      toast.error($t('media.upload_url_missing'));
      continue;
    }

    const formData = new FormData();
    formData.append('file', file);
    formData.append('folder', 'gallery');
    formData.append('role', props.role);

    const result = await mediaStore.addMedia(formData, props.uploadUrl);
    mediaStore.previews = mediaStore.previews.filter(p => p !== previewItem);
    if (!result.success) {
      toast.error($t(`media.${result.error}`));
    } else {
      toast.success($t('media.upload_success'));
      emit('media-updated');
    }
  }
};

// Manejar drop de archivos
const handleDrop = async (event) => {
  dragActive.value = false;
  const files = [...event.dataTransfer.files];
  for (const file of files) {
    const dimensionError = await validateImageDimensions(file);
    if (dimensionError !== true) {
      toast.error(dimensionError);
      continue;
    }

    const previewItem = {
      name: file.name,
      type: file.type,
      url: URL.createObjectURL(file),
    };
    mediaStore.previews.push(previewItem);

    if (!props.uploadUrl) {
      toast.error($t('media.upload_url_missing'));
      continue;
    }

    const formData = new FormData();
    formData.append('file', file);
    formData.append('folder', 'gallery');
    formData.append('role', props.role);

    const result = await mediaStore.addMedia(formData, props.uploadUrl);
    mediaStore.previews = mediaStore.previews.filter(p => p !== previewItem);
    if (!result.success) {
      toast.error($t(`media.${result.error}`));
    } else {
      toast.success($t('media.upload_success'));
      emit('media-updated');
    }
  }
};

// Validar URL de YouTube
const validateYoutubeUrl = () => {
  const url = youtubeUrl.value;
  const regex = /(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/;
  const match = url.match(regex);
  if (match) {
    youtubeError.value = '';
    isValidYoutubeUrl.value = true;
    youtubePreview.value = `https://www.youtube.com/embed/${match[1]}`;
  } else {
    youtubeError.value = $t('media.invalid_youtube_url');
    isValidYoutubeUrl.value = false;
    youtubePreview.value = '';
  }
};

// Agregar medio de YouTube
const addYoutubeMedia = async () => {
  if (!props.uploadUrl) {
    toast.error($t('media.upload_url_missing'));
    return;
  }

  const match = youtubeUrl.value.match(/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/);
  if (!match) return;

  const formData = new FormData();
  formData.append('youtube_id', match[1]);
  formData.append('folder', 'youtube');
  formData.append('role', 'youtube');

  const result = await mediaStore.addMedia(formData, props.uploadUrl);
  if (!result.success) {
    toast.error($t(`media.${result.error}`));
  } else {
    toast.success($t('media.youtube_success'));
    youtubeUrl.value = '';
    youtubePreview.value = '';
    isValidYoutubeUrl.value = false;
    emit('media-updated');
  }
};

// Eliminar medio
const removeMedia = async (id) => {
  if (!props.deleteUrlBase) {
    toast.error($t('media.delete_url_missing'));
    return;
  }
  const url = `${props.deleteUrlBase}${id}`;
  console.log('Deleting media at:', url);
  const result = await mediaStore.removeMedia(id, url);
  if (!result.success) {
    toast.error($t(`media.${result.error}`));
  } else {
    toast.success($t('media.delete_success'));
    emit('media-updated');
  }
};

// Reordenar medios
const onOrderChange = async () => {
  const result = await mediaStore.reorderMedia(props.reorderUrl);
  if (!result.success) {
    toast.error($t(`media.${result.error}`));
  } else {
    toast.success($t('media.reorder_success'));
  }
};

// Abrir lightbox
const openLightbox = (index) => {
  lightboxIndex.value = index;
  showLightbox.value = true;
};
</script>