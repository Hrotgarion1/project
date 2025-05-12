<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import GoBackButton from "@/Components/Common/GoBackButton.vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import { ref, onMounted, getCurrentInstance } from "vue";
import { toast } from 'vue3-toastify';
import axios from "axios";
import alerts from "@/utils/alerts";

const instance = getCurrentInstance();
const $t = instance?.proxy.$t;

const props = defineProps({
    users: { type: Object, required: true },
    userCountsByRole: { type: Object, required: true },
    totalUsers: { type: Number, required: true },
});

const users = ref(props.users);
const roles = ref([]);
const selectedRoles = ref({});
const searchName = ref("");
const searchEmail = ref("");
const filterRole = ref("");
const filterCountry = ref("");
const loading = ref(true);

onMounted(async () => {
    await fetchRoles();
    users.value.data.forEach((user) => {
        selectedRoles.value[user.id] = user.roles.map((role) => role.name);
    });
    loading.value = false;
});

const fetchRoles = async () => {
    try {
        const response = await axios.get("/roles");
        roles.value = response.data;
    } catch (error) {
        toast.error($t("Error fetching roles"));
    }
};

const applyFilters = () => {
    router.get(
        route("users.index"),
        {
            search_name: searchName.value,
            search_email: searchEmail.value,
            role: filterRole.value,
            country: filterCountry.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            onSuccess: (page) => {
                users.value = page.props.users;
                users.value.data.forEach((user) => {
                    selectedRoles.value[user.id] = user.roles.map(
                        (role) => role.name
                    );
                });
            },
        }
    );
};

const deleteUser = async (id) => {
    const result = await alerts.confirmDelete({ t: $t });
    if (result.isConfirmed) {
        router.delete(route("users.destroy", id), {
            onSuccess: () => toast.success($t("User deleted successfully")),
            onFinish: () => applyFilters(),
        });
    }
};

const regeneratePasswordLink = async (email) => {
    const result = await alerts.confirmAction({
        t: $t,
        title: $t('Regenerate Password Link'),
        text: $t('Are you sure you want to regenerate the password reset link for this user?'),
        confirmButtonText: $t('Yes, regenerate'),
    });
    
    if (result.isConfirmed) {
        try {
            const response = await axios.post(route('users.regenerate-password-link', email));
            toast.success(response.data.message);
        } catch (error) {
            toast.error(error.response?.data?.error || $t('Failed to regenerate reset link'));
        }
    }
};
</script>

