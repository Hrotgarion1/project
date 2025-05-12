import { defineStore } from 'pinia';
import axios from 'axios';
import { ref } from 'vue';

export const useMediaStore = defineStore('media', () => {
  const media = ref([]);
  const previews = ref([]);

  function setMedia(newMedia) {
    console.log('setMedia input:', newMedia);
    media.value = newMedia.map(item => {
      const mediaItem = {
        ...item,
        isImage: item.file_type?.startsWith('image/') || false,
        isPdf: item.file_type === 'application/pdf' || false,
        isVideo: item.file_type?.startsWith('video/') || false,
        isYoutube: !!item.is_youtube,
        url: item.is_youtube ? null : item.file_path ? `/storage/${item.file_path}` : null,
        youtubeId: item.youtube_id || null,
      };
      console.log('Processed media item:', {
        id: item.id,
        file_type: item.file_type,
        file_path: item.file_path,
        url: mediaItem.url,
        isImage: mediaItem.isImage,
        isPdf: mediaItem.isPdf,
        isVideo: mediaItem.isVideo,
        isYoutube: mediaItem.isYoutube,
      });
      return mediaItem;
    });
  }

  async function addMedia(formData, uploadUrl) {
    if (!(formData instanceof FormData)) {
      console.error('Invalid formData:', formData);
      return { success: false, error: 'invalid_form_data' };
    }

    try {
      const response = await axios.post(uploadUrl, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
        withCredentials: true,
      });

      console.log('Response data:', response.data);

      const mediaData = response.data.media || response.data.data?.media || response.data;
      if (!mediaData || !mediaData.id) {
        console.error('Invalid media data in response:', response.data);
        return { success: false, error: 'invalid_response' };
      }

      const mediaItem = {
        ...mediaData,
        isImage: mediaData.file_type?.startsWith('image/') || false,
        isPdf: mediaData.file_type === 'application/pdf' || false,
        isVideo: mediaData.file_type?.startsWith('video/') || false,
        isYoutube: !!mediaData.is_youtube,
        url: mediaData.is_youtube ? null : mediaData.file_path ? `/storage/${mediaData.file_path}` : null,
        youtubeId: mediaData.youtube_id || null,
      };

      console.log('Added media item:', {
        id: mediaItem.id,
        file_type: mediaItem.file_type,
        file_path: mediaItem.file_path,
        url: mediaItem.url,
        isImage: mediaItem.isImage,
      });

      media.value.push(mediaItem);

      return { success: true, data: mediaItem };
    } catch (error) {
      console.error('Upload error:', error.response?.data || error.message);
      return {
        success: false,
        error: error.response?.data?.error || 'upload_failed',
        details: error.response?.data?.details || null,
      };
    } finally {
      previews.value = [];
    }
  }

  async function removeMedia(id, deleteUrl) {
    try {
      await axios.delete(deleteUrl, {
        withCredentials: true,
      });
      media.value = media.value.filter(item => item.id !== id);
      return { success: true };
    } catch (error) {
      console.error('Delete error:', error.response?.data || error.message);
      return {
        success: false,
        error: error.response?.data?.error || 'delete_failed',
      };
    }
  }

  async function reorderMedia(reorderUrl) {
    try {
      const order = media.value.map((item, index) => ({
        id: item.id,
        position: index + 1,
      }));
      await axios.post(reorderUrl, { order }, {
        withCredentials: true,
      });
      return { success: true };
    } catch (error) {
      console.error('Reorder error:', error.response?.data || error.message);
      return {
        success: false,
        error: error.response?.data?.error || 'reorder_failed',
      };
    }
  }

  return { media, previews, setMedia, addMedia, removeMedia, reorderMedia };
});