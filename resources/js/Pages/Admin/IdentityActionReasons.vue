<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import GoBackButton from "@/Components/Common/GoBackButton.vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import { ref, getCurrentInstance } from "vue";
import { toast } from 'vue3-toastify';
import axios from "axios";
import alerts from "@/utils/alerts";

const instance = getCurrentInstance();
const $t = instance?.proxy.$t;
const page = usePage();

const props = defineProps({
    reasons: Object,
    search: String,
    perPage: Number,
});

const searchInput = ref(props.search || "");
const showModal = ref(false);
const isEditing = ref(false);
const isSubmitting = ref(false);
const isDeleting = ref(false);
const form = ref({
    id: null,
    action_type: "suspend",
    code: "",
    title: "",
    description: "",
});
const formErrors = ref({});

const actionTypeOptions = [
    { value: "suspend", label: $t("Suspend") },
    { value: "delete", label: $t("Delete") },
];

let searchTimeout = null;
const applySearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(
            route("identity-action-reasons.index"),
            { search: searchInput.value, per_page: props.perPage },
            {
                preserveState: true,
                preserveScroll: true,
                onError: (errors) => {
                    toast.error($t("Error searching reasons"));
                    console.error("Search error:", errors);
                },
            }
        );
    }, 300);
};

const openCreateModal = () => {
    form.value = {
        id: null,
        action_type: "suspend",
        code: "",
        title: "",
        description: "",
    };
    isEditing.value = false;
    formErrors.value = {};
    showModal.value = true;
};

const openEditModal = (reason) => {
    form.value = { ...reason };
    isEditing.value = true;
    formErrors.value = {};
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
};

const submitForm = async () => {

    if (
        !form.value.action_type ||
        !form.value.code ||
        !form.value.title ||
        !form.value.description
    ) {
        toast.error($t("All fields are required"));
        return;
    }
    
    const confirmMethod = isEditing.value
        ? alerts.confirmUpdate
        : alerts.confirmCreateReason;
    const result = await confirmMethod($t);
    if (!result.isConfirmed) return;

    try {
        isSubmitting.value = true;
        formErrors.value = {};
        const url = isEditing.value
            ? route("identity-action-reasons.update", form.value.id)
            : route("identity-action-reasons.store");
        const method = isEditing.value ? "put" : "post";

        const response = await axios[method](url, form.value);

        await alerts.success($t, response.data.message);
        closeModal();
        router.visit(route("identity-action-reasons.index"), {
            preserveState: false,
        });
    } catch (error) {
        if (error.response?.status === 422) {
            formErrors.value = error.response.data.errors;
            toast.error($t("Please check the form for errors"));
        } else {
            await alerts.error(
                $t,
                "Error: " + (error.response?.data?.message || error.message)
            );
        }
    } finally {
        isSubmitting.value = false;
    }
};

const deleteReason = async (reason) => {
    try {
        isDeleting.value = true;
        const result = await alerts.confirmDelete({ t: $t });
        if (!result.isConfirmed) return;

        const response = await axios.delete(
            route("identity-action-reasons.destroy", reason.id)
        );
        await alerts.success($t, response.data.message);
        router.visit(route("identity-action-reasons.index"), {
            preserveState: false,
        });
    } catch (error) {
        console.error("Delete error:", error);
        await alerts.error(
            $t,
            "Error deleting reason: " +
                (error.response?.data?.message || error.message)
        );
    } finally {
        isDeleting.value = false;
    }
};
</script>

