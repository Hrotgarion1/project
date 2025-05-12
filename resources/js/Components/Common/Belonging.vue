<script setup>
import { ref, watch, computed, getCurrentInstance } from 'vue';
import { useBelongingStore } from '@/stores/belonging';
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import { debounce } from 'lodash';
import { useToast } from 'vue-toastification';

const props = defineProps({
    modelValue: [String, Object],
    paises: Array,
    countryId: Number,
    label: { type: String, default: 'Belonging' },
    customClass: { type: String, default: '' },
    fieldName: { type: String, default: 'belonging' },
    error: String,
});

const emit = defineEmits(['update:modelValue', 'update:countryId']);

const instance = getCurrentInstance();
const $t = instance?.proxy.$t;
const toast = useToast();
const belongingStore = useBelongingStore();
const selectedCountry = ref(props.countryId ? String(props.countryId) : '');
const selected = ref(props.modelValue);
const hasInitialCountry = computed(() => !!props.countryId && props.paises.some(pais => pais.id === props.countryId));

console.debug('Belonging.vue cargado', { props });

const updateCountry = () => {
    const countryId = selectedCountry.value ? Number(selectedCountry.value) : null;
    console.debug('Actualizando countryId:', countryId);
    emit('update:countryId', countryId);
    if (countryId) {
        belongingStore.search(countryId, '');
    } else {
        selected.value = null;
        emit('update:modelValue', null);
    }
};

const debouncedSearch = debounce((query) => {
    if (selectedCountry.value) {
        belongingStore.search(Number(selectedCountry.value), query);
    }
}, 300);

const handleInput = async (value) => {
    console.debug('handleInput:', value);
    if (!selectedCountry.value) {
        toast.error($t('select_country_first'));
        return;
    }

    if (typeof value === 'string' && value.trim()) {
        if (value.length > 100) {
            toast.error($t('belonging_name_too_long'));
            return;
        }

        const existing = belongingStore.options.find(opt => opt.name.toLowerCase() === value.trim().toLowerCase());
        if (existing) {
            selected.value = existing;
            console.debug('Belonging existente seleccionado:', existing);
            emit('update:modelValue', existing);
            toast.info($t('belonging_already_exists', { name: existing.name }));
            return;
        }

        try {
            const newBelonging = await belongingStore.addBelonging(value.trim(), Number(selectedCountry.value));
            selected.value = newBelonging;
            console.debug('Nuevo belonging creado:', newBelonging);
            emit('update:modelValue', newBelonging);
            toast.success($t('belonging_added', { name: newBelonging.name }));
        } catch (error) {
            console.error('Error adding belonging:', error);
            toast.error(error.response?.data?.message || $t('error_adding_belonging'));
        }
    } else {
        selected.value = value;
        console.debug('Belonging seleccionado:', value);
        emit('update:modelValue', value);
    }
};

watch(
    () => props.countryId,
    (newCountryId) => {
        console.debug('Cambio en countryId:', newCountryId);
        selectedCountry.value = newCountryId ? String(newCountryId) : '';
        if (newCountryId && props.paises.some((pais) => pais.id === newCountryId)) {
            belongingStore.search(newCountryId, '');
        } else {
            selected.value = null;
            emit('update:modelValue', null);
        }
    },
    { immediate: true }
);

watch(
    () => props.modelValue,
    (newValue) => {
        console.debug('Cambio en modelValue:', newValue);
        selected.value = newValue;
    },
    { deep: true }
);
</script>

<template>
    <div>
        <div>
            <label class="block text-sm font-medium text-gray-700">{{ $t('select_country') }}</label>
            <select
                v-model="selectedCountry"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                @change="updateCountry"
            >
                <option value="">{{ $t('choose_country') }}</option>
                <option v-for="pais in paises" :key="pais.id" :value="pais.id">{{ $t(pais.name) }}</option>
            </select>
        </div>
        <div v-if="selectedCountry" class="mt-4">
            <label class="block text-sm font-medium text-gray-700">{{ $t('select_belonging') }}</label>
            <vSelect
                v-model="selected"
                :options="belongingStore.options"
                label="name"
                :reduce="(option) => option"
                :placeholder="$t('search_belonging')"
                :filterable="false"
                :clearable="true"
                :loading="belongingStore.loading"
                :taggable="true"
                @search="debouncedSearch"
                @option:created="handleInput"
                @update:modelValue="emit('update:modelValue', $event)"
                :class="customClass"
            >
                <template #no-options>
                    {{ $t('no_options') }}
                </template>
            </vSelect>
            <span v-if="error" class="text-red-500 text-sm">{{ error }}</span>
        </div>
    </div>
</template>