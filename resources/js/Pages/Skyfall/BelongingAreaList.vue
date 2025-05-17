<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import HeaderSection from '@/Components/Common/HeaderSection.vue';
import { ref, computed, getCurrentInstance } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';
import { useFormat } from '@/composables/useFormat';

console.debug('BelongingAreaList cargada');

const instance = getCurrentInstance();
const $t = instance?.proxy.$t ?? ((key) => key);
const { formatNumber, formatDate } = useFormat();

const props = defineProps({
    belonging_id: {
        type: [Number, String],
        required: true,
    },
    belonging_name: {
        type: String,
        required: true,
    },
    area_name: {
        type: String,
        default: 'Desconocida',
    },
    area_display_name: {
        type: String,
        default: 'Desconocida',
    },
    has_category: {
        type: Boolean,
        default: false,
    },
    records: {
        type: Object,
        default: () => ({ data: [], current_page: 1, last_page: 1, per_page: 10, total: 0 }),
    },
    totales: {
        type: Object,
        default: () => ({ total_propuestos: 0, total_verificados: 0, total: 0 }),
    },
    paises: {
        type: Array,
        default: () => [],
    },
});

console.debug('Props:', {
    belonging_id: props.belonging_id,
    belonging_name: props.belonging_name,
    area_name: props.area_name,
    area_display_name: props.area_display_name,
    has_category: props.has_category,
    records: props.records,
    totales: props.totales,
});

const sortOrder = ref('asc');
const sortColumn = ref('init_date');
const perPage = ref(props.records.per_page || 10);

const perPageOptions = [
    { value: 10, label: '10' },
    { value: 20, label: '20' },
    { value: 50, label: '50' },
];

const columns = computed(() => {
    const baseColumns = [
        { key: 'name', label: $t('name') },
        { key: 'init_date', label: $t('start_date') },
        { key: 'end_date', label: $t('end_date') },
        { key: 'value', label: $t('value') },
        { key: 'status', label: $t('status') },
    ];
    if (props.has_category) {
        return [{ key: 'category_name', label: $t('category') }, ...baseColumns];
    }
    return baseColumns;
});

const changePage = async (page) => {
    console.debug('Cambiando a página:', page, 'per_page:', perPage.value);
    try {
        await router.get(
            route('skyfall.belonging-area-records.index', { belonging_id: props.belonging_id, area_name: props.area_name }),
            { page, per_page: perPage.value },
            {
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    console.debug('Paginación exitosa, página:', page);
                },
                onError: (errors) => {
                    console.error('Error en paginación:', errors);
                    toast.error($t('pagination_error'), { timeout: 3000 });
                },
            }
        );
    } catch (error) {
        console.error('Error inesperado en paginación:', error);
        toast.error($t('unexpected_error'), { timeout: 3000 });
    }
};

const toggleSortOrder = (column) => {
    if (sortColumn.value === column) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortColumn.value = column;
        sortOrder.value = 'asc';
    }
    props.records.data.sort((a, b) => {
        const aValue = a[column] ?? '';
        const bValue = b[column] ?? '';
        return sortOrder.value === 'asc'
            ? aValue > bValue ? 1 : -1
            : aValue < bValue ? 1 : -1;
    });
};

const formatEndDate = (endDate, currently) => {
    if (currently === 'yes') return $t('current');
    return formatDate(endDate);
};

const formatStatus = (status) => {
    const statuses = {
        1 : { label: $t('proposed'), class: 'text-secondary-2' },
        2 : { label: $t('verified'), class: 'text-secondary-1' },
    };
    return statuses[status] || { label: $t('na'), class: 'text-neutral-2' };
};
</script>

