<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import GoBackButton from "@/Components/Common/GoBackButton.vue";
import { useForm } from '@inertiajs/vue3';
import UserForm from '@/Components/Users/Form.vue';

const props = defineProps({
    user: { type: Object, required: true },
    roles: { type: Array, required: true },
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    phone: props.user.phone || '',
    address: props.user.address || '',
    zip: props.user.zip || '',
    pais_id: props.user.pais_id || null,
    roles: props.user.roles.map(role => role.name),
    password: '', // No obligatorio
});
</script>

<template>
    <AppLayout :title="$t('Edit User')">
        <template #header>
          <div class="flex items-center space-x-2">
            <GoBackButton />
                <h1 class="font-semibold text-xl text-gray-800 leading-tight">{{ $t('Edit User') }}</h1>
            </div>
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <UserForm :updating="true" :form="form" :roles="props.roles" @submit="form.put(route('users.update', user.id))" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>