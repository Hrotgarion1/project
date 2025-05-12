<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import GoBackButton from "@/Components/Common/GoBackButton.vue";
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, getCurrentInstance } from 'vue';
import { toast } from 'vue3-toastify';
import alerts from '@/utils/alerts';
import { useIdentityStore } from '@/stores/identityStore';
import axios from 'axios';

const page = usePage();
const instance = getCurrentInstance();
const $t = instance?.proxy.$t;

const props = defineProps({
  handledIdentities: Object,
  allIdentities: Object,
  search_handled: String,
  search_all: String,
  status_handled: String,
  status_all: String,
  availableHandlers: Array,
  handledCounts: Object,
  allCounts: Object,
});

const store = useIdentityStore();
store.setHandledIdentities(props.handledIdentities?.data || []);
store.setAllIdentities(props.allIdentities?.data || []);

const searchInputHandled = ref(props.search_handled || '');
const searchInputAll = ref(props.search_all || '');
const statusFilterHandled = ref(props.status_handled || '');
const statusFilterAll = ref(props.status_all || '');
const selectedHandler = ref('');

const isAdmin = computed(() => page.props.auth?.user?.roles?.some(role => role.name === 'admin') || false);
const isAdminOrSupervisor = computed(() => 
  page.props.auth.user.roles.some(role => ['admin', 'supervisor'].includes(role.name))
);
const isEditorOnly = computed(() => 
  page.props.auth.user.roles.some(role => role.name === 'editor') && !isAdminOrSupervisor.value
);

const statusOptionsHandled = computed(() => {
  const options = [
    { value: '', label: 'Todos' },
    { value: 'pending', label: 'Pendiente' },
    { value: 'in_progress', label: 'En progreso' },
    { value: 'waiting', label: 'Esperando documentación' },
    { value: 'approved', label: 'Aprobada' },
    { value: 'rejected', label: 'Rechazada' },
    { value: 'suspended', label: 'Suspendida' },
    { value: 'deleted', label: 'Eliminada' },
  ];
  if (isEditorOnly.value) {
    return options.filter(opt => ['in_progress', 'waiting', 'approved'].includes(opt.value) || opt.value === '');
  }
  if (!isAdmin.value) {
    return options.filter(opt => opt.value !== 'deleted');
  }
  return options;
});

const statusOptionsAll = computed(() => {
  const options = [
    { value: '', label: 'Todos' },
    { value: 'pending', label: 'Pendiente' },
    { value: 'in_progress', label: 'En progreso' },
    { value: 'waiting', label: 'Esperando documentación' },
    { value: 'approved', label: 'Aprobada' },
    { value: 'rejected', label: 'Rechazada' },
    { value: 'suspended', label: 'Suspendida' },
    { value: 'deleted', label: 'Eliminada' },
  ];
  if (!isAdmin.value) {
    return options.filter(opt => opt.value !== 'deleted');
  }
  return options;
});

const applySearch = (isHandled) => {
  const routeName = isHandled ? 'admin.my-requests.index' : 'admin.identities.index';
  router.get(route(routeName), {
    search_handled: searchInputHandled.value,
    search_all: isHandled ? '' : searchInputAll.value,
    status_handled: isHandled ? statusFilterHandled.value : props.status_handled,
    status_all: !isHandled ? statusFilterAll.value : props.status_all,
    handled_page: isHandled ? 1 : props.handledIdentities?.current_page || 1,
    all_page: !isHandled ? 1 : props.allIdentities?.current_page || 1,
  }, {
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => {
      store.setHandledIdentities(props.handledIdentities?.data || []);
      store.setAllIdentities(props.allIdentities?.data || []);
    },
  });
};

const filteredHandledIdentities = computed(() => store.handledIdentities);
const filteredAllIdentities = computed(() => store.allIdentities);

