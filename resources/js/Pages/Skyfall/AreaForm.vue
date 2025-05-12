<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import GoBackButton from '@/Components/Common/GoBackButton.vue';
import Belonging from '@/Components/Common/Belonging.vue';
import { ref, watch, computed, getCurrentInstance } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';
import { useCalendarioStore } from '@/stores/calendarioStore';
import { useBelongingStore } from '@/stores/belonging';
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import alerts from '@/utils/alerts';

console.debug('AreaForm cargado');

const props = defineProps({
    user: Object,
    paises: Array,
    festivos: Array,
    definicion: Object,
    area_name: String,
    area_id: Number,
    area_display_name: String,
    categorias: Array,
    belonging_name: String,
});

console.debug('Props recibidas en AreaForm:', {
    user: props.user,
    paises: props.paises,
    festivos: props.festivos,
    definicion: props.definicion,
    area_name: props.area_name,
    area_id: props.area_id,
    area_display_name: props.area_display_name,
    categorias: props.categorias,
    belonging_name: props.belonging_name,
});

const instance = getCurrentInstance();
const $t = instance?.proxy.$t ?? ((key) => key);
const calendarioStore = useCalendarioStore();
const belongingStore = useBelongingStore();

calendarioStore.setFestivos(props.festivos);

const isEdit = computed(() => !!props.definicion?.id);
const isAreaA = computed(() => {
    console.debug('isAreaA:', props.area_name === 'A', 'area_name:', props.area_name, 'categorias:', props.categorias);
    return props.area_name === 'A';
});

const form = useForm({
    area_id: props.definicion?.area_id || props.area_id,
    name: props.definicion?.name || '',
    description: props.definicion?.description || '',
    init_date: props.definicion?.init_date?.split('T')[0] || '',
    end_date: props.definicion?.end_date?.split('T')[0] || '',
    schedule: props.definicion?.schedule || 0,
    overtime: props.definicion?.overtime || 0,
    currently: props.definicion?.currently || 'yes',
    belonging: props.definicion?.belonging || null,
    pais_id: props.definicion?.pais_id || null,
    category_id: props.definicion?.category?.id || null,
    details: props.definicion?.details || '',
    status: props.definicion?.status || '1',
    value: props.definicion?.value || 0,
});

const horasCalculadas = ref(0);
const horasSource = ref('');

const calcularHoras = () => {
    if (isEdit.value && isAreaA.value) {
        horasCalculadas.value = props.definicion?.value || 0;
        horasSource.value = $t('from_record');
    } else if (isAreaA.value && form.currently === 'no') {
        const selectedCategory = props.categorias.find(c => c.id === form.category_id);
        horasCalculadas.value = selectedCategory ? selectedCategory.value : 0;
        horasSource.value = selectedCategory ? $t('from_category') : $t('select_category');
    } else if (form.currently === 'yes') {
        horasCalculadas.value = calendarioStore.calcularHoras(
            form.init_date,
            null,
            form.schedule,
            form.currently,
            form.overtime
        );
        horasSource.value = $t('calculated');
    } else {
        horasCalculadas.value = calendarioStore.calcularHoras(
            form.init_date,
            form.end_date,
            form.schedule,
            form.currently,
            form.overtime
        );
        horasSource.value = $t('calculated');
    }
    console.debug('Horas calculadas:', horasCalculadas.value, 'source:', horasSource.value, 'currently:', form.currently, 'isAreaA:', isAreaA.value, 'category_id:', form.category_id);
};

calcularHoras();

watch(
    () => [form.init_date, form.end_date, form.schedule, form.overtime, form.currently, form.category_id],
    () => {
        calcularHoras();
    },
    { immediate: true }
);

watch(
    () => form.pais_id,
    (newPaisId) => {
        console.debug('Cambio en pais_id:', newPaisId);
        if (newPaisId && !form.belonging) {
            belongingStore.search(newPaisId, '');
        }
    },
    { immediate: true }
);

