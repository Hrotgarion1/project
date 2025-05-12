<script setup>
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import RoleCheckbox from '@/Components/Common/RoleCheckbox.vue';
import { ref, onMounted } from 'vue';
import axios from 'axios';

defineProps({
    form: { type: Object, required: true },
    updating: { type: Boolean, default: false },
    roles: { type: Array, required: true },
});

defineEmits(['submit']);

const countries = ref([]);
const searchCountry = ref('');
const filteredCountries = ref([]);

onMounted(async () => {
    try {
        const response = await axios.get('/countries'); // Ruta para obtener países
        if (Array.isArray(response.data)) {
            countries.value = response.data;
            filteredCountries.value = countries.value.slice(0, 5);
        } else {
            console.error('Invalid data format received from /countries:', response.data);
        }
    } catch (error) {
        console.error('Error fetching countries:', error);
    }
});

const filterCountries = () => {
    filteredCountries.value = countries.value
        .filter(country => country.name.toLowerCase().includes(searchCountry.value.toLowerCase()))
        .slice(0, 5);
};
</script>

<template>
    <FormSection @submitted="$emit('submit')">
        <template #title>{{ updating ? $t('Update User') : $t('Create a New User') }}</template>
        <template #description>{{ updating ? $t('Update the selected User') : $t('Create a New User from Scratch') }}</template>
        <template #form>
            <div class="col-span-6 sm:col-span-3">
                <InputLabel for="name" :value="$t('Name')" />
                <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" />
                <InputError :message="form.errors.name" class="mt-2" />
            </div>
            <div class="col-span-6 sm:col-span-3">
                <InputLabel for="email" :value="$t('Email')" />
                <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full" />
                <InputError :message="form.errors.email" class="mt-2" />
            </div>
            <div class="col-span-6 sm:col-span-3">
                <InputLabel for="phone" :value="$t('Phone')" />
                <TextInput id="phone" v-model="form.phone" type="text" class="mt-1 block w-full" />
                <InputError :message="form.errors.phone" class="mt-2" />
            </div>
            <div class="col-span-6 sm:col-span-3">
                <InputLabel for="address" :value="$t('Address')" />
                <TextInput id="address" v-model="form.address" type="text" class="mt-1 block w-full" />
                <InputError :message="form.errors.address" class="mt-2" />
            </div>
            <div class="col-span-6 sm:col-span-3">
                <InputLabel for="zip" :value="$t('Zip')" />
                <TextInput id="zip" v-model="form.zip" type="text" class="mt-1 block w-full" />
                <InputError :message="form.errors.zip" class="mt-2" />
            </div>
            <div class="col-span-6 sm:col-span-3">
                <InputLabel for="pais_id" :value="$t('Country')" />
                <div class="relative">
                    <input
                        v-model="searchCountry"
                        @input="filterCountries"
                        :placeholder="$t('Search Country')"
                        class="border rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                    <div
                        class="absolute z-10 w-full bg-white border rounded mt-1 max-h-40 overflow-y-auto"
                        v-if="searchCountry || filteredCountries.length"
                    >
                        <div
                            v-for="country in filteredCountries"
                            :key="country.id"
                            @click="() => {
                                form.pais_id = country.id;
                                searchCountry = country.name;
                                filteredCountries = [];
                            }"
                            class="px-3 py-2 hover:bg-gray-100 cursor-pointer"
                        >
                            {{ country.name }}
                        </div>
                    </div>
                </div>
                <InputError :message="form.errors.pais_id" class="mt-2" />
            </div>
            <div class="col-span-6">
                <InputLabel for="roles" :value="$t('Roles')" />
                <RoleCheckbox v-model="form.roles" :roles="roles" />
                <InputError :message="form.errors.roles" class="mt-2" />
            </div>
        </template>
        <template #actions>
            <PrimaryButton :disabled="form.processing">{{ updating ? $t('Update') : $t('Create') }}</PrimaryButton>
        </template>
    </FormSection>
</template>