<template>
    <AppLayout :title="$t('Identity Action Reasons')">
        <template #header>
            <div class="flex items-center space-x-2">
                <GoBackButton />
                <h1 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $t("Identity Action Reasons") }}
                </h1>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between mb-4 space-x-4">
                        <div class="flex-1">
                            <input
                                type="text"
                                v-model="searchInput"
                                @input="applySearch"
                                :placeholder="
                                    $t(
                                        'Search by code, title, or description...'
                                    )
                                "
                                class="border rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                        </div>
                        <button
                            @click="openCreateModal"
                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-500"
                            :disabled="isSubmitting"
                        >
                            {{ $t("Add Reason") }}
                            <span v-if="isSubmitting" class="ml-2 animate-spin"
                                >⌀</span
                            >
                        </button>
                    </div>

                    <div class="overflow-x-auto mb-6">
                        <table
                            class="min-w-full divide-y divide-gray-200 border border-gray-300"
                        >
                            <thead class="bg-gray-100">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        {{ $t("Action Type") }}
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        {{ $t("Code") }}
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        {{ $t("Title") }}
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        {{ $t("Description") }}
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        {{ $t("Actions") }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody v-if="props.reasons.data.length">
                                <tr
                                    v-for="reason in props.reasons.data"
                                    :key="reason.id"
                                    class="border-b border-gray-300 hover:bg-gray-100 transition"
                                >
                                    <td class="px-6 py-4">
                                        {{ $t(reason.action_type) }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ reason.code }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ reason.title }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ reason.description }}
                                    </td>
                                    <td class="px-6 py-4 flex space-x-2">
                                        <button
                                            @click="openEditModal(reason)"
                                            class="px-2 py-1 bg-blue-500 text-white rounded hover:bg-blue-600"
                                            :disabled="isSubmitting || isDeleting"
                                        >
                                            {{ $t("Edit") }}
                                        </button>
                                        <button
                                            @click="deleteReason(reason)"
                                            class="px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600"
                                            :disabled="isDeleting"
                                        >
                                            {{ $t("Delete") }}
                                            <span
                                                v-if="isDeleting"
                                                class="ml-2 animate-spin"
                                                >⌀</span
                                            >
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                            <tbody v-else>
                                <tr>
                                    <td
                                        colspan="5"
                                        class="text-center text-gray-500 py-4"
                                    >
                                        {{ $t("No reasons found") }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div
                        v-if="props.reasons.links.length > 3"
                        class="flex justify-center mt-4 space-x-2"
                    >
                        <Link
                            v-for="link in props.reasons.links"
                            :key="link.url"
                            :href="link.url || '#'"
                            v-html="link.label"
                            class="px-3 py-1 border rounded text-sm"
                            :class="{
                                'bg-blue-500 text-white': link.active,
                                'hover:bg-gray-200': link.url,
                                'cursor-not-allowed opacity-50': !link.url,
                            }"
                            :disabled="!link.url"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div
            v-if="showModal"
            class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50"
        >
            <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
                <h2 class="text-lg font-semibold mb-4">
                    {{ isEditing ? $t("Edit Reason") : $t("Add Reason") }}
                </h2>
                <form @submit.prevent="submitForm">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">
                            {{ $t("Action Type") }}
                        </label>
                        <select
                            v-model="form.action_type"
                            class="mt-1 block w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            :class="{
                                'border-red-500': formErrors.action_type,
                            }"
                            :disabled="isSubmitting"
                        >
                            <option
                                v-for="option in actionTypeOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </option>
                        </select>
                        <p
                            v-if="formErrors.action_type"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ formErrors.action_type[0] }}
                        </p>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">
                            {{ $t("Code") }}
                        </label>
                        <input
                            type="text"
                            v-model="form.code"
                            class="mt-1 block w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            :class="{ 'border-red-500': formErrors.code }"
                            :disabled="isSubmitting"
                        />
                        <p
                            v-if="formErrors.code"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ formErrors.code[0] }}
                        </p>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">
                            {{ $t("Title") }}
                        </label>
                        <input
                            type="text"
                            v-model="form.title"
                            class="mt-1 block w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            :class="{ 'border-red-500': formErrors.title }"
                            :disabled="isSubmitting"
                        />
                        <p
                            v-if="formErrors.title"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ formErrors.title[0] }}
                        </p>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">
                            {{ $t("Description") }}
                        </label>
                        <textarea
                            v-model="form.description"
                            class="mt-1 block w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            :class="{
                                'border-red-500': formErrors.description,
                            }"
                            :disabled="isSubmitting"
                        ></textarea>
                        <p
                            v-if="formErrors.description"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ formErrors.description[0] }}
                        </p>
                    </div>
                    <div class="flex justify-end space-x-2">
                        <button
                            type="button"
                            @click="closeModal"
                            class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400"
                            :disabled="isSubmitting"
                        >
                            {{ $t("Cancel") }}
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-500"
                            :disabled="isSubmitting"
                        >
                            {{ isEditing ? $t("Update") : $t("Create") }}
                            <span v-if="isSubmitting" class="ml-2 animate-spin"
                                >⌀</span
                            >
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.custom-select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='gray'%3E%3Cpath d='M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z'/%3E%3C/svg%3E");
    background-position: right 1rem center;
    background-repeat: no-repeat;
    background-size: 1.5em;
    padding-right: 2.5rem;
}

input:focus,
select:focus,
textarea:focus {
    border-color: #3b82f6;
}

.animate-spin {
    display: inline-block;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}
</style>