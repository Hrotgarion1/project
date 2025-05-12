<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import GoBackButton from '@/Components/Common/GoBackButton.vue';
import { computed, getCurrentInstance } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';
import { useAreaDefinicionStore } from '@/stores/areaDefinicionStore';
import alerts from '@/utils/alerts';

console.debug('AreaDetail cargado');

const props = defineProps({
    area_name: {
        type: String,
        required: true,
    },
    area_display_name: {
        type: String,
        required: true,
    },
    area_id: {
        type: Number,
        required: true,
    },
    definicion: {
        type: Object,
        required: true, // Corregido de Console a required
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
});

console.debug('Props recibidas en AreaDetail:', props);

const instance = getCurrentInstance();
const $t = instance?.proxy.$t ?? ((key) => key);
const definicionStore = useAreaDefinicionStore();

// Determinar si es Área A basándonos en area_name
const isAreaA = computed(() => {
    const isA = props.area_name.toLowerCase() === 'a';
    console.debug('isAreaA:', isA, 'area_name:', props.area_name, 'categorias:', props.categorias);
    return isA;
});

const formatDate = (date) => {
    if (!date) return $t('actual');
    return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const formatStatus = (status) => {
    const statuses = { '1': $t('proposed'), '2': $t('verified') };
    return statuses[status] || $t('na');
};

const formatValue = (value) => {
    console.debug('Formateando valor:', { value, type: typeof value });
    const numericValue = Number(value);
    if (isNaN(numericValue)) {
        console.warn('Valor no numérico:', value);
        return $t('na');
    }
    return numericValue.toLocaleString('es-ES', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
        useGrouping: true,
    });
};

const getCategoryName = () => {
    console.debug('getCategoryName:', { category: props.definicion.category, isAreaA: isAreaA.value });
    if (!isAreaA.value || !props.definicion.category) return $t('na');
    return props.definicion.category.name || $t('category_not_found');
};

const deleteDefinicion = async () => {
    console.debug('Eliminando definición', props.definicion.id);
    const result = await alerts.confirmDelete({ t: $t });
    if (result.isConfirmed) {
        try {
            await definicionStore.deleteDefinicion(props.definicion.id, props.area_name);
            alerts.success($t, 'record_deleted');
            router.visit(route(`skyfall.area-${props.area_name.toLowerCase()}.index`));
        } catch (error) {
            console.error('Error al eliminar definición:', error);
            await alerts.error($t, 'error_occurred');
        }
    }
};

const verifyDefinicion = async () => {
    console.debug('Verificando definición', props.definicion.id);
    const result = await alerts.confirmAction({ t: $t }, 'verify');
    if (result.isConfirmed) {
        try {
            await definicionStore.verifyDefinicion(props.definicion.id, props.area_name);
            alerts.success($t, 'record_verified');
            router.reload({ preserveState: true, preserveScroll: true });
        } catch (error) {
            console.error('Error al verificar definición:', error);
            await alerts.error($t, 'error_occurred');
        }
    }
};
</script>

<template>
    <AppLayout :title="$t('record_details')">
        <div class="container mx-auto p-4 bg-neutral-3 dark:bg-neutral-1 min-h-screen">
            <div class="flex items-center gap-2 mb-6 bg-main-0 dark:bg-main-0 p-4 rounded-lg">
                <GoBackButton />
                <h1 class="text-xl font-bold text-neutral-0 dark:text-neutral-0">
                    {{ $t('Record details') }} - {{ area_display_name }}
                </h1>
            </div>
            <div v-if="definicion" class="bg-neutral-0 dark:bg-neutral-2 border border-neutral-4 dark:border-neutral-2 rounded-lg p-6 shadow-sm">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6 text-neutral-2 dark:text-neutral-0">
                    <div>
                        <h2 class="text-lg font-semibold text-neutral-1 dark:text-neutral-0">{{ definicion.name }}</h2>
                    </div>
                    <div>
                        <p><strong class="text-neutral-1 dark:text-neutral-0">{{ $t('value') }}:</strong> <span class="text-main-1 dark:text-main-1">{{ formatValue(definicion.value) }}</span></p>
                    </div>
                    <div>
                        <p><strong class="text-neutral-1 dark:text-neutral-0">{{ $t('status') }}:</strong> <span :class="definicion.status === '2' ? 'text-secondary-1' : 'text-secondary-2'">{{ formatStatus(definicion.status) }}</span></p>
                    </div>
                    <div>
                        <p><strong class="text-neutral-1 dark:text-neutral-0">{{ $t('daily_hours') }}:</strong> {{ definicion.schedule || $t('na') }}</p>
                    </div>
                    <div>
                        <p><strong class="text-neutral-1 dark:text-neutral-0">{{ $t('overtime_hours') }}:</strong> {{ definicion.overtime || $t('na') }}</p>
                    </div>
                    <div>
                        <p><strong class="text-neutral-1 dark:text-neutral-0">{{ $t('start_date') }}:</strong> {{ formatDate(definicion.init_date) }}</p>
                    </div>
                    <div>
                        <p><strong class="text-neutral-1 dark:text-neutral-0">{{ $t('end_date') }}:</strong> {{ definicion.currently === 'yes' ? $t('actual') : formatDate(definicion.end_date) }}</p>
                    </div>
                    <div>
                        <p><strong class="text-neutral-1 dark:text-neutral-0">{{ $t('Country') }}:</strong> {{ definicion.pais?.name || $t('na') }}</p>
                    </div>
                    <div>
                        <p><strong class="text-neutral-1 dark:text-neutral-0">{{ $t('belonging') }}:</strong> {{ definicion.belonging?.name || $t('na') }}</p>
                    </div>
                    <div v-if="isAreaA">
                        <p><strong class="text-neutral-1 dark:text-neutral-0">{{ $t('category') }}:</strong> {{ getCategoryName() }}</p>
                    </div>
                </div>
                <div class="space-y-6">
                    <div>
                        <p><strong class="text-neutral-1 dark:text-neutral-0">{{ $t('description') }}:</strong></p>
                        <div class="border border-neutral-4 dark:border-neutral-2 rounded-lg p-4 bg-neutral-3 dark:bg-neutral-3 whitespace-pre-wrap text-neutral-2 dark:text-neutral-1">
                            {{ definicion.description || $t('na') }}
                        </div>
                    </div>
                    <div>
                        <p><strong class="text-neutral-1 dark:text-neutral-0">{{ $t('details') }}:</strong></p>
                        <div class="border border-neutral-4 dark:border-neutral-2 rounded-lg p-4 bg-neutral-3 dark:bg-neutral-3 whitespace-pre-wrap text-neutral-2 dark:text-neutral-1">
                            {{ definicion.details || $t('na') }}
                        </div>
                    </div>
                    <div>
                        <p><strong class="text-neutral-1 dark:text-neutral-0">{{ $t('youtube_videos') }}:</strong></p>
                        <p class="text-neutral-2 dark:text-neutral-0">{{ $t('coming_soon') }}</p>
                    </div>
                    <div>
                        <p><strong class="text-neutral-1 dark:text-neutral-0">{{ $t('images') }}:</strong></p>
                        <p class="text-neutral-2 dark:text-neutral-0">{{ $t('coming_soon') }}</p>
                    </div>
                    <div>
                        <p><strong class="text-neutral-1 dark:text-neutral-0">{{ $t('Pdf') }}:</strong></p>
                        <p class="text-neutral-2 dark:text-neutral-0">{{ $t('coming_soon') }}</p>
                    </div>
                </div>
                <div class="flex justify-end gap-4 mt-6">
                    <Link
                        v-if="definicion.status !== '2'"
                        :href="route(`skyfall.area-${area_name.toLowerCase()}.edit`, definicion.id)"
                        class="px-4 py-2 bg-main-1 dark:bg-main-1 text-neutral-0 dark:text-neutral-0 rounded-lg hover:bg-main-0 dark:hover:bg-main-0 transition-colors"
                    >
                        {{ $t('edit') }}
                    </Link>
                    <button
                        v-if="definicion.status === '1'"
                        @click="verifyDefinicion"
                        class="px-4 py-2 bg-secondary-0 dark:bg-secondary-0 text-neutral-0 dark:text-neutral-0 rounded-lg hover:bg-secondary-1 dark:hover:bg-secondary-1 transition-colors"
                    >
                        {{ $t('verify') }}
                    </button>
                    <button
                        @click="deleteDefinicion"
                        class="px-4 py-2 bg-secondary-3 dark:bg-secondary-3 text-neutral-0 dark:text-neutral-0 rounded-lg hover:bg-secondary-2 dark:hover:bg-secondary-2 transition-colors"
                    >
                        {{ $t('delete') }}
                    </button>
                    <Link
                        :href="route(`skyfall.area-${area_name.toLowerCase()}.index`)"
                        class="px-4 py-2 bg-neutral-2 dark:bg-neutral-2 text-neutral-0 dark:text-neutral-0 rounded-lg hover:bg-neutral-1 dark:hover:bg-neutral-1 transition-colors"
                    >
                        {{ $t('back') }}
                    </Link>
                </div>
            </div>
            <p v-else class="text-secondary-3 dark:text-secondary-3">{{ $t('record_not_found') }}</p>
        </div>
    </AppLayout>
</template>

<style scoped>
@media (max-width: 640px) {
    .grid > div {
        margin-bottom: 1.5rem;
    }
    .whitespace-pre-wrap {
        word-break: break-word;
    }
    .space-y-6 > div {
        margin-bottom: 1.5rem;
    }
}
</style>