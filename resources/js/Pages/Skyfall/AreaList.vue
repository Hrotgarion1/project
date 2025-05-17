<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import HeaderSection from '@/Components/Common/HeaderSection.vue';
import { ref, onMounted, getCurrentInstance, watch, computed } from 'vue';
import { toast } from 'vue3-toastify';
import { useAreaDefinicionStore } from '@/stores/areaDefinicionStore';
import { useCalendarioStore } from '@/stores/calendarioStore';
import { Link, router } from '@inertiajs/vue3';
import alerts from '@/utils/alerts';
import { useFormat } from '@/composables/useFormat';

const props = defineProps({
    area_name: {
        type: String,
        required: true,
    },
    area_id: {
        type: Number,
        required: true,
    },
    area_display_name: {
        type: String,
        required: true,
    },
    paises: {
        type: Array,
        default: () => [],
    },
    categorias: {
        type: Array,
        default: () => [],
    },
    festivos: {
        type: Array,
        default: () => [],
    },
    definiciones: {
        type: Object,
        default: () => ({
            data: [],
            current_page: 1,
            last_page: 1,
            per_page: 3,
            total: 0,
            from: 0,
            to: 0,
        }),
    },
    totales: {
        type: Object,
        default: () => ({ propuestos: 0, verificados: 0, total: 0 }),
    },
});

console.debug('AreaList cargada para área:', props.area_name, 'Props:', {
    area_id: props.area_id,
    area_display_name: props.area_display_name,
    definiciones: props.definiciones,
    totales: props.totales,
});

const instance = getCurrentInstance();
const $t = instance?.proxy.$t ?? ((key) => key);
const definicionStore = useAreaDefinicionStore();
const calendarioStore = useCalendarioStore();
const { formatNumber, formatDate } = useFormat();

const sortOrder = ref('asc');
const sortColumn = ref('init_date');
const perPage = ref(props.definiciones.per_page || 3);

const perPageOptions = [
    { value: 3, label: '3' },
    { value: 5, label: '5' },
    { value: 10, label: '10' },
    { value: 20, label: '20' },
];

const columns = computed(() => [
    { key: 'belonging_name', label: $t('belonging') },
    { key: 'init_date', label: $t('start_date') },
    { key: 'end_date', label: $t('end_date') },
    { key: 'name', label: $t('name') },
    { key: 'value', label: $t('value') },
    { key: 'status', label: $t('status') },
]);

const initializeStores = () => {
    console.debug('Inicializando stores para área:', props.area_name, 'area_id:', props.area_id);
    if (calendarioStore.festivos.length === 0) {
        calendarioStore.setFestivos(props.festivos);
    }
    definicionStore.setAreaData(props.area_name, {
        definiciones: props.definiciones.data,
        totales: props.totales,
        categorias: props.categorias,
        pagination: {
            current_page: props.definiciones.current_page,
            last_page: props.definiciones.last_page,
            per_page: props.definiciones.per_page,
            total: props.definiciones.total,
            from: props.definiciones.from,
            to: props.definiciones.to,
        },
    });
};

watch(() => [props.definiciones, props.totales], ([newDefiniciones, newTotales]) => {
    console.debug('Props actualizadas, sincronizando store:', {
        definiciones: newDefiniciones,
        totales: newTotales,
    });
    definicionStore.setAreaData(props.area_name, {
        definiciones: newDefiniciones.data,
        totales: newTotales,
        categorias: props.categorias,
        pagination: {
            current_page: newDefiniciones.current_page,
            last_page: newDefiniciones.last_page,
            per_page: newDefiniciones.per_page,
            total: newDefiniciones.total,
            from: newDefiniciones.from,
            to: newDefiniciones.to,
        },
    });
    perPage.value = newDefiniciones.per_page;
});

watch(perPage, (newPerPage) => {
    console.debug('Cambiando per_page a:', newPerPage);
    changePage(1);
});

const changePage = async (page) => {
    console.debug('Cambiando a página:', page, 'per_page:', perPage.value);
    try {
        await router.get(
            route(`skyfall.area-${props.area_name.toLowerCase()}.index`),
            { page, per_page: perPage.value, sort_by: sortColumn.value, sort_order: sortOrder.value },
            {
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    console.debug('Paginación exitosa, página:', page);
                },
                onError: (errors) => {
                    console.error('Error en paginación:', errors);
                    toast.error($t('error_pagination'));
                },
            }
        );
    } catch (error) {
        console.error('Error inesperado en paginación:', error);
        toast.error($t('error_pagination'));
    }
};