watch(
    () => form.details,
    (newDetails) => {
        if (newDetails?.length > 1000) {
            toast.error($t('details_too_long'));
            form.details = newDetails.slice(0, 1000);
        }
    }
);

watch(
    () => form.belonging,
    (newBelonging) => {
        console.debug('Cambio en belonging:', newBelonging);
        if (!newBelonging || !newBelonging.id) {
            form.belonging = null;
        }
    },
    { deep: true, immediate: true }
);

watch(
    () => [form.init_date, form.end_date, form.currently],
    ([init, end, currently]) => {
        const today = new Date().toISOString().split('T')[0];
        if (currently === 'yes') {
            form.end_date = ''; // Reset end_date when currently="yes"
            if (init > today) {
                toast.error($t('init_date_future_not_allowed'));
                form.init_date = '';
            }
        } else {
            if (init > today) {
                toast.error($t('init_date_future_not_allowed'));
                form.init_date = '';
            }
            if (end && end > today) {
                toast.error($t('end_date_future_not_allowed'));
                form.end_date = '';
            }
            if (end && init && end < init) {
                toast.error($t('end_date_invalid'));
                form.end_date = '';
            }
        }
    },
    { immediate: true }
);

const validateDates = () => {
    const today = new Date().toISOString().split('T')[0];
    if (form.currently === 'yes') {
        if (form.init_date > today) {
            toast.error($t('init_date_future_not_allowed'));
            return false;
        }
    } else {
        if (form.init_date > today) {
            toast.error($t('init_date_future_not_allowed'));
            return false;
        }
        if (form.end_date && form.end_date > today) {
            toast.error($t('end_date_future_not_allowed'));
            return false;
        }
        if (form.end_date && form.init_date && form.end_date < form.init_date) {
            toast.error($t('end_date_invalid'));
            return false;
        }
        if (!isAreaA.value && !form.end_date) {
            toast.error($t('end_date_required'));
            return false;
        }
    }
    return true;
};

const submit = () => {
    console.debug('Iniciando submit', form.data());
    if (!form.name) {
        toast.error($t('name_required'));
        return;
    }
    if (!form.init_date) {
        toast.error($t('init_date_required'));
        return;
    }
    if (!form.pais_id) {
        toast.error($t('select_country'));
        return;
    }
    if (!form.belonging || !form.belonging.id) {
        toast.error($t('select_belonging'));
        return;
    }
    if (isAreaA.value && !form.category_id) {
        toast.error($t('select_category'));
        return;
    }
    if (!isAreaA.value && form.currently === 'no' && !form.end_date) {
        toast.error($t('end_date_required'));
        return;
    }
    if (!isAreaA.value && (!form.schedule || form.schedule === 0)) {
        toast.error($t('daily_hours_required'));
        return;
    }
    if (!isAreaA.value && form.overtime === null) {
        toast.error($t('overtime_hours_required'));
        return;
    }
    if (isAreaA.value && form.currently === 'yes' && (!form.schedule || form.schedule === 0)) {
        toast.error($t('daily_hours_required'));
        return;
    }
    if (!validateDates()) {
        return;
    }

    const selectedCategory = isAreaA.value ? props.categorias.find(c => c.id === form.category_id) : null;
    const transformedData = {
        area_id: form.area_id,
        name: form.name,
        description: form.description,
        init_date: form.init_date,
        end_date: form.currently === 'yes' ? null : form.end_date,
        schedule: isAreaA.value && form.currently === 'no' ? 0 : form.schedule,
        overtime: isAreaA.value ? null : form.overtime,
        currently: form.currently,
        pais_id: form.pais_id,
        category_id: isAreaA.value ? form.category_id : null,
        details: form.details || null,
        status: form.status,
        value: isAreaA.value && form.currently === 'no' && selectedCategory ? selectedCategory.value : horasCalculadas.value,
        belonging_id: form.belonging?.id ?? null,
    };

    console.debug('Datos transformados para enviar:', transformedData);

    const routeName = `skyfall.area-${props.area_name.toLowerCase()}`;
    if (isEdit.value) {
        form.transform(() => transformedData).put(route(routeName + '.update', props.definicion.id), {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                alerts.success($t, 'record_updated');
                router.visit(route(routeName + '.index'));
            },
            onError: (errors) => {
                alerts.error($t, 'form_error');
                Object.entries(errors).forEach(([field, error]) => toast.error(`${$t(field)}: ${error}`));
            },
        });
    } else {
        form.transform(() => transformedData).post(route(routeName + '.store'), {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                alerts.success($t, 'record_created');
                router.visit(route(routeName + '.index'));
            },
            onError: (errors) => {
                alerts.error($t, 'form_error');
                Object.entries(errors).forEach(([field, error]) => toast.error(`${$t(field)}: ${error}`));
            },
        });
    }
};
</script>

