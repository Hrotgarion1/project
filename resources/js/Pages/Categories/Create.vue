<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import GoBackButton from "@/Components/Common/GoBackButton.vue";
import { useForm, Link, router, usePage } from "@inertiajs/vue3";
import { ref, onMounted, getCurrentInstance } from "vue";
import { toast } from 'vue3-toastify';
import alerts from "@/utils/alerts";

const instance = getCurrentInstance();
const $t = instance?.proxy.$t; // Para i18n

const props = defineProps({
    paises: { type: Array, required: true },
});

const form = useForm({
    name: '',
    value: null,
    pais_id: null,
});

const submit = async () => {
    const result = await alerts.confirmCreate($t);
    if (result.isConfirmed) {
        form.post(route('categories.store'), {
            onSuccess: () => {
                toast.success($t('Category created successfully'));
                router.visit(route('categories.index'));
            },
            onError: (errors) => {
                toast.error($t('Error creating category'));
            },
        });
    }
};
</script>

<template>
    <AppLayout :title="$t('Create Category')">
        <template #header>
            <div class="flex items-center space-x-2">
                <GoBackButton />
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $t('Create Category') }}
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submit">
                            <div>
                                <label for="name" class="block font-medium text-sm text-gray-700">{{ $t('Name') }}</label>
                                <input type="text" id="name" v-model="form.name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required />
                                <div v-if="form.errors.name" class="text-sm text-red-600">{{ form.errors.name }}</div>
                            </div>

                            <div class="mt-4">
                                <label for="value" class="block font-medium text-sm text-gray-700">{{ $t('Value') }}</label>
                                <input type="number" id="value" v-model="form.value" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" />
                                <div v-if="form.errors.value" class="text-sm text-red-600">{{ form.errors.value }}</div>
                            </div>

                            <div class="mt-4">
                                <label for="pais_id" class="block font-medium text-sm text-gray-700">{{ $t('Country') }}</label>
                                <select id="pais_id" v-model="form.pais_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option :value="null">{{ $t('Select a country') }}</option>
                                    <option v-for="pais in paises" :key="pais.id" :value="pais.id">{{ pais.name }}</option>
                                </select>
                                <div v-if="form.errors.pais_id" class="text-sm text-red-600">{{ form.errors.pais_id }}</div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    {{ $t('Create') }}
                                </button>
                                <Link :href="route('categories.index')" class="inline-block bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded ml-2">
                                    {{ $t('Cancel') }}
                                </Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>