const openForm = (definicion) => {
    console.debug('Abriendo formulario', definicion);
    const routeName = `skyfall.area-${props.area_name.toLowerCase()}.edit`;
    router.visit(route(routeName, definicion.id));
};

const createForm = () => {
    console.debug('Abriendo formulario de creación');
    const routeName = `skyfall.area-${props.area_name.toLowerCase()}.create`;
    router.visit(route(routeName));
};

const deleteDefinicion = async (id) => {
    console.debug('Eliminando definición', id);
    const result = await alerts.confirmDelete({ t: $t });
    if (result.isConfirmed) {
        try {
            await definicionStore.deleteDefinicion(id, props.area_name);
            router.reload({ preserveState: true, preserveScroll: true });
            toast.success($t('record_deleted'));
        } catch (error) {
            console.error('Error al eliminar definición:', error);
            await alerts.error($t, 'error_occurred');
        }
    }
};

const verifyDefinicion = async (id) => {
    console.debug('Verificando definición', id);
    const result = await alerts.confirmAction({ t: $t }, 'verify');
    if (result.isConfirmed) {
        try {
            await definicionStore.verifyDefinicion(id, props.area_name);
            router.reload({ preserveState: true, preserveScroll: true });
            toast.success($t('record_verified'));
        } catch (error) {
            console.error('Error al verificar definición:', error);
            await alerts.error($t, 'error_occurred');
        }
    }
};

const toggleSortOrder = (column) => {
    if (sortColumn.value === column) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortColumn.value = column;
        sortOrder.value = 'asc';
    }
    console.debug('Ordenando por:', sortColumn.value, 'orden:', sortOrder.value);
    router.get(
        route(`skyfall.area-${props.area_name.toLowerCase()}.index`),
        { sort_by: sortColumn.value, sort_order: sortOrder.value, per_page: perPage.value },
        { preserveState: true, preserveScroll: true }
    );
};

const formatStatus = (status) => {
    const statuses = { 1 : $t('proposed'), 2 : $t('verified') };
    return statuses[status] || $t('unknown');
};

onMounted(() => {
    initializeStores();
});
</script>

