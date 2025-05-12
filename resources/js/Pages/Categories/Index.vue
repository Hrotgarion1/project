<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import GoBackButton from "@/Components/Common/GoBackButton.vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import { ref, onMounted, getCurrentInstance } from "vue";
import { toast } from 'vue3-toastify';
import alerts from "@/utils/alerts";

const instance = getCurrentInstance();
const $t = instance?.proxy.$t; // Para i18n

const props = defineProps({
    categories: { type: Object, required: true },
});

// Mantener la lista de categorías en un ref
const categories = ref(props.categories);
const loading = ref(false);

const deleteCategory = async (id) => {
    const result = await alerts.confirmDelete({ t: $t });
    if (result.isConfirmed) {
        router.delete(route("categories.destroy", id), {
            onSuccess: () => {
                toast.success($t("Category deleted successfully"));
                // Actualizar la lista de categorías
                categories.value.data = categories.value.data.filter(category => category.id !== id);
            },
            onError: (error) => toast.error($t("Error deleting category")),
        });
    }
};
</script>

<template>
    <AppLayout :title="$t('Categories List')">
        <template #header>
            <div class="flex items-center space-x-2">
                <GoBackButton />
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $t('Categories List') }}
            </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="mb-4">
                            <Link :href="route('categories.create')" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                {{ $t('Create Category') }}
                            </Link>
                        </div>

                        <div v-if="categories.data.length > 0">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('Name') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('Value') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('Country') }}</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $t('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="category in categories.data" :key="category.id">
                                        <td class="px-6 py-4 whitespace-nowrap">{{ category.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ category.value }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ category.pais ? category.pais.name : $t('N/A') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <Link :href="route('categories.edit', category.id)" class="text-indigo-600 hover:text-indigo-900 mr-2">{{ $t('Edit') }}</Link>
                                            <button @click="deleteCategory(category.id)" class="text-red-600 hover:text-red-900">{{ $t('Delete') }}</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="text-center py-4">{{ $t('No categories found') }}</div>

                        <div class="mt-4" v-if="categories.links && categories.links.length > 3">
                            <div class="flex justify-center">
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                    <template v-for="link in categories.links" :key="link.label">
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