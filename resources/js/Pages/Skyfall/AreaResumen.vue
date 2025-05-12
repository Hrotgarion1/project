<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import GoBackButton from '@/Components/Common/GoBackButton.vue';
import { ref, watch, getCurrentInstance, computed, onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { useAreaGlobalStore } from '@/stores/areaGlobalStore';

console.debug('AreaResumen cargado');

const instance = getCurrentInstance();
const $t = instance?.proxy.$t ?? ((key) => key);

const props = defineProps({
    resumen: {
        type: Object,
        required: true,
    },
});

const globalStore = useAreaGlobalStore();
const sortKey = ref('belonging_name');
const sortOrder = ref('asc');
const perPage = ref(props.resumen.per_page || 3);

const perPageOptions = [
    { value: 3, label: '3' },
    { value: 5, label: '5' },
    { value: 10, label: '10' },
    { value: 20, label: '20' },
];

const columns = computed(() => [
    { key: 'pais_name', label: $t('Country') },
    { key: 'belonging_name', label: $t('belonging') },
    { key: 'total_propuestos', label: $t('proposed') },
    { key: 'total_verificados', label: $t('verified') },
    { key: 'totales', label: $t('total') },
]);

onMounted(() => {
    globalStore.fetchGlobalData();
});

const sort = (key) => {
    if (sortKey.value === key) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortKey.value = key;
        sortOrder.value = 'asc';
    }
    console.debug('Ordenando por:', sortKey.value, 'orden:', sortOrder.value);
    router.get(
        route('skyfall.area-resumen'),
        { sort_by: sortKey.value, sort_order: sortOrder.value, per_page: perPage.value },
        { preserveState: true, preserveScroll: true }
    );
};

const changePage = (page) => {
    console.debug('Cambiando a página:', page, 'per_page:', perPage.value);
    router.get(
        route('skyfall.area-resumen'),
        { page, per_page: perPage.value, sort_by: sortKey.value, sort_order: sortOrder.value },
        { preserveState: true, preserveScroll: true }
    );
};

watch(perPage, (newPerPage) => {
    console.debug('perPage cambiado a:', newPerPage);
    changePage(1);
});

const formatNumber = (value) => {
    const numericValue = Number(value);
    if (isNaN(numericValue)) return $t('na');
    return numericValue.toLocaleString('es-ES', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
        useGrouping: true,
    });
};
</script>

