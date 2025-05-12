<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import GoBackButton from "@/Components/Common/GoBackButton.vue";
import ChangeRequestHistory from '@/Components/ChangeRequestHistory.vue';
import { usePage, router, Link } from '@inertiajs/vue3';
import axios from 'axios';
import { ref, computed, getCurrentInstance, onMounted } from 'vue';
import { toast } from 'vue3-toastify';
import alerts from '@/utils/alerts';

const page = usePage();
const instance = getCurrentInstance();
const $t = instance?.proxy.$t;

const props = defineProps({
  identity: Object,
  suspendReasons: Array,
  deleteReasons: Array,
  availableHandlers: Array,
});

const identityData = ref(props.identity);
const selectedSuspendReason = ref('');
const selectedDeleteReason = ref('');
const selectedHandler = ref('');
const requestedChanges = ref('');
const previews = ref({});

const showImageModal = ref(false);
const selectedImage = ref('');
const selectedImageType = ref('');
const selectedAttachedDoc = ref(null);
const isModalMaximized = ref(false);
const zoomLevel = ref(1);

const isAdminOrSupervisor = computed(() => 
  page.props.auth.user.roles.some(role => ['admin', 'supervisor'].includes(role.name))
);

const statusClass = (status = '') => ({
  'text-blue-600': status === 'pending',
  'text-yellow-600': status === 'in_progress',
  'text-violet-600': status === 'waiting',
  'text-green-600': status === 'approved',
  'text-red-600': status === 'rejected',
  'text-orange-600': status === 'suspended',
});

const getFileType = (path) => {
  const extension = path.split('.').pop().toLowerCase();
  return extension === 'pdf' ? 'pdf' : extension === 'txt' ? 'text' : 'image';
};

const fetchTextContent = async (path) => {
  try {
    const response = await axios.get(`/storage/${path}`, { responseType: 'text' });
    return response.data;
  } catch (error) {
    console.error(`Error fetching text content for ${path}:`, error);
    return $t('Error loading text content');
  }
};

// Cargar previsualizaciones al montar el componente
onMounted(async () => {
  console.log('Identity data:', identityData.value);
  if (identityData.value.identity_documents) {
    for (const doc of identityData.value.identity_documents) {
      if (doc.type === 'text') {
        previews.value[doc.name] = await fetchTextContent(doc.path);
      } else {
        previews.value[doc.name] = `/storage/${doc.path}`;
      }
    }
  }
});

const getMatchStatus = (requiredDoc) => {
  const attachedDoc = identityData.value.identity_documents?.find(doc => doc.name === requiredDoc.name);
  if (!attachedDoc) return 'missing';
  return attachedDoc.type === requiredDoc.type ? 'match' : 'mismatch';
};

const updateStatus = async (status) => {
  if (!identityData.value.slug) {
    toast.error($t('Error: Identity slug is missing'));
    return;
  }
  const result = await alerts.confirmUpdateStatus($t, status);
  if (result.isConfirmed) {
    try {
      const payload = {
        status,
        ...(status === 'approved' && {
          email: identityData.value.email || identityData.value.user.email,
          name: identityData.value.name || identityData.value.user.name,
          address: identityData.value.address || null,
        }),
      };
      const response = await axios.post(route('identities.updateStatus', { identity: identityData.value.slug }), payload);
      router.visit(route('identity-panel.index'), {
        preserveState: false,
        onSuccess: () => alerts.success($t, response.data.message),
      });
    } catch (error) {
      await alerts.error($t, `Error updating status to ${status}: ${error.response?.data?.message || error.message}`);
    }
  }
};

const suspendIdentity = async () => {
  if (!identityData.value.slug) {
    toast.error($t('Error: Identity slug is missing'));
    return;
  }
  if (!selectedSuspendReason.value) {
    toast.error($t('Please select a suspend reason'));
    return;
  }
  const result = await alerts.confirmSuspend($t);
  if (result.isConfirmed) {
    try {
      const response = await axios.post(route('identities.suspend', { identity: identityData.value.slug }), {
        reason_code: selectedSuspendReason.value,
      });
      router.visit(route('identity-panel.index'), {
        preserveState: false,
        onSuccess: () => alerts.success($t, response.data.message),
      });
    } catch (error) {
      await alerts.error($t, 'Error suspending: ' + (error.response?.data?.message || error.message));
    }
  }
};

