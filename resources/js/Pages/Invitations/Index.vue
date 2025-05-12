<script setup>
import { ref, getCurrentInstance } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import HeaderSection from '@/Components/Common/HeaderSection.vue';

const instance = getCurrentInstance();
const $t = instance?.proxy.$t;

const props = defineProps({
    sentInvitations: Array,
    receivedInvitations: Array,
});
</script>

<template>
    <AppLayout :title="$t('Invitations')">
        <div class="container mx-auto p-4 bg-neutral-3 dark:bg-neutral-1 min-h-screen">
            <HeaderSection
                :title="$t('Invitations')"
                :show-back-button="true"
            />

            <!-- Invitaciones Enviadas -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold mb-4 text-neutral-1 dark:text-neutral-0">{{ $t('Sent Invitations') }}</h2>
                
                <!-- Diseño de tarjetas para pantallas pequeñas -->
                <div v-if="sentInvitations.length" class="block sm:hidden space-y-4">
                    <div
                        v-for="invitation in sentInvitations"
                        :key="invitation.id"
                        class="bg-neutral-0 dark:bg-neutral-2 border border-neutral-4 dark:border-neutral-2 rounded-lg shadow-sm"
                    >
                        <div class="bg-main-0 dark:bg-main-0 px-4 py-2 rounded-t-lg">
                            <h3 class="text-neutral-0 dark:text-neutral-0 font-semibold">
                                {{ invitation.invitado.name }}
                            </h3>
                        </div>
                        <div class="border-b-4 border-secondary-3"></div>
                        <div class="p-4 text-sm text-neutral-2 dark:text-neutral-0">
                            <p><span class="font-medium text-neutral-1 dark:text-neutral-0">{{ $t('Identity') }}:</span> {{ invitation.identity.name }} ({{ invitation.identity.type }})</p>
                            <p><span class="font-medium text-neutral-1 dark:text-neutral-0">{{ $t('Role') }}:</span> {{ invitation.role_name }}</p>
                            <p><span class="font-medium text-neutral-1 dark:text-neutral-0">{{ $t('Status') }}:</span> {{ $t(invitation.status) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Diseño de tabla para pantallas grandes -->
                <div v-if="sentInvitations.length" class="hidden sm:block overflow-x-auto">
                    <table class="min-w-full bg-neutral-0 dark:bg-neutral-2 border border-neutral-4 dark:border-neutral-2 rounded-lg shadow-sm" aria-label="Sent invitations table">
                        <thead class="bg-main-0 dark:bg-main-0" style="border-bottom: 4px solid #FFA07A;">
                            <tr>
                                <th class="p-3 text-left text-sm font-medium text-neutral-0 dark:text-neutral-0">{{ $t('Invited') }}</th>
                                <th class="p-3 text-left text-sm font-medium text-neutral-0 dark:text-neutral-0">{{ $t('Identity') }}</th>
                                <th class="p-3 text-left text-sm font-medium text-neutral-0 dark:text-neutral-0">{{ $t('Role') }}</th>
                                <th class="p-3 text-left text-sm font-medium text-neutral-0 dark:text-neutral-0">{{ $t('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="invitation in sentInvitations"
                                :key="invitation.id"
                                class="border-t border-neutral-4 dark:border-neutral-2 hover:bg-neutral-3 dark:hover:bg-neutral-1"
                            >
                                <td class="p-3 text-neutral-2 dark:text-neutral-0">{{ invitation.invitado.name }}</td>
                                <td class="p-3 text-neutral-2 dark:text-neutral-0">{{ invitation.identity.name }} ({{ invitation.identity.type }})</td>
                                <td class="p-3 text-neutral-2 dark:text-neutral-0">{{ invitation.role_name }}</td>
                                <td class="p-3 text-neutral-2 dark:text-neutral-0">{{ $t(invitation.status) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="!sentInvitations.length" class="text-center text-neutral-2 dark:text-neutral-0">
                    {{ $t('No sent invitations') }}
                </div>
            </div>

            <!-- Invitaciones Recibidas -->
            <div>
                <h2 class="text-lg font-semibold mb-4 text-neutral-1 dark:text-neutral-0">{{ $t('Received Invitations') }}</h2>
                
                <!-- Diseño de tarjetas para pantallas pequeñas -->
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
                            <p><span class="font-medium text-neutral-1 dark:text-neutral-0">{{ $t('Identity') }}:</span> {{ invitation.identity.name }} ({{ invitation.identity.type }})</p>
                            <p><span class="font-medium text-neutral-1 dark:text-neutral-0">{{ $t('Role') }}:</span> {{ invitation.role_name }}</p>
                            <p><span class="font-medium text-neutral-1 dark:text-neutral-0">{{ $t('Status') }}:</span> {{ $t(invitation.status) }}</p>
                            <div v-if="invitation.status === 'pending'" class="mt-3 flex gap-2">
                                <a
                                    :href="route('invitations.accept', invitation.token)"
                                    class="text-main-1 dark:text-main-1 hover:underline"
                                    :aria-label="$t('Accept invitation')"
                                >
                                    {{ $t('Accept') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Diseño de tabla para pantallas grandes -->
                <div v-if="receivedInvitations.length" class="hidden sm:block overflow-x-auto">
                    <table class="min-w-full bg-neutral-0 dark:bg-neutral-2 border border-neutral-4 dark:border-neutral-2 rounded-lg shadow-sm" aria-label="Received invitations table">
                        <thead class="bg-main-0 dark:bg-main-0" style="border-bottom: 4px solid #FFA07A;">
                            <tr>
                                <th class="p-3 text-left text-sm font-medium text-neutral-0 dark:text-neutral-0">{{ $t('Inviter') }}</th>
                                <th class="p-3 text-left text-sm font-medium text-neutral-0 dark:text-neutral-0">{{ $t('Identity') }}</th>
                                <th class="p-3 text-left text-sm font-medium text-neutral-0 dark:text-neutral-0">{{ $t('Role') }}</th>
                                <th class="p-3 text-left text-sm font-medium text-neutral-0 dark:text-neutral-0">{{ $t('Status') }}</th>
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
                                <td class="p-3 text-neutral-2 dark:text-neutral-0">{{ invitation.identity.name }} ({{ invitation.identity.type }})</td>
                                <td class="p-3 text-neutral-2 dark:text-neutral-0">{{ invitation.role_name }}</td>
                                <td class="p-3 text-neutral-2 dark:text-neutral-0">{{ $t(invitation.status) }}</td>
                                <td class="p-3 flex gap-2">
                                    <a
                                        v-if="invitation.status === 'pending'"
                                        :href="route('invitations.accept', invitation.token)"
                                        class="text-main-1 dark:text-main-1 hover:underline"
                                        :aria-label="$t('Accept invitation')"
                                    >
                                        {{ $t('Accept') }}
                                    </a>
                                    <a
                                        v-if="invitation.status === 'pending'"
                                        :href="route('invitations.cancel', id)"
                                        class="text-secondary-3 dark:text-secondary-3 hover:underline"
                                        :aria-label="$t('Cancel invitation')"
                                    >
                                        {{ $t('Cancel') }}
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="!receivedInvitations.length" class="text-center text-neutral-2 dark:text-neutral-0">
                    {{ $t('No received invitations') }}
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