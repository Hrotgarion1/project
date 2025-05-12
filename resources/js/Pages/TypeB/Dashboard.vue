<template>
  <AppLayout title="Type B Dashboard">
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-4">Type B Dashboard: {{ identity.name }}</h1>
      <p>Email: {{ identity.email }}</p>
      <p>Slug: {{ identity.slug }}</p>

      <h2 class="text-xl mb-2 mt-8">Logo</h2>
      <MediaUploader
        :identity-id="identityId"
        :identity-slug="identitySlug"
        :mediable-type="mediableType"
        :initial-media="initialMedia.filter(m => m.role === 'logo')"
        :upload-url="uploadUrl"
        :delete-url="deleteUrl"
        :reorder-url="reorderUrl"
        :folders="folders"
        mode="single"
        role="logo"
        allowed-mimes="image/jpeg,image/png"
        :min-dimensions="{ width: 200, height: 200 }"
        :max-dimensions="{ width: 1000, height: 1000 }"
      />

      <h2 class="text-xl mb-2 mt-8">Cabecera</h2>
      <MediaUploader
        :identity-id="identityId"
        :identity-slug="identitySlug"
        :mediable-type="mediableType"
        :initial-media="initialMedia.filter(m => m.role === 'header')"
        :upload-url="uploadUrl"
        :delete-url="deleteUrl"
        :reorder-url="reorderUrl"
        :folders="folders"
        mode="single"
        role="header"
        allowed-mimes="image/jpeg,image/png"
        :min-dimensions="{ width: 800, height: 200 }"
        :max-dimensions="{ width: 1920, height: 400 }"
      />

      <h2 class="text-xl mb-2 mt-8">Galería</h2>
      <MediaUploader
        :identity-id="identityId"
        :identity-slug="identitySlug"
        :mediable-type="mediableType"
        :initial-media="initialMedia.filter(m => m.role === 'gallery')"
        :upload-url="uploadUrl"
        :delete-url="deleteUrl"
        :reorder-url="reorderUrl"
        :folders="folders"
        mode="multiple"
        role="gallery"
        allowed-mimes="image/jpeg,image/png,application/pdf"
      />

      <h2 class="text-xl mb-2 mt-8">Videos de YouTube</h2>
      <MediaUploader
        :identity-id="identityId"
        :identity-slug="identitySlug"
        :mediable-type="mediableType"
        :initial-media="initialMedia.filter(m => m.role === 'youtube')"
        :upload-url="uploadUrl"
        :delete-url="deleteUrl"
        :reorder-url="reorderUrl"
        :folders="folders"
        mode="youtube"
        role="youtube"
      />
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import MediaUploader from '@/Components/Common/MediaUploader.vue';

const props = defineProps({
  identity: Object,
  identityId: String,
  identitySlug: String,
  mediableType: {
    type: String,
    default: 'App\\Models\\TypeB',
  },
  initialMedia: Array,
  uploadUrl: String,
  deleteUrl: Function,
  reorderUrl: String,
  folders: {
    type: Array,
    default: () => ['General', 'Planos', 'Videos'],
  },
});

console.log('Dashboard props:', {
  identity: props.identity,
  uploadUrl: props.uploadUrl,
  identityId: props.identityId,
  identitySlug: props.identitySlug,
  mediableType: props.mediableType,
  initialMedia: props.initialMedia,
  folders: props.folders,
});
</script>