<template>
    <AppLayout :title="$t('Area Resumen')">
        <div class="container mx-auto p-4 bg-neutral-3 dark:bg-neutral-1 min-h-screen">
            <div class="flex items-center justify-between gap-2 mb-6 bg-main-0 dark:bg-main-0 p-4 rounded-lg">
                <div class="flex items-center gap-2">
                    <GoBackButton />
                    <h1 class="text-xl font-bold text-neutral-0 dark:text-neutral-0">
                        {{ $t('Area Resumen') }}
                    </h1>
                </div>
                <div class="flex items-center gap-4 text-sm text-neutral-0 dark:text-neutral-0">
                    <span>
                        {{ $t('proposed') }}: <span class="text-secondary-2">{{ formatNumber(globalStore.sumas.total_propuestos) }}</span>
                    </span>
                    <span>
                        {{ $t('verified') }}: <span class="text-secondary-1">{{ formatNumber(globalStore.sumas.total_verificados) }}</span>
                    </span>
                    <span>
                        {{ $t('total') }}: <span class="text-main-1">{{ formatNumber(globalStore.sumas.totales) }}</span>
                    </span>
                </div>
            </div>
            <div v-if="!resumen.data.length" class="text-center text-neutral-2 dark:text-neutral-0">
                {{ $t('no_records') }}
            </div>
            <div v-else>
                <!-- Dropdown para ordenar en móviles -->
                <div class="sm:hidden mb-4 flex items-center gap-2">
                    <label class="text-sm text-neutral-1 dark:text-neutral-0">{{ $t('sort_by') }}</label>
                    <select
                        v-model="sortKey"
                        @change="sort(sortKey)"
                        class="appearance-none border border-neutral-4 dark:border-neutral-2 rounded px-2 py-1 pr-8 text-neutral-2 dark:text-neutral-0 bg-neutral-0 dark:bg-neutral-2 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNiIgaGVpZ2h0PSIxNiIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9IiM4QkMzNEEiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIj48cG9seWxpbmUgcG9pbnRzPSI2IDkgMTIgMTUgMTggOSIvPjwvc3ZnPg==')] bg-no-repeat bg-[right_0.5rem_center] bg-[length:16px_16px]"
                    >
                        <option v-for="col in columns" :key="col.key" :value="col.key">
                            {{ col.label }}
                        </option>
                    </select>
                    <button
                        @click="sortOrder = sortOrder === 'asc' ? 'desc' : 'asc'; sort(sortKey)"
                        class="text-sm text-secondary-0 dark:text-secondary-0"
                    >
                        {{ sortOrder === 'asc' ? '↑' : '↓' }}
                    </button>
                </div>

                <!-- Diseño de tarjetas para pantallas pequeñas -->
                <div class="block sm:hidden space-y-4">
                    <div
                        v-for="item in resumen.data"
                        :key="`${item.belonging_id}-${item.pais_id}`"
                        class="bg-neutral-0 dark:bg-neutral-2 border border-neutral-4 dark:border-neutral-2 rounded-lg shadow-sm"
                    >
                        <div class="bg-main-0 dark:bg-main-0 px-4 py-2 flex justify-between items-center rounded-t-lg">
                            <Link
                                :href="route('skyfall.belonging.detail', item.belonging_id)"
                                class="text-neutral-0 dark:text-neutral-0 hover:underline font-semibold text-lg"
                            >
                                {{ item.belonging_name }}
                            </Link>
                            <span class="text-sm font-medium text-neutral-0 dark:text-neutral-0">
                                {{ item.pais_name }}
                            </span>
                        </div>
                        <div class="border-b-4 border-secondary-3"></div>
                        <div class="p-4">
                            <div class="grid grid-cols-2 gap-2 text-sm text-neutral-2 dark:text-neutral-0">
                                <div>
                                    <span class="font-medium text-neutral-1 dark:text-neutral-0">{{ $t('proposed') }}:</span>
                                    <span class="ml-2 text-secondary-2">{{ formatNumber(item.total_propuestos) }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-neutral-1 dark:text-neutral-0">{{ $t('verified') }}:</span>
                                    <span class="ml-2 text-secondary-1">{{ formatNumber(item.total_verificados) }}</span>
                                </div>
                                <div class="col-span-2">
                                    <span class="font-medium text-neutral-1 dark:text-neutral-0">{{ $t('total') }}:</span>
                                    <span class="ml-2 text-lg font-semibold text-main-1 dark:text-main-1">{{ formatNumber(item.totales) }}</span>
                                </div>
                            </div>
                            <div class="mt-3 flex justify-end">
                                <Link
                                    :href="route('skyfall.belonging.detail', item.belonging_id)"
                                    class="text-main-1 dark:text-main-1 hover:underline text-sm"
                                >
                                    {{ $t('view_details') }}
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Diseño de tabla para pantallas grandes -->
                <div class="hidden sm:block overflow-x-auto">
                    <table class="min-w-full bg-neutral-0 dark:bg-neutral-2 border border-neutral-4 dark:border-neutral-2 rounded-lg shadow-sm">
                        <thead class="bg-main-0 dark:bg-main-0" style="border-bottom: 4px solid #FFA07A;">
                            <tr>
                                <th
                                    v-for="col in columns"
                                    :key="col.key"
                                    class="p-3 text-left text-sm font-medium text-neutral-0 dark:text-neutral-0 cursor-pointer hover:bg-main-1 dark:hover:bg-main-1"
                                    @click="sort(col.key)"
                                >
                                    {{ col.label }}
                                    <span v-if="sortKey === col.key" class="text-secondary-0">
                                        {{ sortOrder === 'asc' ? '↑' : '↓' }}
                                    </span>
                                </th>
                                <th class="p-3 text-left text-sm font-medium text-neutral-0 dark:text-neutral-0">
                                    {{ $t('details') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="item in resumen.data"
                                :key="`${item.belonging_id}-${item.pais_id}`"
                                class="border-t border-neutral-4 dark:border-neutral-2 hover:bg-neutral-3 dark:hover:bg-neutral-1 text-neutral-2 dark:text-neutral-0"
                            >
                                <td class="p-3">{{ item.pais_name }}</td>
                                <td class="p-3">{{ item.belonging_name }}</td>
                                <td class="p-3 text-secondary-2">{{ formatNumber(item.total_propuestos) }}</td>
                                <td class="p-3 text-secondary-1">{{ formatNumber(item.total_verificados) }}</td>
                                <td class="p-3 text-main-1">{{ formatNumber(item.totales) }}</td>
                                <td class="p-3">
                                    <Link
                                        :href="route('skyfall.belonging.detail', item.belonging_id)"
                                        class="text-main-1 dark:text-main-1 hover:underline"
                                    >
                                        {{ $t('view_details') }}
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Controles de paginación -->
                <div class="mt-6 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2">
                            <label class="text-sm text-neutral-1 dark:text-neutral-0">{{ $t('per_page') }}</label>
                            <select
                                v-model="perPage"
                                class="appearance-none border border-neutral-4 dark:border-neutral-2 rounded px-2 py-1 pr-8 text-neutral-2 dark:text-neutral-0 bg-neutral-0 dark:bg-neutral-2 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNiIgaGVpZ2h0PSIxNiIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9IiM4QkMzNEEiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIj48cG9seWxpbmUgcG9pbnRzPSI2IDkgMTIgMTUgMTggOSIvPjwvc3ZnPg==')] bg-no-repeat bg-[right_0.5rem_center] bg-[length:16px_16px]"
                            >
                                <option v-for="option in perPageOptions" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                        </div>
                        <div class="text-sm text-neutral-2 dark:text-neutral-0">
                            {{ $t('showing') }} {{ perPage * (resumen.current_page - 1) + 1 }}
                            {{ $t('to') }}
                            {{ Math.min(perPage * resumen.current_page, resumen.total) }}
                            {{ $t('of') }} {{ resumen.total }} {{ $t('records') }}
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button
                            :disabled="resumen.current_page === 1"
                            @click="changePage(resumen.current_page - 1)"
                            class="px-4 py-2 bg-main-0 dark:bg-main-0 text-neutral-0 dark:text-neutral-0 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-main-1 dark:hover:bg-main-1"
                        >
                            {{ $t('Previous') }}
                        </button>
                        <button
                            :disabled="resumen.current_page === resumen.last_page"
                            @click="changePage(resumen.current_page + 1)"
                            class="px-4 py-2 bg-main-0 dark:bg-main-0 text-neutral-0 dark:text-neutral-0 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-main-1 dark:hover:bg-main-1"
                        >
                            {{ $t('Next') }}
                        </button>
                    </div>
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

@media (max-width: 640px) {
    .overflow-x-auto {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
}
</style>