const openApproval = async (identity) => {
  const user = page.props.auth.user;
  console.log('Identity data:', identity); // Depuración
  if (!identity.slug) {
    await alerts.error($t, 'Error: No se encontró el slug de la identidad');
    console.error('Missing slug for identity:', identity);
    return;
  }
  if (isEditorOnly.value) {
    if (identity.status !== 'pending' && (identity.handled_by?.id !== user.id || !['in_progress', 'waiting', 'approved'].includes(identity.status))) {
      await alerts.error($t, 'Editors can only work on their own in_progress, waiting, or approved requests.');
      return;
    }
  } else if (identity.status === 'in_progress' && !isAdmin.value && identity.handled_by?.id !== user.id) {
    await alerts.error($t, 'Only the assigned editor or an admin can handle this request.');
    return;
  }
  if (identity.status === 'pending') {
    try {
      console.log('Taking request for slug:', identity.slug);
      const response = await axios.post(route('identities.takeRequest', { identity: identity.slug }));
      await alerts.success($t, response.data.message);
      identity.status = 'in_progress';
      identity.handled_by = { id: user.id, name: user.name };
    } catch (error) {
      console.error('Error taking request:', error);
      await alerts.error($t, 'Error taking request: ' + (error.response?.data?.message || error.message));
      return;
    }
  }
  router.visit(route('admin.identities.show', { identity: identity.slug }));
};

const restoreIdentity = async (identitySlug) => { // Change parameter to identitySlug
  const result = await alerts.confirmRestore($t);
  if (result.isConfirmed) {
    try {
      const response = await axios.post(route('identities.restore', identitySlug)); // Use slug
      router.get(route('admin.identities.index'), { 
        search_handled: searchInputHandled.value,
        search_all: searchInputAll.value,
        status_handled: statusFilterHandled.value,
        status_all: statusFilterAll.value,
      }, { 
        preserveState: false,
        onSuccess: () => alerts.success($t, response.data.message),
      });
    } catch (error) {
      await alerts.error($t, 'Error restoring: ' + (error.response?.data?.message || error.message));
    }
  }
};

const statusClass = (status = '') => ({
  'text-blue-600': status === 'pending',
  'text-green-600': status === 'approved',
  'text-yellow-600': status === 'in_progress',
  'text-violet-600': status === 'waiting',
  'text-orange-600': status === 'suspended',
  'text-red-600': status === 'rejected',
  'text-gray-600': status === 'deleted',
});

const bulkReassign = async () => {
  if (!store.selectedIdentities.length) {
    toast.error($t('Please select at least one identity'));
    return;
  }
  if (!selectedHandler.value) {
    toast.error($t('Please select a handler'));
    return;
  }
  const result = await alerts.confirmReassign($t);
  if (result.isConfirmed) {
    try {
      const message = await store.bulkReassign(selectedHandler.value); // Use store.bulkReassign
      await alerts.success($t, message);
      selectedHandler.value = '';
      // No need to update store manually; bulkReassign does it
      router.get(route('admin.identities.index'), {
        search_handled: searchInputHandled.value,
        search_all: searchInputAll.value,
        status_handled: statusFilterHandled.value,
        status_all: statusFilterAll.value,
      }, {
        preserveState: false,
        preserveScroll: true,
      });
    } catch (error) {
      await alerts.error($t, 'Error reassigning: ' + (error.response?.data?.message || error.message));
    }
  }
};
</script>

