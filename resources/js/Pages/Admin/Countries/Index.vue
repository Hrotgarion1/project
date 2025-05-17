<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import { ref, onMounted, getCurrentInstance } from "vue";
import { toast } from 'vue3-toastify';
import alerts from "@/utils/alerts";

const instance = getCurrentInstance();
const $t = instance?.proxy.$t;

const props = defineProps({
    paises: { type: Object, required: true },
});

// Mantener la lista de países en un ref
const paises = ref(props.paises);
const loading = ref(false);

const deletePais = async (id) => {
    const result = await alerts.confirmDelete({ t: $t });
    if (result.isConfirmed) {
        router.delete(route("paises.destroy", id), {
            onSuccess: () => {
                toast.success($t("Country deleted successfully"));
                // Actualizar la lista de países
                paises.value.data = paises.value.data.filter(pais => pais.id !== id);
            },
            onError: (error) => toast.error($t("Error deleting country")),
        });
    }
};
</script>

<template>
    <AppLayout :title="$t('Countries List')">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $t('Countries List') }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="mb-4">
                            <Link :href="route('paises.create')" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                {{ $t('Create Country') }}
                            </Link>
                        </div>

                        <div v-if="paises.data.length > 0">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('Name') }}</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="pais in paises.data" :key="pais.id">
                                        <td class="px-6 py-4 whitespace-nowrap">{{ pais.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <Link :href="route('paises.edit', pais.id)" class="text-indigo-600 hover:text-indigo-900 mr-2">{{ $t('Edit') }}</Link>
                                            <button @click="deletePais(pais.id)" class="text-red-600 hover:text-red-900">{{ $t('Delete') }}</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="text-center py-4">{{ $t('No countries found') }}</div>

                        <div class="mt-4" v-if="paises.links && paises.links.length > 3">
                            <div class="flex justify-center">
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                    <template v-for="link in paises.links" :key="link.label">
                                        <div v-if="link.url === null" class="px-2 py-2 border text-gray-500 cursor-default" v-html="link.label"></div>
                                        <Link v-else :href="link.url" class="px-4 py-2 border" :class="{'bg-blue-500 text-white': link.active, 'text-gray-700 hover:bg-gray-200': !link.active}" v-html="link.label"></Link>
                                    </template>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>