const deleteIdentity = async () => {
  if (!identityData.value.slug) {
    toast.error($t('Error: Identity slug is missing'));
    return;
  }
  if (!selectedDeleteReason.value) {
    toast.error($t('Please select a delete reason'));
    return;
  }
  const result = await alerts.confirmDeletePermanent($t);
  if (result.isConfirmed) {
    try {
      const response = await axios.delete(route('identities.destroy', { identity: identityData.value.slug }), {
        data: { reason_code: selectedDeleteReason.value },
      });
      router.visit(route('identity-panel.index'), {
        preserveState: false,
        onSuccess: () => alerts.success($t, response.data.message),
      });
    } catch (error) {
      await alerts.error($t, 'Error deleting: ' + (error.response?.data?.message || error.message));
    }
  }
};

const reactivateIdentity = async () => {
  if (!identityData.value.slug) {
    toast.error($t('Error: Identity slug is missing'));
    return;
  }
  const result = await alerts.confirmReactivate($t);
  if (result.isConfirmed) {
    try {
      const response = await axios.post(route('identities.reactivate', { identity: identityData.value.slug }));
      router.visit(route('identity-panel.index'), {
        preserveState: false,
        onSuccess: () => alerts.success($t, response.data.message),
      });
    } catch (error) {
      await alerts.error($t, 'Error reactivating: ' + (error.response?.data?.message || error.message));
    }
  }
};

const reassignIdentity = async () => {
  if (!identityData.value.slug) {
    toast.error($t('Error: Identity slug is missing'));
    return;
  }
  if (!selectedHandler.value) {
    toast.error($t('Please select a handler'));
    return;
  }
  const result = await alerts.confirmReassign($t);
  if (result.isConfirmed) {
    try {
      const response = await axios.post(route('identities.reassign', { identity: identityData.value.slug }), {
        handled_by: selectedHandler.value,
      });
      identityData.value = response.data.identity;
      selectedHandler.value = '';
      await alerts.success($t, response.data.message);
    } catch (error) {
      await alerts.error($t, 'Error reassigning: ' + (error.response?.data?.message || error.message));
    }
  }
};

const requestMoreDocs = async () => {
  if (!identityData.value.slug) {
    toast.error($t('Error: Identity slug is missing'));
    return;
  }
  if (!requestedChanges.value) {
    toast.error($t('Please enter instructions for additional documents'));
    return;
  }
  const result = await alerts.confirmRequestMoreDocs($t);
  if (result.isConfirmed) {
    try {
      const response = await axios.post(route('identities.request-more-docs', { identity: identityData.value.slug }), {
        requested_changes: requestedChanges.value,
      });
      identityData.value = response.data.identity;
      requestedChanges.value = '';
      await alerts.success($t, response.data.message);
      router.visit(route('identity-panel.index'), { preserveState: true });
    } catch (error) {
      const errorMessage = error.response?.data?.message || error.message || 'Unknown error';
      await alerts.error($t, `Error requesting more documents: ${errorMessage}`);
    }
  }
};

const suspendReasonDescription = computed(() => {
  const reason = props.suspendReasons.find(r => r.code === selectedSuspendReason.value);
  return reason ? reason.description : '';
});

const deleteReasonDescription = computed(() => {
  const reason = props.deleteReasons.find(r => r.code === selectedDeleteReason.value);
  return reason ? reason.description : '';
});

const openImageModal = async (path) => {
  selectedImage.value = `/storage/${path}`;
  selectedImageType.value = getFileType(path);
  const requiredDoc = identityData.value.type.required_documents.find(doc => `/storage/${doc.sample_path}` === selectedImage.value);
  selectedAttachedDoc.value = identityData.value.identity_documents?.find(doc => doc.name === requiredDoc?.name) || null;
  if (selectedAttachedDoc.value && selectedAttachedDoc.value.type === 'text' && !previews.value[selectedAttachedDoc.value.name]) {
    previews.value[selectedAttachedDoc.value.name] = await fetchTextContent(selectedAttachedDoc.value.path);
  }
  if (selectedImageType.value === 'text' && !previews.value[requiredDoc?.name]) {
    previews.value[requiredDoc.name] = await fetchTextContent(path);
  }
  showImageModal.value = true;
  isModalMaximized.value = false;
  zoomLevel.value = 1;
};

const toggleMaximize = () => {
  isModalMaximized.value = !isModalMaximized.value;
};

const zoomIn = () => {
  if (zoomLevel.value < 3) zoomLevel.value += 0.25;
};

