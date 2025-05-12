<script setup>
import { ref, getCurrentInstance } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import HeaderSection from '@/Components/Common/HeaderSection.vue';
import { useInvitationStore } from '@/stores/invitations';
import alerts from '@/utils/alerts';

// Obtener $t para i18n
const instance = getCurrentInstance();
const $t = instance?.proxy.$t ?? ((key) => key);

const invitationStore = useInvitationStore();

// Estado del formulario
const form = ref({
    email: '',
    role: '',
});

// Props desde Inertia
const props = defineProps({
    identity: {
        type: Object,
        required: true,
    },
    availableRoles: {
        type: Array,
        required: true,
    },
});

// Enviar la invitación con confirmación
const enviarInvitacion = async () => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(form.value.email)) {
        alerts.error($t, $t('Invalid email format'));
        return;
    }
    const result = await alerts.confirmSendInvitation($t);
    if (result.isConfirmed) {
        await invitationStore.sendInvitation(form.value.email, props.identity.id, form.value.role);
        if (!invitationStore.error) {
            alerts.success($t, 'Invitation sent successfully');
            form.value.email = '';
            form.value.role = '';
        } else {
            alerts.error($t, invitationStore.error);
        }
    }
};
</script>

<template>
    <AppLayout :title="$t('Send Invitation')">
        <div class="container mx-auto p-4 bg-neutral-3 dark:bg-neutral-1 min-h-screen">
            <HeaderSection
                :title="$t('Send Invitation') + ' - ' + identity.name"
                :show-back-button="true"
            />

            <div class="bg-neutral-0 dark:bg-neutral-2 border border-neutral-4 dark:border-neutral-2 rounded-lg shadow-sm p-6">
                <form @submit.prevent="enviarInvitacion">
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-neutral-1 dark:text-neutral-0 mb-2">
                            {{ $t('Guest Email') }}
                        </label>
                        <input
                            type="email"
                            id="email"
                            v-model="form.email"
                            required
                            class="appearance-none border border-neutral-4 dark:border-neutral-2 rounded px-3 py-2 w-full text-neutral-2 dark:text-neutral-0 bg-neutral-0 dark:bg-neutral-2 focus:outline-none focus:ring-2 focus:ring-main-1"
                            :placeholder="$t('E.g.: guest@example.com')"
                            :aria-label="$t('Guest Email')"
                        />
                    </div>

                    <div class="mb-4">
                        <label for="role" class="block text-sm font-medium text-neutral-1 dark:text-neutral-0 mb-2">
                            {{ $t('Select Role') }}
                        </label>
                        <select
                            id="role"
                            v-model="form.role"
                            required
                            class="appearance-none border border-neutral-4 dark:border-neutral-2 rounded px-3 py-2 w-full text-neutral-2 dark:text-neutral-0 bg-neutral-0 dark:bg-neutral-2 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNiIgaGVpZ2h0PSIxNiIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9IiM4QkMzNEEiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIj48cG9seWxpbmUgcG9pbnRzPSI2IDkgMTIgMTUgMTggOSIvPjwvc3ZnPg==')] bg-no-repeat bg-[right_0.5rem_center] bg-[length:16px_16px] focus:outline-none focus:ring-2 focus:ring-main-1"
                            :aria-label="$t('Select Role')"
                        >
                            <option value="" disabled>{{ $t('Choose a role') }}</option>
                            <option v-for="role in availableRoles" :key="role.type" :value="role.type">
                                {{ role.name }}
                            </option>
                        </select>
                    </div>

                    <div class="flex items-center justify-end">
                        <button
                            class="px-4 py-2 bg-main-1 dark:bg-main-1 text-neutral-0 dark:text-neutral-0 rounded-lg hover:bg-main-0 dark:hover:bg-main-0 transition-colors duration-200"
                            type="submit"
                            :disabled="invitationStore.loading"
                            :aria-label="$t('Send Invitation')"
                        >
                            {{ invitationStore.loading ? $t('Sending...') : $t('Send Invitation') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Asegura que el formulario tenga bordes redondeados */
form {
    border-radius: 0.5rem;
    overflow: hidden;
}
</style>