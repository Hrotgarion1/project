<script setup>
import { useForm } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';
import AppLayout from '@/Layouts/AppLayout.vue';
import GoBackButton from '@/Components/Common/GoBackButton.vue';
import { ref, getCurrentInstance } from "vue";
import UserForm from '@/Components/Users/Form.vue';
import alerts from '@/utils/alerts';

const instance = getCurrentInstance();
const $t = instance?.proxy.$t;

const props = defineProps({
    roles: { type: Array, required: true },
    paises: { type: Array, required: true },
});

const form = useForm({
    name: '',
    email: '',
    phone: '',
    address: '',
    zip: '',
    pais_id: null,
    roles: [],
});

const submit = async () => {
    const result = await alerts.confirmCreateUser($t);
    if (result.isConfirmed) {
        form.post(route('users.store'), {
            onSuccess: () => {
                toast.success($t('User created successfully'));
                alerts.success($t, 'User created successfully');
            },
            onError: (errors) => {
                toast.error($t('Error creating user'));
                alerts.error($t, 'Error creating user');
                console.log('Errores:', errors);
            },
        });
    }
};
</script>

<template>
    <AppLayout :title="$t('Create User')">
        <template #header>
            <div class="flex items-center space-x-2">
                <GoBackButton />
                <h1 class="font-semibold text-xl text-gray-800 leading-tight">{{ $t('Create User') }}</h1>
            </div>
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <UserForm :form="form" :roles="props.roles" :paises="props.paises" @submit="submit" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>