<script setup>
import { ref, getCurrentInstance } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import HeaderSection from '@/Components/Common/HeaderSection.vue';
import { router } from '@inertiajs/vue3';
import alerts from '@/utils/alerts';

const instance = getCurrentInstance();
const $t = instance?.proxy.$t ?? ((key) => key);

const props = defineProps({
    receivedInvitations: Array,
});

const acceptInvitation = async (token) => {
    const result = await alerts.confirmAction({ t: $t }, 'accept invitation');
    if (result.isConfirmed) {
        router.post(route('invitations.accept', token), {}, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                alerts.success($t, 'Invitation accepted successfully');
            },
            onError: (errors) => {
                alerts.error($t, errors.message || 'Error accepting invitation');
            },
        });
    }
};

const cancelInvitation = async (id) => {
    const result = await alerts.confirmDelete({ t: $t });
    if (result.isConfirmed) {
        router.delete(route('invitations.cancel', id), {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                alerts.success($t, 'Invitation cancelled successfully');
            },
            onError: (errors) => {
                alerts.error($t, errors.message || 'Error cancelling invitation');
            },
        });
    }
};
</script>

<template>
    <AppLayout :title="$t('Received Invitations')">
        <div class="container mx-auto p-4 bg-neutral-3 dark:bg-neutral-1 min-h-screen">
            <HeaderSection
                :title="$t('Received Invitations')"
                :show-back-button="true"
            />

            <div v-if="receivedInvitations.length" class="block sm:hidden space-y-4">
                <div
                    v-for="invitation in receivedInvitations"
                    :key="invitation.id"
                    class="bg-neutral-0 dark:bg-neutral-2 border border-neutral-4 dark:border-neutral-2 rounded-lg shadow-sm"
                >
                    <div class="bg-main-0 dark:bg-main-0 px-4 py-2 rounded-t-lg">
                        <h3 class="text-neutral-0 dark:text-neutral-0 font-semibold">
                            {{ invitation.invitador.name }}
                        </h3>
                    </div>
                    <div class="border-b-4 border-secondary-3"></div>
                    <div class="p-4 text-sm text-neutral-2 dark:text-neutral-0">
                        <p><span class="font-medium text-neutral-1 dark:text-neutral-0">{{ $t('Identity') }}:</span> {{ invitation.identity.name }} ({{ invitation.identity.type_name }})</p>
                        <p><span class="font-medium text-neutral-1 dark:text-neutral-0">{{ $t('Role') }}:</span> <span class="text-main-1">{{ invitation.role_name }}</span></p>
                        <p><span class="font-medium text-neutral-1 dark:text-neutral-0">{{ $t('Status') }}:</span> <span :class="invitation.status === 'approved' ? 'text-secondary-1' : 'text-neutral-2'">{{ $t(invitation.status) }}</span></p>
                        <div class="mt-3 flex gap-2">
                            <button
                                v-if="invitation.status === 'pending'"
                                @click="acceptInvitation(invitation.token)"
                                class="text-main-1 dark:text-main-1 hover:underline"
                                :aria-label="$t('Accept invitation')"
                            >
                                {{ $t('Accept') }}
                            </button>
                            <button
                                v-if="invitation.status === 'pending' || invitation.status === 'approved'"
                                @click="cancelInvitation(invitation.id)"
                                class="text-secondary-3 dark:text-secondary-3 hover:underline"
                                :aria-label="$t('Cancel invitation')"
                            >
                                {{ $t('Cancel') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="receivedInvitations.length" class="hidden sm:block overflow-x-auto">
                <table class="min-w-full bg-neutral-0 dark:bg-neutral-2 border border-neutral-4 dark:border-neutral-2 rounded-lg shadow-sm" aria-label="Received invitations table">
                    <thead class="bg-main-0 dark:bg-main-0" style="border-bottom: 4px solid #FFA07A;">
                        <tr>
                            <th class="p-3 text-left text-sm font-medium text-neutral-0 dark:text-neutral-0">{{ $t('Inviter name') }}</th>
                            <th class="p-3 text-left text-sm font-medium text-neutral-0 dark:text-neutral-0">{{ $t('Identity') }}</th>
                            <th class="p-3 text-left text-sm font-medium text-neutral-0 dark:text-neutral-0">{{ $t('Assigned role') }}</th>
                            <th class="p-3 text-left text-sm font-medium text-neutral-0 dark:text-neutral-0">{{ $t('Invitation Status') }}</th>
                            <th class="p-3 text-left text-sm font-medium text-neutral-0 dark:text-neutral-0">{{ $t('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="invitation in receivedInvitations"
                            :key="invitation.id"
                            class="border-t border-neutral-4 dark:border-neutral-2 hover:bg-neutral-3 dark:hover:bg-neutral-1"
                        >
                            <td class="p-3 text-neutral-2 dark:text-neutral-0">{{ invitation.invitador.name }}</td>
                            <td class="p-3 text-neutral-2 dark:text-neutral-0">{{ invitation.identity.name }}</td>
                            <td class="p-3 text-main-1 dark:text-main-1">{{ invitation.role_name }}</td>
                            <td class="p-3" :class="invitation.status === 'approved' ? 'text-secondary-1 dark:text-secondary-1' : 'text-neutral-2 dark:text-neutral-0'">{{ $t(invitation.status) }}</td>
                            <td class="p-3 flex gap-2">
                                <button
                                    v-if="invitation.status === 'pending'"
                                    @click="acceptInvitation(invitation.token)"
                                    class="text-main-1 dark:text-main-1 hover:underline"
                                    :aria-label="$t('Accept invitation')"
                                >
                                    {{ $t('Accept') }}
                                </button>
                                <button
                                    v-if="invitation.status === 'pending' || invitation.status === 'approved'"
                                    @click="cancelInvitation(invitation.id)"
                                    class="text-secondary-3 dark:text-secondary-3 hover:underline"
                                    :aria-label="$t('Cancel invitation')"
                                >
                                    {{ $t('Cancel') }}
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="!receivedInvitations.length" class="text-center text-neutral-2 dark:text-neutral-0">
                {{ $t('No received invitations') }}
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