<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import MediaUploader from '@/Components/Common/MediaUploader.vue';
import { getCurrentInstance } from 'vue';
import axios from 'axios';
import { ref } from 'vue';
import { toast } from 'vue3-toastify';

const instance = getCurrentInstance();
const $t = instance?.proxy.$t;

const props = defineProps({
  identity: Object,
  identityId: String,
  mediableType: {
    type: String,
    default: 'App\\Models\\TypeB',
  },
  initialMedia: {
    type: Array,
    default: () => [],
  },
  uploadUrl: String,
  deleteUrlBase: String,
  reorderUrl: String,
  folders: {
    type: Array,
    default: () => ['General', 'Planos', 'Videos'],
  },
});

const localInitialMedia = ref(props.initialMedia);

console.log('Dashboard props:', {
  identity: props.identity,
  identityId: props.identityId,
  mediableType: props.mediableType,
  initialMedia: localInitialMedia.value,
  uploadUrl: props.uploadUrl,
  deleteUrlBase: props.deleteUrlBase,
  reorderUrl: props.reorderUrl,
  folders: props.folders,
});

const refreshMedia = async () => {
  try {
    // Normalizar mediableType
    const mediableType = props.mediableType === 'App\\Models\\TypeB' ? 'type-b' : props.mediableType;
    console.log('Fetching media from:', `/media/${mediableType}/${props.identityId}`);
    const response = await axios.get(`/media/${mediableType}/${props.identityId}`, {
      withCredentials: true,
    });
    console.log('refreshMedia response:', response.data);
    localInitialMedia.value = response.data.media || [];
    console.log('Updated localInitialMedia:', localInitialMedia.value);
  } catch (error) {
    console.error('Error refreshing media:', error);
    toast.error($t('media.refresh_failed'));
  }
};
</script>

<template>
  <AppLayout title="Type B Dashboard">
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-4">Media Manager: {{ identity.name }}</h1>

      <h2 class="text-xl mb-2">Logo</h2>
      <MediaUploader
        :identity-id="identityId"
        :mediable-type="mediableType"
        :initial-media="localInitialMedia.filter(m => m.role === 'logo')"
        :upload-url="uploadUrl"
        :delete-url-base="deleteUrlBase"
        :reorder-url="reorderUrl"
        :folders="folders"
        mode="single"
        role="logo"
        allowed-mimes="image/jpeg,image/png"
        :min-dimensions="{ width: 200, height: 200 }"
        :max-dimensions="{ width: 1000, height: 1000 }"
        @media-updated="refreshMedia"
      />
    </div>
  </AppLayout>
</template>