<template>
  <AppLayout :title="$t('Panel Request')">
    <template #header>
      <div class="flex items-center space-x-2">
        <GoBackButton />
        <h1 class="font-semibold text-xl text-gray-800 leading-tight">
          {{ $t("Panel Request") }}
        </h1>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="p-6 bg-white border-b border-gray-200">
          <div v-if="props.handledIdentities" class="mb-6">
            <div class="flex justify-between mb-4 space-x-4">
              <div class="flex-1">
                <input
                  type="text"
                  v-model="searchInputHandled"
                  @input="applySearch(true)"
                  :placeholder="$t('Search By Name, E-Mail or Role...')"
                  class="border rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <select
                  v-model="statusFilterHandled"
                  @change="applySearch(true)"
                  class="border rounded px-3 py-2 w-48 bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none custom-select"
                >
                  <option v-for="option in statusOptionsHandled" :key="option.value" :value="option.value">
                    {{ $t(option.label) }}
                  </option>
                </select>
              </div>
            </div>

            <div v-if="isAdminOrSupervisor" class="flex justify-end mb-4 space-x-2">
              <select v-model="selectedHandler" class="border rounded px-2 py-1 text-sm custom-select">
                <option value="">{{ $t('Select a handler') }}</option>
                <option v-for="handler in props.availableHandlers" :key="handler.id" :value="handler.id">
                  {{ handler.name }} ({{ handler.roles.join(', ') }})
                </option>
              </select>
              <button @click="bulkReassign" class="px-3 py-1 bg-purple-600 text-white rounded hover:bg-purple-500">
                {{ $t('Reassign Selected') }}
              </button>
            </div>

            <h2 class="text-lg font-semibold mb-2">{{ $t('My Assigned Requests') }}</h2>
            <div class="mb-4 flex space-x-4">
              <span class="text-sm text-gray-600">{{ $t('In Progress') }}: {{ props.handledCounts.in_progress }}</span>
              <span class="text-sm text-gray-600">{{ $t('Waiting') }}: {{ props.handledCounts.waiting }}</span>
              <span class="text-sm text-gray-600">{{ $t('Approved') }}: {{ props.handledCounts.approved }}</span>
            </div>
            <div class="overflow-x-auto mb-6">
              <table class="min-w-full divide-y divide-gray-200 border border-gray-300">
                <thead class="bg-gray-100">
                  <tr>
                    <th v-if="isAdminOrSupervisor" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      <input type="checkbox" @change="store.selectedIdentities = $event.target.checked ? filteredHandledIdentities.map(i => i.id) : []" />
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('User') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('Requested Roles') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('Status') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('Handled By') }}</th>
                    <th v-if="isAdmin" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('Actions') }}</th>
                  </tr>
                </thead>
                <tbody v-if="filteredHandledIdentities.length">
                  <tr v-for="identity in filteredHandledIdentities" :key="identity.id" class="border-b border-gray-300 hover:bg-gray-300 transition cursor-pointer">
                    <td v-if="isAdminOrSupervisor" class="px-6 py-4">
                      <input type="checkbox" :checked="store.selectedIdentities.includes(identity.id)" @change="store.toggleSelection(identity.id)" />
                    </td>
                    <td class="px-6 py-4" @click="openApproval(identity)">{{ identity.user?.name || $t('User not available') }}</td>
                    <td class="px-6 py-4" @click="openApproval(identity)">{{ identity.role || $t('No roles requested') }}</td>
                    <td class="px-6 py-4" @click="openApproval(identity)">
                      <span :class="statusClass(identity.deleted_at ? 'deleted' : identity.status)">
                        {{ identity.deleted_at ? $t('Deleted') : $t(identity.status || 'Unknown') }}
                        <span v-if="identity.has_updates && !identity.deleted_at" class="ml-2">🔔</span>
                      </span>
                    </td>
                    <td class="px-6 py-4" @click="openApproval(identity)">{{ identity.handled_by?.name || $t('Not assigned') }}</td>
                    <td v-if="isAdmin" class="px-6 py-4">
                      <button v-if="identity.deleted_at" @click.stop="restoreIdentity(identity.slug)" class="px-2 py-1 bg-green-500 text-white rounded hover:bg-green-600">
                        {{ $t('Restore') }}
                      </button>
                    </td>
                  </tr>
                </tbody>
                <tbody v-else>
                  <tr>
                    <td :colspan="isAdmin ? 6 : 5" class="text-center text-gray-500 py-4">{{ $t('No assigned requests') }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div v-if="props.handledIdentities?.links?.length > 3" class="flex justify-center mt-4 space-x-2">
              <Link v-for="link in props.handledIdentities.links" :key="link.url" :href="link.url || '#'" v-html="link.label" class="px-3 py-1 border rounded text-sm" :class="{ 'bg-blue-500 text-white': link.active, 'hover:bg-gray-200': link.url, 'cursor-not-allowed opacity-50': !link.url }" :disabled="!link.url" />
            </div>
          </div>

          <div v-if="props.allIdentities">
            <h2 class="text-lg font-semibold mb-2 mt-6">{{ $t('All Requests') }}</h2>
            <div v-if="isAdmin" class="mb-4 flex flex-wrap gap-4">
              <span class="text-sm text-gray-600">{{ $t('Total') }}: {{ props.allCounts.total }}</span>
              <span class="text-sm text-gray-600">{{ $t('Pending') }}: {{ props.allCounts.pending }}</span>
              <span class="text-sm text-gray-600">{{ $t('In Progress') }}: {{ props.allCounts.in_progress }}</span>
              <span class="text-sm text-gray-600">{{ $t('Waiting') }}: {{ props.allCounts.waiting }}</span>
              <span class="text-sm text-gray-600">{{ $t('Approved') }}: {{ props.allCounts.approved }}</span>
              <span class="text-sm text-gray-600">{{ $t('Rejected') }}: {{ props.allCounts.rejected }}</span>
              <span class="text-sm text-gray-600">{{ $t('Suspended') }}: {{ props.allCounts.suspended }}</span>
              <span class="text-sm text-gray-600">{{ $t('Deleted') }}: {{ props.allCounts.deleted }}</span>
            </div>
            <div v-if="isAdminOrSupervisor" class="flex justify-between mb-4 space-x-4">
              <div class="flex-1">
                <input
                  type="text"
                  v-model="searchInputAll"
                  @input="applySearch(false)"
                  :placeholder="$t('Search by name, email, role, or handled by...')"
                  class="border rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <select
                  v-model="statusFilterAll"
                  @change="applySearch(false)"
                  class="border rounded px-3 py-2 w-48 bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none custom-select"
                >
                  <option v-for="option in statusOptionsAll" :key="option.value" :value="option.value">
                    {{ $t(option.label) }}
                  </option>
                </select>
              </div>
            </div>
            <div v-else class="flex justify-between mb-4 space-x-4">
              <div class="flex-1">
                <input
                  type="text"
                  v-model="searchInputAll"
                  @input="applySearch(false)"
                  :placeholder="$t('Search by name, email, or role...')"
                  class="border rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>

            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200 border border-gray-300">
                <thead class="bg-gray-100">
                  <tr>
                    <th v-if="isAdminOrSupervisor" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      <input type="checkbox" @change="store.selectedIdentities = $event.target.checked ? filteredAllIdentities.map(i => i.id) : []" />
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('User') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('Requested Roles') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('Status') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('Handled By') }}</th>
                    <th v-if="isAdmin" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('Actions') }}</th>
                  </tr>
                </thead>
                <tbody v-if="filteredAllIdentities.length">
                  <tr v-for="identity in filteredAllIdentities" :key="identity.id" class="border-b border-gray-300 hover:bg-gray-300 transition cursor-pointer">
                    <td v-if="isAdminOrSupervisor" class="px-6 py-4">
                      <input type="checkbox" :checked="store.selectedIdentities.includes(identity.id)" @change="store.toggleSelection(identity.id)" />
                    </td>
                    <td class="px-6 py-4" @click="openApproval(identity)">{{ identity.user?.name || $t('User not available') }}</td>
                    <td class="px-6 py-4" @click="openApproval(identity)">{{ identity.role || $t('No roles requested') }}</td>
                    <td class="px-6 py-4" @click="openApproval(identity)">
                      <span :class="statusClass(identity.deleted_at ? 'deleted' : identity.status)">
                        {{ identity.deleted_at ? $t('Deleted') : $t(identity.status || 'Unknown') }}
                        <span v-if="identity.has_updates && !identity.deleted_at" class="ml-2">🔔</span>
                      </span>
                    </td>
                    <td class="px-6 py-4" @click="openApproval(identity)">{{ identity.handled_by?.name || $t('Not assigned') }}</td>
                    <td v-if="isAdmin" class="px-6 py-4">
                      <button v-if="identity.deleted_at" @click.stop="restoreIdentity(identity.slug)" class="px-2 py-1 bg-green-500 text-white rounded hover:bg-green-600">
                        {{ $t('Restore') }}
                      </button>
                    </td>
                  </tr>
                </tbody>
                <tbody v-else>
                  <tr>
                    <td :colspan="isAdmin ? 6 : 5" class="text-center text-gray-500 py-4">{{ $t('No identity requests found') }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div v-if="props.allIdentities?.links?.length > 3" class="flex justify-center mt-4 space-x-2">
              <Link v-for="link in props.allIdentities.links" :key="link.url" :href="link.url || '#'" v-html="link.label" class="px-3 py-1 border rounded text-sm" :class="{ 'bg-blue-500 text-white': link.active, 'hover:bg-gray-200': link.url, 'cursor-not-allowed opacity-50': !link.url }" :disabled="!link.url" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.custom-select {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='gray'%3E%3Cpath d='M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z'/%3E%3C/svg%3E");
  background-position: right 1rem center;
  background-repeat: no-repeat;
  background-size: 1.5em;
  padding-right: 2.5rem;
}

input:focus, select:focus {
  border-color: #3b82f6;
}
</style>