<template>
    <AppLayout :title="`${belonging_name} - Área ${area_display_name}`">
        <div class="container mx-auto p-4 bg-neutral-3 dark:bg-neutral-1 min-h-screen">
            <HeaderSection
                :title="`${belonging_name} - Área ${area_display_name}`"
                :summary-data="{
                    propuestos: totales.total_propuestos ?? 0,
                    verificados: totales.total_verificados ?? 0,
                    total: totales.total ?? 0 }"
                :show-back-button="true"
            />

            <div v-if="records.data.length === 0" class="text-center text-neutral-2 dark:text-neutral-0">
                {{ $t('no_records') }}
            </div>
            <div v-else>
                <!-- Dropdown para ordenar en móviles -->
                <div class="sm:hidden mb-4 flex items-center gap-2">
                    <label class="text-sm text-neutral-1 dark:text-neutral-0">{{ $t('sort_by') }}</label>
                    <select
                        v-model="sortColumn"
                        @change="toggleSortOrder(sortColumn)"
                        class="appearance-none border border-neutral-4 dark:border-neutral-2 rounded px-2 py-1 pr-8 text-neutral-2 dark:text-neutral-0 bg-neutral-0 dark:bg-neutral-2"
                    >
                        <option v-for="col in columns" :key="col.key" :value="col.key">
                            {{ col.label }}
                        </option>
                    </select>
                    <button
                        @click="sortOrder = sortOrder === 'asc' ? 'desc' : 'asc'; toggleSortOrder(sortColumn)"
                        class="text-sm text-secondary-0 dark:text-secondary-0"
                    >
                        {{ sortOrder === 'asc' ? '↑' : '↓' }}
                    </button>
                </div>

                <!-- Diseño de tarjetas para pantallas pequeñas -->
                <div class="block sm:hidden space-y-4">
                    <div
                        v-for="record in records.data"
                        :key="record.id"
                        class="bg-neutral-0 dark:bg-neutral-2 border border-neutral-4 dark:border-neutral-2 rounded-lg shadow-sm"
                    >
                        <div class="bg-main-0 dark:bg-main-0 px-4 py-2 flex justify-between items-center rounded-t-lg">
                            <Link
                                :href="route(`skyfall.area-${area_name.toLowerCase()}.show`, record.id)"
                                class="text-neutral-0 dark:text-neutral-0 font-semibold text-lg hover:underline"
                            >
                                {{ record.name || $t('na') }}
                            </Link>
                            <span class="text-sm font-medium" :class="formatStatus(record.status).class">
                                {{ formatStatus(record.status).label }}
                            </span>
                        </div>
                        <div class="border-b-4 border-secondary-3"></div>
                        <div class="p-4">
                            <div class="grid grid-cols-2 gap-2 text-sm text-neutral-2 dark:text-neutral-0">
                                <div v-if="has_category">
                                    <span class="font-medium text-neutral-1 dark:text-neutral-0">{{ $t('category') }}:</span>
                                    {{ record.category_name || $t('na') }}
                                </div>
                                <div>
                                    <span class="font-medium text-neutral-1 dark:text-neutral-0">{{ $t('start_date') }}:</span>
                                    {{ formatDate(record.init_date) }}
                                </div>
                                <div>
                                    <span class="font-medium text-neutral-1 dark:text-neutral-0">{{ $t('end_date') }}:</span>
                                    {{ formatEndDate(record.end_date, record.currently) }}
                                </div>
                                <div class="col-span-2">
                                    <span class="font-medium text-neutral-1 dark:text-neutral-0">{{ $t('value') }}:</span>
                                    <span class="ml-2 text-lg font-semibold text-main-1 dark:text-main-1">{{ formatNumber(record.value) }}</span>
                                </div>
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
                                    @click="toggleSortOrder(col.key)"
                                >
                                    {{ col.label }}
                                    <span v-if="sortColumn === col.key" class="text-secondary-0">
                                        {{ sortOrder === 'asc' ? '↑' : '↓' }}
                                    </span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="record in records.data"
                                :key="record.id"
                                class="border-t border-neutral-4 dark:border-neutral-2 hover:bg-neutral-3 dark:hover:bg-neutral-1"
                            >
                                <td v-if="has_category" class="p-3 text-neutral-2 dark:text-neutral-0">{{ record.category_name || $t('na') }}</td>
                                <td class="p-3 text-neutral-2 dark:text-neutral-0">
                                    <Link
                                        :href="route(`skyfall.area-${area_name.toLowerCase()}.show`, record.id)"
                                        class="text-main-1 dark:text-main-1 hover:underline"
                                    >
                                        {{ record.name || $t('na') }}
                                    </Link>
                                </td>
                                <td class="p-3 text-neutral-2 dark:text-neutral-0">{{ formatDate(record.init_date) }}</td>
                                <td class="p-3 text-neutral-2 dark:text-neutral-0">{{ formatEndDate(record.end_date, record.currently) }}</td>
                                <td class="p-3 text-lg font-semibold text-main-1 dark:text-main-1">{{ formatNumber(record.value) }}</td>
                                <td class="p-3" :class="formatStatus(record.status).class">{{ formatStatus(record.status).label }}</td>
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
                                class="appearance-none border border-neutral-4 dark:border-neutral-2 rounded px-2 py-1 pr-8 text-neutral-2 dark:text-neutral-0 bg-neutral-0 dark:bg-neutral-2"
                            >
                                <option v-for="option in perPageOptions" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                        </div>
                        <div class="text-sm text-neutral-2 dark:text-neutral-0">
                            {{ $t('showing') }} {{ records.per_page * (records.current_page - 1) + 1 }} {{ $t('to') }}
                            {{ Math.min(records.per_page * records.current_page, records.total) }} {{ $t('of') }} {{ records.total }} {{ $t('records') }}
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button
                            :disabled="records.current_page === 1"
                            @click="changePage(records.current_page - 1)"
                            class="px-4 py-2 bg-main-0 text-neutral-0 rounded-lg disabled:opacity-50 hover:bg-main-1"
                        >
                            {{ $t('Previous') }}
                        </button>
                        <button
                            :disabled="records.current_page === records.last_page"
                            @click="changePage(records.current_page + 1)"
                            class="px-4 py-2 bg-main-0 text-neutral-0 rounded-lg disabled:opacity-50 hover:bg-main-1"
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
</style>