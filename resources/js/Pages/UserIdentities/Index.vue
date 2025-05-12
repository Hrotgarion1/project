<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import HeaderSection from '@/Components/Common/HeaderSection.vue';
import { ref, getCurrentInstance } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import alerts from '@/utils/alerts';

const props = defineProps({
  identities: Object,
  userRole: String,
});

const page = usePage();
const message = ref(page.props.flash?.message || null);
const errors = ref(page.props.errors || {});

const instance = getCurrentInstance();
const $t = instance?.proxy.$t;

const deleteIdentity = async (identityId) => {
  const result = await alerts.confirmDeleteIdentity($t);
  if (result.isConfirmed) {
    router.delete(route('user.identities.destroy', identityId), {
      onSuccess: () => {
        alerts.success($t, $t('Identity deleted successfully'));
      },
      onError: () => {
        alerts.error($t, $t('Error deleting identity'));
      },
    });
  }
};

const statusClass = (status = '') => ({
  'text-secondary-0': status === 'pending',
  'text-secondary-1': status === 'approved',
  'text-secondary-2': status === 'in_progress',
  'text-primary-2': status === 'waiting',
  'text-secondary-3': status === 'rejected',
});
</script>

<template>
  <AppLayout :title="$t('My Identities Request')">
    <div class="container mx-auto p-4 bg-neutral-3 dark:bg-neutral-1 min-h-screen">
      <HeaderSection
        :title="$t('My Identities')"
        :show-back-button="true"
      />

      <div v-if="message" class="mb-4 p-4 bg-secondary-1 dark:bg-secondary-1 text-neutral-0 dark:text-neutral-0 rounded-lg">
        {{ message }}
      </div>
      <div v-if="errors.message" class="mb-4 p-4 bg-secondary-3 dark:bg-secondary-3 text-neutral-0 dark:text-neutral-0 rounded-lg">
        {{ errors.message }}
      </div>

      <div v-if="!identities.data.length" class="text-center text-neutral-2 dark:text-neutral-0">
        {{ $t('Whithout identities') }}
      </div>

      <div v-else>
        <!-- Diseño de tarjetas para pantallas pequeñas -->
        <div class="block sm:hidden space-y-4">
          <div
            v-for="identity in identities.data"
            :key="identity.id"
            class="bg-neutral-0 dark:bg-neutral-2 border border-neutral-4 dark:border-neutral-2 rounded-lg shadow-sm"
          >
            <div class="bg-main-0 dark:bg-main-0 px-4 py-2 rounded-t-lg">
              <h3 class="text-neutral-0 dark:text-neutral-0 font-semibold">
                {{ identity.name }}
              </h3>
            </div>
            <div class="border-b-4 border-secondary-3"></div>
            <div class="p-4 text-sm text-neutral-2 dark:text-neutral-0">
              <p><span class="font-medium text-neutral-1 dark:text-neutral-0">{{ $t('Identity type') }}:</span> {{ identity.role_name }}</p>
              <p><span class="font-medium text-neutral-1 dark:text-neutral-0">{{ $t('Email') }}:</span> {{ identity.email }}</p>
              <p><span class="font-medium text-neutral-1 dark:text-neutral-0">{{ $t('Phone') }}:</span> {{ identity.phone || $t('na') }}</p>
              <p><span class="font-medium text-neutral-1 dark:text-neutral-0">{{ $t('Status') }}:</span> <span :class="statusClass(identity.status)">{{ $t(identity.status || 'unknown') }}<span v-if="identity.has_unseen_requests" class="ml-2">🔔</span></span></p>
              <p><span class="font-medium text-neutral-1 dark:text-neutral-0">{{ $t('Handled By') }}:</span> {{ identity.handled_by ? identity.handled_by.name : $t('Not assigned') }}</p>
              <div v-if="userRole === 'invitado'" class="mt-3 flex gap-2">
                <Link
                  v-if="identity.status === 'pending' || identity.status === 'in_progress' || identity.status === 'waiting'"
                  :href="route('user.identities.edit', identity.id)"
                  class="text-main-1 dark:text-main-1 hover:underline"
                  :aria-label="$t('Edit identity')"
                >
                  {{ $t('Edit') }}
                </Link>
                <button
                  @click="deleteIdentity(identity.id)"
                  class="text-secondary-3 dark:text-secondary-3 hover:underline"
                  :aria-label="$t('Delete identity')"
                >
                  {{ $t('Delete') }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Diseño de tabla para pantallas grandes -->
        <div class="hidden sm:block overflow-x-auto">
          <table class="min-w-full bg-neutral-0 dark:bg-neutral-2 border border-neutral-4 dark:border-neutral-2 rounded-lg shadow-sm" aria-label="Identities table">
            <thead class="bg-main-0 dark:bg-main-0" style="border-bottom: 4px solid #FFA07A;">
              <tr>
                <th class="p-3 text-left text-sm font-medium text-neutral-0 dark:text-neutral-0">{{ $t('Identity type') }}</th>
                <th class="p-3 text-left text-sm font-medium text-neutral-0 dark:text-neutral-0">{{ $t('Identity name') }}</th>
                <th class="p-3 text-left text-sm font-medium text-neutral-0 dark:text-neutral-0">{{ $t('Email') }}</th>
                <th class="p-3 text-left text-sm font-medium text-neutral-0 dark:text-neutral-0">{{ $t('Phone') }}</th>
                <th class="p-3 text-left text-sm font-medium text-neutral-0 dark:text-neutral-0">{{ $t('Status') }}</th>
                <th class="p-3 text-left text-sm font-medium text-neutral-0 dark:text-neutral-0">{{ $t('Handled By') }}</th>
                <th class="p-3 text-left text-sm font-medium text-neutral-0 dark:text-neutral-0">{{ $t('Options') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="identity in identities.data"
                :key="identity.id"
                class="border-t border-neutral-4 dark:border-neutral-2 hover:bg-neutral-3 dark:hover:bg-neutral-1"
              >
                <td class="p-3 text-neutral-2 dark:text-neutral-0">{{ identity.role_name }}</td>
                <td class="p-3 text-neutral-2 dark:text-neutral-0">{{ identity.name }}</td>
                <td class="p-3 text-neutral-2 dark:text-neutral-0">{{ identity.email }}</td>
                <td class="p-3 text-neutral-2 dark:text-neutral-0">{{ identity.phone || $t('na') }}</td>
                <td class="p-3">
                  <span :class="statusClass(identity.status)">
                    {{ $t(identity.status || 'unknown') }}
                    <span v-if="identity.has_unseen_requests" class="ml-2">🔔</span>
                  </span>
                </td>
                <td class="p-3 text-neutral-2 dark:text-neutral-0">
                  {{ identity.handled_by ? identity.handled_by.name : $t('Not assigned') }}
                </td>
                <td class="p-3 flex gap-2">
                  <Link
                    v-if="userRole === 'invitado' && (identity.status === 'pending' || identity.status === 'in_progress' || identity.status === 'waiting')"
                    :href="route('user.identities.edit', identity.id)"
                    class="text-main-1 dark:text-main-1 hover:underline"
                    :aria-label="$t('Edit identity')"
                  >
                    {{ $t('Edit') }}
                  </Link>
                  <button
                    v-if="userRole === 'invitado'"
                    @click="deleteIdentity(identity.id)"
                    class="text-secondary-3 dark:text-secondary-3 hover:underline"
                    :aria-label="$t('Delete identity')"
                  >
                    {{ $t('Delete') }}
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Paginación -->
        <div v-if="identities.links.length > 3" class="mt-6 flex justify-center gap-2">
          <Link
            v-for="(link, index) in identities.links"
            :key="index"
            :href="link.url || '#'"
            v-html="link.label"
            :class="[
              'px-4 py-2 bg-main-0 dark:bg-main-0 text-neutral-0 dark:text-neutral-0 rounded-lg',
              {
                'bg-main-1 dark:bg-main-1': link.active,
                'hover:bg-main-1 dark:hover:bg-main-1': link.url && !link.active,
                'opacity-50 cursor-not-allowed': !link.url,
              },
            ]"
            :aria-label="link.label.includes('Previous') ? $t('Previous page') : link.label.includes('Next') ? $t('Next page') : $t('Page') + ' ' + link.label"
            :disabled="!link.url"
          />
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
table {
  border-collapse: collapse;
  border-spacing: 0;
  border-radius: 0.5rem;
  overflow: hidden;
}
</style>