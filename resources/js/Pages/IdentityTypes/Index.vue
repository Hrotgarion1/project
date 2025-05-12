<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import GoBackButton from "@/Components/Common/GoBackButton.vue";
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  identityTypes: Array,
});

</script>

<template>
  <AppLayout :title="$t('Identity Types')">
    <template #header>
      <div class="flex items-center space-x-2">
        <GoBackButton />
                <h1 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $t("Identity Types") }}
                </h1>
            </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <Link :href="route('identity-types.create')" class="mb-4 inline-block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
              {{ $t('Create New') }}
            </Link>
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Required Documents</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Terms and Conditions</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="identityType in props.identityTypes" :key="identityType.id">
                  <td class="px-6 py-4 whitespace-nowrap">{{ identityType.type }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <ul>
                      <li v-for="doc in identityType.required_documents" :key="doc.name">{{ doc.name }} ({{ doc.type }})</li>
                    </ul>
                  </td>
                  <td class="px-6 py-4">{{ identityType.terms_and_conditions }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <Link :href="route('identity-types.edit', identityType.id)" class="text-blue-600 hover:text-blue-900 mr-2">Edit</Link>
                    <Link :href="route('identity-types.destroy', identityType.id)" method="delete" as="button" class="text-red-600 hover:text-red-900">Delete</Link>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>