<template>
    <AppLayout :title="$t('Users List')">
        <template #header>
            <div class="flex items-center space-x-2">
                <GoBackButton />
                <h1 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $t("Users List") }}
                </h1>
            </div>
        </template>

        <div class="pt-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Contadores de Usuarios por Rol -->
                <div
                    class="mb-6 bg-white shadow overflow-hidden sm:rounded-lg p-4"
                >
                    <div class="text-center bg-stats-total mb-4">
                        <h2 class="text-3xl font-bold text-gray-800">
                            {{ props.totalUsers }}
                        </h2>
                        <p class="text-sm text-gray-500">
                            {{ $t("Total Users") }}
                        </p>
                    </div>
                    <h2 class="text-lg font-semibold mb-2">
                        {{ $t("Users by Role") }}
                    </h2>
                    <div
                        class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4"
                    >
                        <span
                            v-for="(count, role) in userCountsByRole"
                            :key="role"
                            class="text-sm text-gray-800 flex items-center hover:underline"
                        >
                            <span
                                class="font-medium px-1 py-1 text-blue-800 hover:underline"
                                >{{ $t(role) }}:</span
                            >
                            {{ count }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="pb-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="p-6 bg-white border-b border-gray-200 rounded-lg shadow"
                >
                    <div class="flex justify-between mb-4 space-x-4">
                        <input
                            v-model="searchName"
                            @input="applyFilters"
                            :placeholder="$t('Search by Name')"
                            class="border rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                        <input
                            v-model="searchEmail"
                            @input="applyFilters"
                            :placeholder="$t('Search by Email')"
                            class="border rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                        <select
                            v-model="filterRole"
                            @change="applyFilters"
                            class="border rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">{{ $t("All Roles") }}</option>
                            <option
                                v-for="role in roles"
                                :key="role.name"
                                :value="role.name"
                            >
                                {{ role.name }}
                            </option>
                        </select>
                        <select
                            v-model="filterCountry"
                            @change="applyFilters"
                            class="border rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">{{ $t("All Countries") }}</option>
                            <option
                                v-for="country in users.data
                                    .map((u) => u.pais)
                                    .filter(
                                        (v, i, a) =>
                                            a.findIndex(
                                                (c) => c?.id === v?.id
                                            ) === i
                                    )"
                                :key="country?.id"
                                :value="country?.id"
                            >
                                {{ country?.name }}
                            </option>
                        </select>
                        <Link
                            :href="route('users.create')"
                            class="px-3 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-500"
                            >{{ $t("Create User") }}</Link
                        >
                    </div>
                    <div v-if="loading" class="text-center py-4">
                        {{ $t("Loading...") }}
                    </div>
                    <div v-else class="overflow-x-auto">
                        <table
                            class="min-w-full divide-y divide-gray-200 border border-gray-300"
                        >
                            <thead class="bg-gray-100">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        {{ $t("Name") }}
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        {{ $t("Phone") }}
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        {{ $t("Email") }}
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        {{ $t("Country") }}
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        {{ $t("Roles") }}
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        {{ $t("Actions") }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="user in users.data"
                                    :key="user.id"
                                    class="border-b border-gray-300 hover:bg-gray-50 transition"
                                >
                                    <td class="px-6 py-4">{{ user.name }}</td>
                                    <td class="px-6 py-4">
                                        {{ user.phone || $t("N/A") }}
                                    </td>
                                    <td class="px-6 py-4">{{ user.email }}</td>
                                    <td class="px-6 py-4">
                                        {{ user.pais?.name || $t("N/A") }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span v-if="user.roles.length > 0">
                                            <span
                                                v-for="(
                                                    role, index
                                                ) in user.roles"
                                                :key="role.id"
                                                class="inline-block bg-green-200 text-gray-800 rounded-full px-2 py-1 text-xs font-semibold mr-1"
                                            >
                                                {{ role.name }}
                                            </span>
                                        </span>
                                        <span
                                            v-else
                                            class="text-gray-500 italic"
                                            >{{ $t("No roles assigned") }}</span
                                        >
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <Link
                                            :href="route('users.edit', user.id)"
                                            class="text-blue-600 bg-blue-200 rounded-full px-2 py-1 hover:underline mr-2"
                                            >{{ $t("Edit") }}</Link
                                        >
                                        <button
                                            @click="regeneratePasswordLink(user.email)"
                                            class="text-yellow-600 bg-yellow-200 rounded-full px-2 py-1 hover:underline mr-2"
                                        >
                                            {{ $t("Regen") }}
                                        </button>
                                        <button
                                            @click="deleteUser(user.id)"
                                            class="text-red-600 bg-red-200 rounded-full px-2 py-1 hover:underline"
                                        >
                                            {{ $t("Delete") }}
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div
                        v-if="!loading && users.links?.length > 3"
                        class="flex justify-center mt-4 space-x-2"
                    >
                        <Link
                            v-for="link in users.links"
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
.grid {
    grid-gap: 1rem;
}
.text-3xl {
    font-size: 1.875rem;
    line-height: 2.25rem;
}
.text-center {
    text-align: center;
}
.bg-stats-total {
    background-color: #f0f9ff; /* Fondo azul claro */
    border: 1px solid #e0f2fe; /* Borde suave */
    padding: 1rem;
    border-radius: 0.5rem;
}
</style>