const zoomOut = () => {
  if (zoomLevel.value > 0.5) zoomLevel.value -= 0.25;
};

const resetZoom = () => {
  zoomLevel.value = 1;
};
</script>

<template>
  <AppLayout :title="$t('Identity Approval')">
    <template #header>
      <div class="flex items-center space-x-2">
        <GoBackButton />
        <h1 class="font-semibold text-xl text-gray-800 leading-tight">{{ $t('Identity Approval') }}</h1>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="p-6 bg-white border-b border-gray-200 rounded-lg shadow">
          <h2 class="text-lg font-semibold mb-4">{{ $t('Identity Details') }}</h2>

          <div v-if="identityData" class="space-y-4">
            <p><strong>{{ $t('User') }}:</strong> {{ identityData.user?.name || $t('User not available') }}</p>
            <p><strong>{{ $t('Email') }}:</strong> {{ identityData.email || identityData.user?.email || $t('No email') }}</p>
            <p><strong>{{ $t('Requested Role') }}:</strong> {{ identityData.role || $t('No role specified') }}</p>
            <p><strong>{{ $t('Status') }}:</strong> <span :class="statusClass(identityData.status)">{{ $t(identityData.status || 'Unknown') }}</span></p>
            <p><strong>{{ $t('Handled By') }}:</strong> {{ identityData.handled_by?.name || $t('Not assigned') }}</p>

            <!-- Sección de documentos adjuntos enviados por el usuario -->
            <div v-if="identityData.identity_documents?.length" class="mt-6">
              <h3 class="font-semibold mb-2">{{ $t('Attached Documents') }}</h3>
              <div class="space-y-4">
                <div v-for="(doc, index) in identityData.identity_documents" :key="index" class="border rounded p-4">
                  <p>{{ doc.name || $t('Document') }} ({{ doc.type }})</p>
                  <embed 
                    v-if="doc.type === 'pdf'" 
                    :src="previews[doc.name]" 
                    type="application/pdf" 
                    class="w-full h-64 rounded" 
                  />
                  <img 
                    v-else-if="doc.type === 'image'" 
                    :src="previews[doc.name]" 
                    class="w-full h-64 object-contain rounded" 
                  />
                  <pre 
                    v-else-if="doc.type === 'text'" 
                    class="w-full h-64 overflow-auto p-2 bg-gray-100 rounded"
                  >{{ previews[doc.name] || 'Loading...' }}</pre>
                </div>
              </div>
            </div>
            <p v-else class="text-gray-500">{{ $t('No documents attached') }}</p>

            <!-- Sección de documentos requeridos con ejemplos -->
            <div v-if="identityData.type?.required_documents?.length" class="mt-6">
              <h3 class="font-semibold mb-2">{{ $t('Required Documents (Examples)') }}</h3>
              <div class="space-y-4">
                <div v-for="(doc, index) in identityData.type.required_documents" :key="index" class="border rounded p-4 flex items-start space-x-4">
                  <div>
                    <p><strong>{{ $t('Name') }}:</strong> {{ doc.name }}</p>
                    <p><strong>{{ $t('Type') }}:</strong> {{ $t(doc.type) }}</p>
                    <p><strong>{{ $t('Description') }}:</strong> {{ doc.description || $t('No description') }}</p>
                    <p><strong>{{ $t('Status') }}:</strong> 
                      <span :class="{
                        'text-green-600': getMatchStatus(doc) === 'match',
                        'text-red-600': getMatchStatus(doc) === 'mismatch',
                        'text-gray-600': getMatchStatus(doc) === 'missing'
                      }">
                        {{ $t(getMatchStatus(doc)) }}
                      </span>
                    </p>
                  </div>
                  <div v-if="doc.sample_path" class="flex-shrink-0">
                    <div v-if="getFileType(doc.sample_path) === 'pdf'" class="w-20 h-20 flex items-center justify-center bg-gray-200 rounded cursor-pointer" @click="openImageModal(doc.sample_path)">
                      <svg class="w-10 h-10 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                      </svg>
                    </div>
                    <img 
                      v-else-if="getFileType(doc.sample_path) === 'image'"
                      :src="'/storage/' + doc.sample_path" 
                      class="w-20 h-20 object-cover rounded cursor-pointer" 
                      :alt="$t('Sample for') + ' ' + doc.name" 
                      @click="openImageModal(doc.sample_path)"
                    />
                    <pre 
                      v-else-if="getFileType(doc.sample_path) === 'text'" 
                      class="w-20 h-20 overflow-auto border rounded p-1 text-xs bg-gray-100 cursor-pointer"
                      @click="openImageModal(doc.sample_path)"
                    >{{ previews[doc.name] || 'Text Sample' }}</pre>
                  </div>
                </div>
              </div>
            </div>
            <p v-else class="text-gray-500">{{ $t('No required documents defined for this identity type') }}</p>

            <div class="mt-6">
              <ChangeRequestHistory :requests="identityData.change_requests" />
            </div>

            <div class="mt-6 space-y-4">
              <div v-if="identityData.status === 'pending' || identityData.status === 'in_progress' || identityData.status === 'waiting'" class="flex space-x-2">
                <button @click="updateStatus('approved')" class="px-3 py-1 text-sm bg-green-600 text-white rounded hover:bg-green-500">
                  {{ $t('Approve') }}
                </button>
                <button @click="updateStatus('rejected')" class="px-3 py-1 text-sm bg-red-600 text-white rounded hover:bg-red-500">
                  {{ $t('Reject') }}
                </button>
              </div>
              <div v-if="identityData.status === 'pending' || identityData.status === 'in_progress' || identityData.status === 'waiting'" class="flex items-start space-x-2">
                <div class="flex-1">
                  <textarea
                    v-model="requestedChanges"
                    :placeholder="$t('Enter instructions for additional documents')"
                    class="w-full border rounded px-2 py-1 text-sm"
                    rows="3"
                  ></textarea>
                </div>
                <button @click="requestMoreDocs" class="px-3 py-1 text-sm bg-violet-600 text-white rounded hover:bg-violet-500">
                  {{ $t('Request More Docs') }}
                </button>
              </div>
              <div v-if="identityData.status === 'approved'" class="flex items-start space-x-2">
                <div class="flex-1">
                  <select v-model="selectedSuspendReason" class="w-full border rounded px-2 py-1 text-sm">
                    <option value="">{{ $t('Select a suspend reason') }}</option>
                    <option v-for="reason in props.suspendReasons" :key="reason.code" :value="reason.code">{{ reason.title }}</option>
                  </select>
                  <p v-if="suspendReasonDescription" class="text-xs text-gray-600 mt-1">{{ suspendReasonDescription }}</p>
                </div>
                <button @click="suspendIdentity" class="px-3 py-1 text-sm bg-yellow-600 text-white rounded hover:bg-yellow-500">
                  {{ $t('Suspend') }}
                </button>
              </div>
              <div v-if="identityData.status === 'suspended'" class="flex space-x-2">
                <button @click="reactivateIdentity" class="px-3 py-1 text-sm bg-green-600 text-white rounded hover:bg-green-500">
                  {{ $t('Reactivate') }}
                </button>
              </div>
              <div class="flex items-start space-x-2">
                <div class="flex-1">
                  <select v-model="selectedDeleteReason" class="w-full border rounded px-2 py-1 text-sm">
                    <option value="">{{ $t('Select a delete reason') }}</option>
                    <option v-for="reason in props.deleteReasons" :key="reason.code" :value="reason.code">{{ reason.title }}</option>
                  </select>
                  <p v-if="deleteReasonDescription" class="text-xs text-gray-600 mt-1">{{ deleteReasonDescription }}</p>
                </div>
                <button @click="deleteIdentity" class="px-3 py-1 text-sm bg-red-700 text-white rounded hover:bg-red-800">
                  {{ $t('Delete') }}
                </button>
              </div>
              <div v-if="isAdminOrSupervisor" class="flex items-start space-x-2">
                <div class="flex-1">
                  <select v-model="selectedHandler" class="w-full border rounded px-2 py-1 text-sm">
                    <option value="">{{ $t('Select a handler') }}</option>
                    <option v-for="handler in props.availableHandlers" :key="handler.id" :value="handler.id">
                      {{ handler.name }} ({{ handler.roles.join(', ') }})
                    </option>
                  </select>
                </div>
                <button @click="reassignIdentity" class="px-3 py-1 text-sm bg-purple-600 text-white rounded hover:bg-purple-500">
                  {{ $t('Reassign') }}
                </button>
              </div>
            </div>
          </div>
          <p v-else class="text-gray-500">{{ $t('Loading identity details...') }}</p>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showImageModal" class="fixed inset-y-0 right-0 w-full bg-white shadow-lg z-50 transition-all duration-300">
      <div class="h-full flex flex-col">
        <div class="p-4 flex justify-between items-center border-b">
          <h3 class="text-lg font-semibold">{{ $t('Document Preview') }}</h3>
          <div class="flex space-x-2">
            <button @click="zoomOut" class="p-1 text-gray-600 hover:text-gray-800" aria-label="Zoom Out">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7"></path>
              </svg>
            </button>
            <button @click="zoomIn" class="p-1 text-gray-600 hover:text-gray-800" aria-label="Zoom In">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m-3-3h6"></path>
              </svg>
            </button>
            <button @click="resetZoom" class="p-1 text-gray-600 hover:text-gray-800" aria-label="Reset Zoom">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
              </svg>
            </button>
            <button @click="toggleMaximize" class="p-1 text-gray-600 hover:text-gray-800">
              <svg v-if="!isModalMaximized" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l4 4m12-4v4m0-4h-4m4 4l-4-4M4 16v4m0 0h4m-4 0l4-4m12 4v-4m0 4h-4m4 0l-4 4"></path>
              </svg>
              <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 4H4v4m4-4L4 8m12-4h4v4m-4-4l4 4m-12 12h-4v-4m4 4l-4-4m12-4h4v4m-4-4l4-4"></path>
              </svg>
            </button>
            <button @click="showImageModal = false" class="p-1 text-gray-600 hover:text-gray-800">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
        </div>
        <div class="flex-1 overflow-y-auto p-4 flex space-x-4">
          <div class="w-1/2 overflow-x-auto">
            <h4 class="text-sm font-semibold mb-2">{{ $t('Sample') }}</h4>
            <embed 
              v-if="selectedImageType === 'pdf'" 
              :src="selectedImage" 
              type="application/pdf" 
              class="min-w-full h-full rounded" 
              :style="{ transform: `scale(${zoomLevel})`, transformOrigin: 'top left' }"
            />
            <img 
              v-else-if="selectedImageType === 'image'" 
              :src="selectedImage" 
              class="min-w-full h-auto rounded" 
              :alt="$t('Enlarged Sample Document')"
              :style="{ transform: `scale(${zoomLevel})`, transformOrigin: 'top left' }"
            />
            <pre 
              v-else-if="selectedImageType === 'text'" 
              class="min-w-full h-full overflow-auto p-2 bg-gray-100 rounded"
              :style="{ transform: `scale(${zoomLevel})`, transformOrigin: 'top left' }"
            >{{ previews[selectedAttachedDoc?.name] || 'Loading...' }}</pre>
          </div>
          <div v-if="selectedAttachedDoc" class="w-1/2 overflow-x-auto">
            <h4 class="text-sm font-semibold mb-2">{{ $t('Attached') }}</h4>
            <embed 
              v-if="selectedAttachedDoc.type === 'pdf'" 
              :src="previews[selectedAttachedDoc.name]" 
              type="application/pdf" 
              class="min-w-full h-full rounded" 
              :style="{ transform: `scale(${zoomLevel})`, transformOrigin: 'top left' }"
            />
            <img 
              v-else-if="selectedAttachedDoc.type === 'image'" 
              :src="previews[selectedAttachedDoc.name]" 
              class="min-w-full h-auto rounded" 
              :alt="$t('Enlarged Attached Document')"
              :style="{ transform: `scale(${zoomLevel})`, transformOrigin: 'top left' }"
            />
            <pre 
              v-else-if="selectedAttachedDoc.type === 'text'" 
              class="min-w-full h-full overflow-auto p-2 bg-gray-100 rounded"
              :style="{ transform: `scale(${zoomLevel})`, transformOrigin: 'top left' }"
            >{{ previews[selectedAttachedDoc.name] || 'Loading...' }}</pre>
          </div>
          <div v-else class="w-1/2 flex items-center justify-center text-gray-500">
            {{ $t('No matching attached document') }}
          </div>
        </div>
      </div>
    </div>
    <div v-if="showImageModal && isModalMaximized" class="fixed inset-0 bg-black bg-opacity-50 z-40" @click="isModalMaximized = false"></div>
  </AppLayout>
</template>

<style scoped>
.border-blue-700 {
  border-color: #164C73;
}
.text-blue-700 {
  color: #164C73;
}
.transition-all {
  transition: width 0.3s ease-in-out;
}
</style>