<template>
    <AppLayout :title="$t('Area') + ' ' + area_display_name">
        <div class="container mx-auto p-4 bg-neutral-3 dark:bg-neutral-1 min-h-screen">
            <HeaderSection
                :title="$t('Area') + ' ' + area_display_name"
                :summary-data="props.totales"
                :show-back-button="true"
                :action-button="{ label: 'new_record', onClick: createForm, permission: 'create-definicion' }"
            />

            <div v-if="definicionStore.loading" class="text-center text-neutral-2 dark:text-neutral-0">
                {{ $t('loading') }}
                <svg
                    class="animate-spin h-5 w-5 mx-auto"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                >
                    <circle
                        class="opacity-25"
                        cx="12"
                        cy="12"
                        r="10"
                        stroke="currentColor"
                        stroke-width="4"
                    ></circle>
                    <path
                        class="opacity-75"
                        fill="currentColor"
                        d="M4 12a8 8 0 018-8v8z"
                    ></path>
                </svg>
            </div>
            <div
                v-else-if="definicionStore.error"
                class="text-center text-secondary-3 dark:text-secondary-3"
            >
                {{ definicionStore.error }}
            </div>
            <div
                v-else-if="!props.definiciones.data.length"
                class="text-center text-neutral-2 dark:text-neutral-0"
            >
                {{ $t('no_records') }}
            </div>
            <div v-else>
                <!-- Dropdown para ordenar en móviles -->
                <div class="sm:hidden mb-4 flex items-center gap-2">
                    <label class="text-sm text-neutral-1 dark:text-neutral-0">{{ $t('sort_by') }}</label>
                    <select
                        v-model="sortColumn"
                        @change="toggleSortOrder(sortColumn)"
                        class="appearance-none border border-neutral-4 dark:border-neutral-2 rounded px-2 py-1 pr-8 text-neutral-2 dark:text-neutral-0 bg-neutral-0 dark:bg-neutral-2 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNiIgaGVpZ2h0PSIxNiIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9IiM4QkMzNEEiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIj48cG9seWxpbmUgcG9pbnRzPSI2IDkgMTIgMTUgMTggOSIvPjwvc3ZnPg==')] bg-no-repeat bg-[right_0.5rem_center] bg-[length:16px_16px]"
                        :aria-label="$t('sort_by')"
                    >
                        <option v-for="col in columns" :key="col.key" :value="col.key">
                            {{ col.label }}
                        </option>
                    </select>
                    <button
                        @click="sortOrder = sortOrder === 'asc' ? 'desc' : 'asc'; toggleSortOrder(sortColumn)"
                        class="text-sm text-secondary-0 dark:text-secondary-0"
                        :aria-label="sortOrder === 'asc' ? $t('sort_ascending') : $t('sort_descending')"
                    >
                        {{ sortOrder === 'asc' ? '↑' : '↓' }}
                    </button>
                </div>

                <!-- Diseño de tarjetas para pantallas pequeñas -->
                <div class="block sm:hidden space-y-4">
                    <div
                        v-for="definicion in props.definiciones.data"
                        :key="definicion.id"
                        class="bg-neutral-0 dark:bg-neutral-2 border border-neutral-4 dark:border-neutral-2 rounded-lg shadow-sm"
                    >
                        <div class="bg-main-0 dark:bg-main-0 px-4 py-2 flex justify-between items-center rounded-t-lg">
                            <Link
                                :href="route(`skyfall.area-${area_name.toLowerCase()}.show`, definicion.id)"
                                class="text-neutral-0 dark:text-neutral-0 hover:underline font-semibold text-lg"
                                :aria-label="$t('view_record') + ' ' + (definicion.name || $t('unknown'))"
                            >
                                {{ definicion.name || $t('unknown') }}
                            </Link>
                            <span
                                class="text-sm font-medium"
                                :class="definicion.status === 2 ? 'text-secondary-1 dark:text-secondary-1' : 'text-secondary-2 dark:text-secondary-2'"
                            >
                                {{ formatStatus(definicion.status) }}
                            </span>
                        </div>
                        <div class="border-b-4 border-secondary-3"></div>
                        <div class="p-4">
                            <div class="grid grid-cols-2 gap-2 text-sm text-neutral-2 dark:text-neutral-0">
                                <div>
                                    <span class="font-medium text-neutral-1 dark:text-neutral-0">{{ $t('belonging') }}:</span>
                                    {{ definicion.belonging_name || $t('na') }}
                                </div>
                                <div>
                                    <span class="font-medium text-neutral-1 dark:text-neutral-0">{{ $t('start_date') }}:</span>
                                    {{ formatDate(definicion.init_date) }}
                                </div>
                                <div>
                                    <span class="font-medium text-neutral-1 dark:text-neutral-0">{{ $t('end_date') }}:</span>
                                    {{ definicion.currently === 'yes' ? $t('actual') : formatDate(definicion.end_date) }}
                                </div>
                                <div class="col-span-2">
                                    <span class="font-medium text-neutral-1 dark:text-neutral-0">{{ $t('value') }}:</span>
                                    <span class="ml-2 text-lg font-semibold text-main-1 dark:text-main-1">{{ formatNumber(definicion.value) }}</span>
                                </div>
                            </div>
                            <div class="mt-3 flex gap-2 flex-wrap">
                                <button
                                    v-if="definicion.status !== 2 "
                                    @click="openForm(definicion)"
                                    class="text-main-1 dark:text-main-1 hover:underline text-sm"
                                >
                                    {{ $t('edit') }}
                                </button>
                                <button
                                    v-if="definicion.status === 1 "
                                    @click="verifyDefinicion(definicion.id)"
                                    class="text-secondary-0 dark:text-secondary-0 hover:underline text-sm"
                                >
                                    {{ $t('verify') }}
                                </button>
                                <button
                                    @click="deleteDefinicion(definicion.id)"
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
                                    @click="toggleSortOrder(col.key)"
                                    :aria-label="$t('sort_by') + ' ' + col.label"
                                >
                                    {{ col.label }}
                                    <span v-if="sortColumn === col.key" class="text-secondary-0">
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
                                v-for="definicion in props.definiciones.data"
                                :key="definicion.id"
                                class="border-t border-neutral-4 dark:border-neutral-2 hover:bg-neutral-3 dark:hover:bg-neutral-1"
                            >
                                <td class="p-3 text-neutral-2 dark:text-neutral-0">
                                    {{ definicion.belonging_name || $t('na') }}
                                </td>
                                <td class="p-3 text-neutral-2 dark:text-neutral-0">
                                    {{ formatDate(definicion.init_date) }}
                                </td>
                                <td class="p-3 text-neutral-2 dark:text-neutral-0">
                                    {{ definicion.currently === 'yes' ? $t('actual') : formatDate(definicion.end_date) }}
                                </td>
                                <td class="p-3">
                                    <Link
                                        :href="route(`skyfall.area-${area_name.toLowerCase()}.show`, definicion.id)"
                                        class="text-main-1 dark:text-main-1 hover:underline"
                                        :aria-label="$t('view_record') + ' ' + (definicion.name || $t('unknown'))"
                                    >
                                        {{ definicion.name || $t('unknown') }}
                                    </Link>
                                </td>
                                <td class="p-3 text-lg font-semibold text-main-1 dark:text-main-1">
                                    {{ formatNumber(definicion.value) }}
                                </td>
                                <td class="p-3">
                                    <span
                                        :class="definicion.status === 2 ? 'text-secondary-1 dark:text-secondary-1' : 'text-secondary-2 dark:text-secondary-2'"
                                    >
                                        {{ formatStatus(definicion.status) }}
                                    </span>
                                </td>
                                <td class="p-3 flex gap-2 flex-wrap">
                                    <button
                                        v-if="definicion.status !== 2 "
                                        @click="openForm(definicion)"
                                        class="text-main-1 dark:text-main-1 hover:underline"
                                    >
                                        {{ $t('edit') }}
                                    </button>
                                    <button
                                        v-if="definicion.status === 1 "
                                        @click="verifyDefinicion(definicion.id)"
                                        class="text-secondary-0 dark:text-secondary-0 hover:underline"
                                    >
                                        {{ $t('verify') }}
                                    </button>
                                    <button
                                        @click="deleteDefinicion(definicion.id)"
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
                    <!-- Selector de per_page y texto de paginación -->
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2">
                            <label class="text-sm text-neutral-1 dark:text-neutral-0">{{ $t('per_page') }}</label>
                            <select
                                v-model="perPage"
                                class="appearance-none border border-neutral-4 dark:border-neutral-2 rounded px-2 py-1 pr-8 text-neutral-2 dark:text-neutral-0 bg-neutral-0 dark:bg-neutral-2 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNiIgaGVpZ2h0PSIxNiIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9IiM4QkMzNEEiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIj48cG9seWxpbmUgcG9pbnRzPSI2IDkgMTIgMTUgMTggOSIvPjwvc3ZnPg==')] bg-no-repeat bg-[right_0.5rem_center] bg-[length:16px_16px]"
                                :aria-label="$t('records_per_page')"
                            >
                                <option v-for="option in perPageOptions" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                        </div>
                        <div class="text-sm text-neutral-2 dark:text-neutral-0">
                            {{ $t('showing') }} {{ props.definiciones.from || 0 }}
                            {{ $t('to') }}
                            {{ props.definiciones.to || 0 }}
                            {{ $t('of') }} {{ props.definiciones.total || 0 }} {{ $t('records') }}
                        </div>
                    </div>
                    <!-- Botones de paginación -->
                    <div class="flex gap-2">
                        <button
                            :disabled="props.definiciones.current_page === 1"
                            @click="changePage(props.definiciones.current_page - 1)"
                            class="px-4 py-2 bg-main-0 dark:bg-main-0 text-neutral-0 dark:text-neutral-0 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-main-1 dark:hover:bg-main-1"
                            :aria-label="$t('previous_page')"
                        >
                            {{ $t('Previous') }}
                        </button>
                        <button
                            :disabled="props.definiciones.current_page === props.definiciones.last_page"
                            @click="changePage(props.definiciones.current_page + 1)"
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
</style>