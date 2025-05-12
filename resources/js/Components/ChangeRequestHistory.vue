<template>
  <div class="mt-6">
    <h3 class="font-semibold mb-2">{{ $t(title) }}</h3>
    <div class="max-h-[7.5rem] overflow-y-auto border rounded p-2">
      <div v-if="props.requests && props.requests.length" class="space-y-2">
        <div v-for="request in props.requests" :key="request.id" class="text-sm">
          <p>{{ request.message }}</p>
          <p class="text-gray-500 text-xs">
            Sent on: {{ formatDate(request.sent_at) }} by {{ request.sender?.name || `User ID: ${request.sent_by}` }}
          </p>
        </div>
      </div>
      <p v-else class="text-gray-500 text-sm">{{ $t('No change requests yet') }}</p>
    </div>
  </div>
</template>

<script setup>
import { defineProps } from 'vue';

const props = defineProps({
  requests: {
      type: Array,
      default: () => [],
      required: true,
  },
  title: {
      type: String,
      default: 'Change Request History',
  },
});

const formatDate = (date) => {
  return new Date(date).toLocaleString();
};
</script>

<style scoped>
.max-h-\[7\.5rem\] {
    max-height: 7.5rem;
}
.overflow-y-auto {
    overflow-y: auto;
    scrollbar-width: thin; /* Firefox */
    scrollbar-color: #9CA3AF #E5E7EB; /* Firefox */
}
.overflow-y-auto::-webkit-scrollbar {
    width: 8px; /* Chrome, Safari */
}
.overflow-y-auto::-webkit-scrollbar-thumb {
    background-color: #9CA3AF; /* Gris */
    border-radius: 4px;
}
.overflow-y-auto::-webkit-scrollbar-track {
    background-color: #E5E7EB; /* Fondo claro */
}
</style>