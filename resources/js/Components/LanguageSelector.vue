<template>
    <div class="relative">
        <select
            v-model="locale"
            @change="changeLanguage"
            class="border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
        >
            <option v-for="item in languages" :key="item.value" :value="item.value">
                {{ item.flag }} {{ $t(item.title) }}
            </option>
        </select>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { loadLanguageAsync } from 'laravel-vue-i18n';
import { getCurrentInstance } from 'vue';

// Inyecta i18n para traducciones
const instance = getCurrentInstance();
const $t = instance?.proxy?.$t ?? ((key) => key);

const locale = ref(usePage().props.locale);
const supportedLocales = usePage().props.supportedLocales;

const languages = [
    { title: 'Català', value: 'ca' }, 
    { title: 'Deutsch', value: 'de' },
    { title: 'English', value: 'en' },
    { title: 'Español', value: 'es' },
    { title: 'Euskara', value: 'eu' }, 
    { title: 'Français', value: 'fr' },
    { title: 'Galego', value: 'gl' },
    { title: 'Italiano', value: 'it' },
    { title: 'Português', value: 'pt' },
    { title: 'Русский', value: 'ru' },
];

const changeLanguage = () => {
    if (supportedLocales.includes(locale.value)) {
        router.post(route('language.switch'), { locale: locale.value }, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                loadLanguageAsync(locale.value);
                console.log('Language changed to:', locale.value);
            },
            onError: (errors) => {
                console.error('Error changing language:', errors);
            },
        });
    }
};
</script>