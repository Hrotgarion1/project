<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import HeaderSection from '@/Components/Common/HeaderSection.vue';
import { ref, watch, getCurrentInstance, computed, onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { useAreaDefinicionStore } from '@/stores/areaDefinicionStore';
import { useAreaGlobalStore } from '@/stores/areaGlobalStore';
import { useFormat } from '@/composables/useFormat';
import alerts from '@/utils/alerts';

console.debug('BelongingDetail cargado');

const instance = getCurrentInstance();
const $t = instance?.proxy?.$t ?? ((key) => key);
const { formatNumber } = useFormat();

const props = defineProps({
    belonging: {
        type: Object,
        required: true,
    },
    records: {
        type: Object,
        required: true,
    },
});

console.debug('Props recibidas en BelongingDetail:', props);

const definicionStore = useAreaDefinicionStore();
const globalStore = useAreaGlobalStore();
const sortKey = ref('area_key');
const sortOrder = ref('asc');
const perPage = ref(props.records.per_page || 3);

const perPageOptions = [
    { value: 3, label: '3' },
    { value: 5, label: '5' },
    { value: 10, label: '10' },
    { value: 20, label: '20' },
];

const columns = computed(() => [
    { key: 'area_key', label: $t('Area') }, // Primero Area
    { key: 'name', label: $t('name') }, // Segundo name
    { key: 'status', label: $t('status') },
    { key: 'puntuacion_1', label: $t('proposed') },
    { key: 'puntuacion_2', label: $t('verified') },
]);

onMounted(() => {
    globalStore.fetchGlobalData(props.belonging.id);
    console.debug('Records statuses:', props.records.data.map(record => ({
        id: record.id,
        status: record.status,
        statusDisplay: formatStatus(record.status),
        name: record.name,
    })));
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
        route('skyfall.belonging.detail', props.belonging.id),
        { sort_by: sortKey.value, sort_order: sortOrder.value, per_page: perPage.value },
        { preserveState: true, preserveScroll: true }
    );
};

const changePage = (page) => {
    console.debug('Cambiando a página:', page, 'per_page:', perPage.value);
    router.get(
        route('skyfall.belonging.detail', props.belonging.id),
        { page, per_page: perPage.value, sort_by: sortKey.value, sort_order: sortOrder.value },
        { preserveState: true, preserveScroll: true }
    );
};

watch(perPage, (newPerPage) => {
    console.debug('perPage cambiado a:', newPerPage);
    changePage(1);
});

const formatStatus = (status) => {
    const statuses = {
        1 : { label: $t('proposed'), class: 'text-secondary-2' },
        2 : { label: $t('verified'), class: 'text-secondary-1' },
    };
    return statuses[status] || { label: $t('unknown'), class: 'text-neutral-2' };
};

const openForm = (record) => {
    if (!record.area_key) {
        console.error('No se encontró area_key para el registro:', record);
        alerts.error($t, 'error_no_area');
        return;
    }
    console.debug('Abriendo formulario para editar', record);
    const routeName = `skyfall.area-${record.area_key.toLowerCase()}.edit`;
    router.visit(route(routeName, record.recordable_id));
};

const verifyDefinicion = async (record) => {
    if (!record.area_key) {
        console.error('No se encontró area_key para el registro:', record);
        alerts.error($t, 'error_no_area');
        return;
    }
    console.debug('Verificando definición', record.id);
    const result = await alerts.confirmAction({ t: $t }, 'verify');
    if (result.isConfirmed) {
        try {
            await definicionStore.verifyDefinicion(record.recordable_id, record.area_key);
            router.reload({ preserveState: true, preserveScroll: true });
        } catch (error) {
            console.error('Error al verificar definición:', error);
            await alerts.error($t, 'error_occurred');
        }
    }
};

const deleteDefinicion = async (record) => {
    if (!record.area_key) {
        console.error('No se encontró area_key para el registro:', record);
        alerts.error($t, 'error_no_area');
        return;
    }
    console.debug('Eliminando definición', record.id);
    const result = await alerts.confirmDelete({ t: $t });
    if (result.isConfirmed) {
        try {
            await definicionStore.deleteDefinicion(record.recordable_id, record.area_key);
            router.reload({ preserveState: true, preserveScroll: true });
        } catch (error) {
            console.error('Error al eliminar definición:', error);
            await alerts.error($t, 'error_occurred');
        }
    }
};
</script>

<template>
    <AppLayout :title="$t('Belonging Detail') + ': ' + belonging.name">
        <div class="container mx-auto p-4 bg-neutral-3 dark:bg-neutral-1 min-h-screen">
            <HeaderSection
                :title="$t('Belonging Detail') + ': ' + belonging.name"
                :summary-data="{
                    propuestos: globalStore.sumasPorBelonging[props.belonging.id]?.total_propuestos ?? 0,
                    verificados: globalStore.sumasPorBelonging[props.belonging.id]?.total_verificados ?? 0,
                    total: (globalStore.sumasPorBelonging[props.belonging.id]?.total_propuestos ?? 0) + (globalStore.sumasPorBelonging[props.belonging.id]?.total_verificados ?? 0)
                }"
                :show-back-button="true"
            />

            <div v-if="!records.data?.length" class="text-center text-neutral-2 dark:text-neutral-0 mt-6">
                {{ $t('no_records') }}
            </div>
            <div v-else>
                <!-- Dropdown para ordenar en móviles -->
                <div class="sm:hidden mb-4 flex items-center gap-2">
                    <label class="text-sm text-neutral-1 dark:text-neutral-0">{{ $t('sort_by') }}</label>
                    <select
                        v-model="sortKey"
                        @change="sort(sortKey)"
                        class="appearance-none border border-neutral-4 dark:border-neutral-2 rounded px-2 py-1 pr-8 text-neutral-2 dark:text-neutral-0 bg-neutral-0 dark:bg-neutral-2 bg-[length:16px_16px] bg-no-repeat bg-right-2"
                        :aria-label="$t('sort_by')"
                    >
                        <option v-for="col in columns" :key="col.key" :value="col.key">
                            {{ col.label }}
                        </option>
                    </select>
                    <button
                        @click="sortOrder = sortOrder === 'asc' ? 'desc' : 'asc'; sort(sortKey)"
                        class="text-sm text-secondary-0 dark:text-secondary-0"
                        :aria-label="sortOrder === 'asc' ? $t('sort_ascending') : $t('sort_descending')"
                    >
                        {{ sortOrder === 'asc' ? '↑' : '↓' }}
                    </button>
                </div>

                <!-- Diseño de tarjetas para pantallas pequeñas (sin cambios) -->
                <div class="block sm:hidden space-y-4">
                    <div
                        v-for="record in records.data"
                        :key="record.id"
                        class="bg-neutral-0 dark:bg-neutral-2 border border-neutral-4 dark:border-neutral-2 rounded-lg shadow-sm"
                    >
                        <div class="bg-main-0 dark:bg-main-0 px-4 py-2 flex justify-between items-center rounded-t-lg">
                            <Link
                                :href="route(`skyfall.area-${record.area_key.toLowerCase()}.show`, record.recordable_id)"
                                class="text-neutral-0 dark:text-neutral-0 hover:underline font-semibold text-lg"
                                :aria-label="$t('view_record') + ' ' + (record.name || $t('unknown'))"
                            >
                                {{ record.name || $t('unknown') }}
                            </Link>
                            <span class="text-sm font-medium" :class="formatStatus(record.status).class">
                                {{ formatStatus(record.status).label }}
                            </span>
                        </div>
                        <div class="border-b-4 border-secondary-3"></div>
                        <div class="p-4">
                            <div class="grid grid-cols-2 gap-2 text-sm text-neutral-2 dark:text-neutral-0">
                                <div>
                                    <span class="font-medium text-neutral-1 dark:text-neutral-0">{{ $t('area') }}:</span>
                                    {{ record.area_display_name || $t('unknown_area') }}
                                </div>
                                <div>
                                    <span class="font-medium text-neutral-1 dark:text-neutral-0">{{ $t('proposed') }}:</span>
                                    <span class="ml-2 text-secondary-2">{{ formatNumber(record.puntuacion_1) }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-neutral-1 dark:text-neutral-0">{{ $t('verified') }}:</span>
                                    <span class="ml-2 text-secondary-1">{{ formatNumber(record.puntuacion_2) }}</span>
                                </div>
                            </div>
                            <div class="mt-3 flex gap-2 flex-wrap">
                                <button
                                    v-if="record.status !== 2 "
                                    @click="openForm(record)"
                                    class="text-main-1 dark:text-main-1 hover:underline text-sm"
                                >
                                    {{ $t('edit') }}
                                </button>
                                <button
                                    v-if="record.status === 1 "
                                    @click="verifyDefinicion(record)"
                                    class="text-secondary-0 dark:text-secondary-0 hover:underline text-sm"
                                >
                                    {{ $t('verify') }}
                                </button>
                                <button
                                    @click="deleteDefinicion(record)"
                                    class="text-secondary-3 dark:text-secondary-3 hover:underline text-sm"
                                >
                                    {{ $t('delete') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Diseño de tabla para pantallas grandes -->
                <div class="hidden sm:block overflow-x-auto">
                    <table class="min-w-full bg-neutral-0 dark:bg-neutral-2 border border-neutral-4 dark:border-neutral-2 rounded-lg shadow-sm" aria-label="Records table">
                        <thead class="bg-main-0 dark:bg-main-0" style="border-bottom: 4px solid #FFA07A;">
                            <tr>
                                <th
                                    v-for="col in columns"
                                    :key="col.key"
                                    class="p-3 text-left text-sm font-medium text-neutral-0 dark:text-neutral-0 cursor-pointer hover:bg-main-1 dark:hover:bg-main-1"
                                    @click="sort(col.key)"
                                    :aria-label="$t('sort_by') + ' ' + col.label"
                                >
                                    {{ col.label }}
                                    <span v-if="sortKey === col.key" class="text-secondary-0">
                                        {{ sortOrder === 'asc' ? '↑' : '↓' }}
                                    </span>
                                </th>
                                <th class="p-3 text-left text-sm font-medium text-neutral-0 dark:text-neutral-0">
                                    {{ $t('actions') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="record in records.data"
                                :key="record.id"
                                class="border-t border-neutral-4 dark:border-neutral-2 hover:bg-neutral-3 dark:hover:bg-neutral-3 text-neutral-2 dark:text-neutral-0"
                            >
                                <td class="p-3">{{ record.area_display_name || $t('unknown_area') }}</td>
                                <td class="p-3">
                                    <Link
                                        :href="route(`skyfall.area-${record.area_key.toLowerCase()}.show`, record.recordable_id)"
                                        class="text-main-1 dark:text-main-1 hover:underline"
                                        :aria-label="$t('view_record') + ' ' + (record.name || $t('unknown'))"
                                    >
                                        {{ record.name || $t('unknown') }}
                                    </Link>
                                </td>
                                <td class="p-3" :class="formatStatus(record.status).class">
                                    {{ formatStatus(record.status).label }}
                                </td>
                                <td class="p-3 text-secondary-2">{{ formatNumber(record.puntuacion_1) }}</td>
                                <td class="p-3 text-secondary-1">{{ formatNumber(record.puntuacion_2) }}</td>
                                <td class="p-3 flex gap-2 flex-wrap">
                                    <button
                                        v-if="record.status !== '2'"
                                        @click="openForm(record)"
                                        class="text-main-1 dark:text-main-1 hover:underline"
                                    >
                                        {{ $t('edit') }}
                                    </button>
                                    <button
                                        v-if="record.status === 1 "
                                        @click="verifyDefinicion(record)"
                                        class="text-secondary-0 dark:text-secondary-0 hover:underline"
                                    >
                                        {{ $t('verify') }}
                                    </button>
                                    <button
                                        @click="deleteDefinicion(record)"
                                        class="text-secondary-3 dark:text-secondary-3 hover:underline"
                                    >
                                        {{ $t('delete') }}
                                    </button>
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
                                class="appearance-none border border-neutral-4 dark:border-neutral-2 rounded px-2 py-1 pr-8 text-neutral-2 dark:text-neutral-0 bg-neutral-0 dark:bg-neutral-2 bg-[length:16px_16px] bg-no-repeat bg-right-2"
                                :aria-label="$t('records_per_page')"
                            >
                                <option v-for="option in perPageOptions" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                        </div>
                        <div class="text-sm text-neutral-2 dark:text-neutral-0">
                            {{ $t('showing') }} {{ records.from || 0 }}
                            {{ $t('to') }}
                            {{ records.to || 0 }}
                            {{ $t('of') }} {{ records.total || 0 }} {{ $t('records') }}
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button
                            :disabled="records.current_page === 1"
                            @click="changePage(records.current_page - 1)"
                            class="px-4 py-2 bg-main-0 dark:bg-main-0 text-neutral-0 dark:text-neutral-0 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-main-1 dark:hover:bg-main-1"
                            :aria-label="$t('previous_page')"
                        >
                            {{ $t('Previous') }}
                        </button>
                        <button
                            :disabled="records.current_page === records.last_page"
                            @click="changePage(records.current_page + 1)"
                            class="px-4 py-2 bg-main-0 dark:bg-main-0 text-neutral-0 dark:text-neutral-0 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-main-1 dark:hover:bg-main-1"
                            :aria-label="$t('next_page')"
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