<template>
    <AppLayout :title="$t('Area') + ' ' + area_display_name">
        <div class="container mx-auto p-4 bg-neutral-3 dark:bg-neutral-1 min-h-screen">
            <div class="flex items-center gap-2 mb-6 bg-main-0 dark:bg-main-0 p-4 rounded-lg">
                <GoBackButton />
                <h1 class="text-xl font-bold text-neutral-0 dark:text-neutral-0">
                    {{ $t(isEdit ? 'edit_record' : 'new_record') }} - {{ $t('Area') }} {{ area_display_name }}
                </h1>
            </div>
            <form @submit.prevent="submit" class="bg-neutral-0 dark:bg-neutral-2 border border-neutral-4 dark:border-neutral-2 rounded-lg p-6 space-y-6 shadow-sm">
                <!-- Nombre -->
                <div>
                    <label for="name" class="block text-sm font-medium text-neutral-1 dark:text-neutral-0">{{ $t('name') }}</label>
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        class="mt-1 block w-full rounded-lg border-neutral-4 dark:border-neutral-2 bg-neutral-0 dark:bg-neutral-2 text-neutral-2 dark:text-neutral-0 focus:border-main-1 focus:ring focus:ring-main-1 focus:ring-opacity-50"
                        :class="{ 'border-secondary-3': form.errors.name }"
                        required
                    />
                    <p v-if="form.errors.name" class="mt-1 text-sm text-secondary-3 dark:text-secondary-3">{{ form.errors.name }}</p>
                </div>

                <!-- Descripción -->
                <div>
                    <label for="description" class="block text-sm font-medium text-neutral-1 dark:text-neutral-0">{{ $t('description') }}</label>
                    <textarea
                        id="description"
                        v-model="form.description"
                        class="mt-1 block w-full rounded-lg border-neutral-4 dark:border-neutral-2 bg-neutral-0 dark:bg-neutral-2 text-neutral-2 dark:text-neutral-0 focus:border-main-1 focus:ring focus:ring-main-1 focus:ring-opacity-50"
                        :class="{ 'border-secondary-3': form.errors.description }"
                    ></textarea>
                    <p v-if="form.errors.description" class="mt-1 text-sm text-secondary-3 dark:text-secondary-3">{{ form.errors.description }}</p>
                </div>

                <!-- Categoría (solo Área A) -->
                <div v-if="isAreaA">
                    <label for="category_id" class="block text-sm font-medium text-neutral-1 dark:text-neutral-0">{{ $t('category') }}</label>
                    <v-select
                        id="category_id"
                        v-model="form.category_id"
                        :options="props.categorias || []"
                        label="name"
                        :reduce="(option) => option.id"
                        :placeholder="$t('select_category')"
                        class="mt-1 w-full rounded-lg border-neutral-4 dark:border-neutral-2 bg-neutral-0 dark:bg-neutral-2 text-neutral-2 dark:text-neutral-0"
                        :class="{ 'border-secondary-3': form.errors.category_id }"
                        required
                    >
                        <template #no-options>
                            {{ $t('no_categories_available') }}
                        </template>
                    </v-select>
                    <p v-if="form.errors.category_id" class="mt-1 text-sm text-secondary-3 dark:text-secondary-3">{{ form.errors.category_id }}</p>
                    <p v-if="!props.categorias || props.categorias.length === 0" class="mt-1 text-sm text-secondary-2 dark:text-secondary-2">
                        {{ $t('no_categories_loaded') }}
                    </p>
                </div>

                <!-- Actualmente -->
                <div>
                    <label for="currently" class="block text-sm font-medium text-neutral-1 dark:text-neutral-0">{{ $t('currently') }}</label>
                    <select
                        id="currently"
                        v-model="form.currently"
                        class="mt-1 block w-full rounded-lg border-neutral-4 dark:border-neutral-2 bg-neutral-0 dark:bg-neutral-2 text-neutral-2 dark:text-neutral-0 focus:border-main-1 focus:ring focus:ring-main-1 focus:ring-opacity-50 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNiIgaGVpZ2h0PSIxNiIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9IiM4QkMzNEEiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIj48cG9seWxpbmUgcG9pbnRzPSI2IDkgMTIgMTUgMTggOSIvPjwvc3ZnPg==')] bg-no-repeat bg-[right_0.5rem_center] bg-[length:16px_16px]"
                        :class="{ 'border-secondary-3': form.errors.currently }"
                        required
                    >
                        <option value="yes">{{ $t('yes') }}</option>
                        <option value="no">{{ $t('no') }}</option>
                    </select>
                    <p v-if="form.errors.currently" class="mt-1 text-sm text-secondary-3 dark:text-secondary-3">{{ form.errors.currently }}</p>
                </div>

                <!-- Fechas -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="init_date" class="block text-sm font-medium text-neutral-1 dark:text-neutral-0">{{ $t('start_date') }}</label>
                        <input
                            id="init_date"
                            v-model="form.init_date"
                            type="date"
                            class="mt-1 block w-full rounded-lg border-neutral-4 dark:border-neutral-2 bg-neutral-0 dark:bg-neutral-2 text-neutral-2 dark:text-neutral-0 focus:border-main-1 focus:ring focus:ring-main-1 focus:ring-opacity-50"
                            :class="{ 'border-secondary-3': form.errors.init_date }"
                            :max="new Date().toISOString().split('T')[0]"
                            required
                            @change="calcularHoras"
                        />
                        <p v-if="form.errors.init_date" class="mt-1 text-sm text-secondary-3 dark:text-secondary-3">{{ form.errors.init_date }}</p>
                    </div>

                    <div v-if="form.currently === 'no'">
                        <label for="end_date" class="block text-sm font-medium text-neutral-1 dark:text-neutral-0">{{ $t('end_date') }}</label>
                        <input
                            id="end_date"
                            v-model="form.end_date"
                            type="date"
                            class="mt-1 block w-full rounded-lg border-neutral-4 dark:border-neutral-2 bg-neutral-0 dark:bg-neutral-2 text-neutral-2 dark:text-neutral-0 focus:border-main-1 focus:ring focus:ring-main-1 focus:ring-opacity-50"
                            :class="{ 'border-secondary-3': form.errors.end_date }"
                            :min="form.init_date"
                            :max="new Date().toISOString().split('T')[0]"
                            :required="!isAreaA"
                            @change="calcularHoras"
                        />
                        <p v-if="form.errors.end_date" class="mt-1 text-sm text-secondary-3 dark:text-secondary-3">{{ form.errors.end_date }}</p>
                    </div>
                </div>

                <!-- Horas diarias (no en Área A con currently="no") -->
                <div v-if="!isAreaA || form.currently === 'yes'">
                    <label for="schedule" class="block text-sm font-medium text-neutral-1 dark:text-neutral-0">{{ $t('daily_hours') }}</label>
                    <select
                        id="schedule"
                        v-model="form.schedule"
                        class="mt-1 block w-full rounded-lg border-neutral-4 dark:border-neutral-2 bg-neutral-0 dark:bg-neutral-2 text-neutral-2 dark:text-neutral-0 focus:border-main-1 focus:ring focus:ring-main-1 focus:ring-opacity-50 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNiIgaGVpZ2h0PSIxNiIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9IiM4QkMzNEEiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIj48cG9seWxpbmUgcG9pbnRzPSI2IDkgMTIgMTUgMTggOSIvPjwvc3ZnPg==')] bg-no-repeat bg-[right_0.5rem_center] bg-[length:16px_16px]"
                        :class="{ 'border-secondary-3': form.errors.schedule }"
                        required
                    >
                        <option value="0" disabled>{{ $t('select_hours') }}</option>
                        <option v-for="hour in [1, 2, 3, 4, 5, 6, 7, 8]" :key="hour" :value="hour">{{ hour }}</option>
                    </select>
                    <p v-if="form.errors.schedule" class="mt-1 text-sm text-secondary-3 dark:text-secondary-3">{{ form.errors.schedule }}</p>
                </div>

                <!-- Horas extras (solo áreas B-H) -->
                <div v-if="!isAreaA">
                    <label for="overtime" class="block text-sm font-medium text-neutral-1 dark:text-neutral-0">{{ $t('overtime_hours') }}</label>
                    <select
                        id="overtime"
                        v-model="form.overtime"
                        class="mt-1 block w-full rounded-lg border-neutral-4 dark:border-neutral-2 bg-neutral-0 dark:bg-neutral-2 text-neutral-2 dark:text-neutral-0 focus:border-main-1 focus:ring focus:ring-main-1 focus:ring-opacity-50 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNiIgaGVpZ2h0PSIxNiIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9IiM4QkMzNEEiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIj48cG9seWxpbmUgcG9pbnRzPSI2IDkgMTIgMTUgMTggOSIvPjwvc3ZnPg==')] bg-no-repeat bg-[right_0.5rem_center] bg-[length:16px_16px]"
                        :class="{ 'border-secondary-3': form.errors.overtime }"
                        required
                    >
                        <option v-for="hour in [0, 1, 2, 3, 4]" :key="hour" :value="hour">{{ hour }}</option>
                    </select>
                    <p v-if="form.errors.overtime" class="mt-1 text-sm text-secondary-3 dark:text-secondary-3">{{ form.errors.overtime }}</p>
                </div>

                <!-- Horas calculadas (no en Área A con currently="no") -->
                <div v-if="!isAreaA || form.currently === 'yes'">
                    <label for="calculated_hours" class="block text-sm font-medium text-neutral-1 dark:text-neutral-0">{{ $t('calculated_hours') }}</label>
                    <input
                        id="calculated_hours"
                        :value="horasCalculadas"
                        type="number"
                        class="mt-1 block w-full rounded-lg border-neutral-4 dark:border-neutral-2 bg-neutral-3 dark:bg-neutral-3 text-neutral-2 dark:text-neutral-1"
                        readonly
                    />
                    <p class="mt-1 text-sm text-neutral-2 dark:text-neutral-2">{{ horasSource }}</p>
                </div>

                <!-- Valor de la categoría (solo Área A con currently="no") -->
                <div v-if="isAreaA && form.currently === 'no'">
                    <label for="category_value" class="block text-sm font-medium text-neutral-1 dark:text-neutral-0">{{ $t('category_value') }}</label>
                    <input
                        id="category_value"
                        :value="props.categorias.find(c => c.id === form.category_id)?.value ?? 0"
                        type="number"
                        class="mt-1 block w-full rounded-lg border-neutral-4 dark:border-neutral-2 bg-neutral-3 dark:bg-neutral-3 text-neutral-2 dark:text-neutral-0"
                        readonly
                    />
                    <p class="mt-1 text-sm text-neutral-2 dark:text-neutral-2">{{ $t('category_value_reference') }}</p>
                </div>

                <!-- Belonging (incluye pais_id) -->
                <div>
                    <label for="belonging" class="block text-sm font-medium text-neutral-1 dark:text-neutral-0">{{ $t('belonging') }}</label>
                    <Belonging
                        v-model="form.belonging"
                        :paises="props.paises"
                        :country-id="Number(form.pais_id)"
                        :error="form.errors.belonging_id"
                        custom-class="w-full rounded-lg border-neutral-4 dark:border-neutral-2 bg-neutral-0 dark:bg-neutral-2 text-neutral-2 dark:text-neutral-0"
                        @update:country-id="form.pais_id = $event"
                    />
                    <p v-if="form.errors.belonging_id" class="mt-1 text-sm text-secondary-3 dark:text-secondary-3">{{ form.errors.belonging_id }}</p>
                </div>

                <!-- Detalles -->
                <div>
                    <label for="details" class="block text-sm font-medium text-neutral-1 dark:text-neutral-0">{{ $t('details') }}</label>
                    <textarea
                        id="details"
                        v-model="form.details"
                        class="mt-1 block w-full rounded-lg border-neutral-4 dark:border-neutral-2 bg-neutral-0 dark:bg-neutral-2 text-neutral-2 dark:text-neutral-0 focus:border-main-1 focus:ring focus:ring-main-1 focus:ring-opacity-50"
                        :class="{ 'border-secondary-3': form.errors.details }"
                        :placeholder="$t('details_placeholder')"
                        maxlength="1000"
                    ></textarea>
                    <p v-if="form.errors.details" class="mt-1 text-sm text-secondary-3 dark:text-secondary-3">{{ form.errors.details }}</p>
                </div>

                <!-- Botones -->
                <div class="flex justify-end gap-4">
                    <button
                        type="button"
                        @click="router.visit(route(`skyfall.area-${area_name.toLowerCase()}.index`))"
                        class="px-4 py-2 bg-neutral-2 dark:bg-neutral-2 text-neutral-0 dark:text-neutral-0 rounded-lg hover:bg-neutral-1 dark:hover:bg-neutral-1 transition-colors"
                    >
                        {{ $t('cancel') }}
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 bg-main-1 dark:bg-main-1 text-neutral-0 dark:text-neutral-0 rounded-lg hover:bg-main-0 dark:hover:bg-main-0 transition-colors"
                        :disabled="form.processing"
                    >
                        {{ $t(isEdit ? 'update' : 'add') }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<style scoped>
:deep(.v-select .vs__dropdown-toggle) {
    border-radius: 0.5rem;
    border: 1px solid #E5E5E5;
    background-color: #FFFFFF;
    color: #444444;
}
:deep(.v-select .vs__dropdown-toggle .vs__selected-options .vs__selected) {
    color: #444444;
}
:deep(.v-select .vs__dropdown-menu) {
    border: 1px solid #E5E5E5;
    background-color: #FFFFFF;
    color: #444444;
}
:deep(.v-select .vs__dropdown-option--highlight) {
    background-color: #3498DB;
    color: #FFFFFF;
}
:deep(.v-select .vs__dropdown-toggle .vs__actions::after) {
    content: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNiIgaGVpZ2h0PSIxNiIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9IiM4QkMzNEEiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIj48cG9seWxpbmUgcG9pbnRzPSI2IDkgMTIgMTUgMTggOSIvPjwvc3ZnPg==');
    margin-right: 0.5rem;
}
:deep(.v-select .vs__dropdown-toggle:focus-within) {
    border-color: #3498DB;
    box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.5);
}
:deep(.v-select.border-secondary-3 .vs__dropdown-toggle) {
    border-color: #FFA07A;
}

@media (max-width: 640px) {
    .space-y-6 > div {
        margin-bottom: 1.5rem;
    }
}
</style>