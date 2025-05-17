<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import GoBackButton from "@/Components/Common/GoBackButton.vue";
import { ref, getCurrentInstance } from "vue";
import { toast } from 'vue3-toastify';
import axios from "axios";
import alerts from "@/utils/alerts"; // Importamos las alertas

const instance = getCurrentInstance();
const $t = instance?.proxy.$t;

const showModal = ref(false);
const roles = ref([]);
const rules = ref({});
const newRule = ref({
    role_type: "",
    is_inviter: true,
    view: "",
    controller: "",
    function: "",
    allowed: false,
});
const editingRule = ref(null);
const form = ref({ ...newRule.value });

const loadRules = async () => {
    try {
        const response = await axios.get("/suspension-rules/data");
        rules.value = response.data.rules || {};
        roles.value = response.data.roles || [];
        if (!form.value.role_type && roles.value.length) {
            form.value.role_type = roles.value[0].type;
        }
    } catch (error) {
        console.error("Error cargando reglas:", error);
        toast.error($t("Load Error"));
    }
};

const registerRule = async () => {
    try {
        await axios.post("/suspension-rules", form.value);
        await loadRules();
        toast.success($t("Register Success"));
        showModal.value = false;
        resetForm();
    } catch (error) {
        if (
            error.response?.status === 422 &&
            error.response.data.message.includes("Ya existe")
        ) {
            toast.error(error.response.data.message);
        } else {
            console.error("Error registrando regla:", error);
            toast.error($t("Register Error"));
        }
    }
};

const editRule = (rule) => {
    editingRule.value = { ...rule };
    form.value = { ...rule };
    showModal.value = true;
};

const updateRule = async () => {
    try {
        await axios.put(
            `/suspension-rules/${editingRule.value.id}`,
            form.value
        );
        await loadRules();
        toast.success($t("Update Success"));
        showModal.value = false;
        editingRule.value = null;
        resetForm();
    } catch (error) {
        if (
            error.response?.status === 422 &&
            error.response.data.message.includes("Ya existe")
        ) {
            toast.error(error.response.data.message);
        } else {
            console.error("Error actualizando regla:", error);
            toast.error($t("Update Error"));
        }
    }
};

const toggleAllowed = async (rule, event) => {
    event.preventDefault(); // Prevenimos el cambio inmediato del checkbox
    const originalValue = rule.allowed;
    const newValue = !rule.allowed;

    const result = await alerts.confirmToggleRule($t, rule.function, newValue);
    if (result.isConfirmed) {
        try {
            await axios.put(`/suspension-rules/${rule.id}`, {
                role_type: rule.role_type,
                is_inviter: rule.is_inviter,
                view: rule.view,
                controller: rule.controller,
                function: rule.function,
                allowed: newValue,
            });
            rule.allowed = newValue; // Actualizamos solo después de éxito
            toast.success($t("Update Success"));
        } catch (error) {
            console.error("Error actualizando regla:", error);
            rule.allowed = originalValue;
            toast.error($t("Update Error"));
        }
    }
};

const deleteRule = async (rule) => {
    const result = await alerts.confirmDeleteRule($t, rule.function);
    if (result.isConfirmed) {
        try {
            await axios.delete(`/suspension-rules/${rule.id}`);
            await loadRules();
            toast.success($t("Delete Success"));
        } catch (error) {
            console.error("Error eliminando regla:", error);
            toast.error($t("Delete Error"));
        }
    }
};

const resetForm = () => {
    form.value = {
        role_type: roles.value[0]?.type || "",
        is_inviter: true,
        view: "",
        controller: "",
        function: "",
        allowed: false,
    };
};

loadRules();
</script>

<template>
    <AppLayout :title="$t('Suspension Rules')">
        <template #header>
            <div class="flex items-center space-x-2">
                <GoBackButton />
                <h1 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $t("Suspension Rules") }}
                </h1>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="p-6 bg-white border-b border-gray-200">
                    <button
                        @click="
                            showModal = true;
                            editingRule = null;
                            resetForm();
                        "
                        class="px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-500 mb-4"
                    >
                        {{ $t("Register New Rule") }}
                    </button>

                    <!-- Modal para registrar o editar -->
                    <div
                        v-if="showModal"
                        class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center"
                    >
                        <div class="bg-white p-6 rounded-lg w-full max-w-md">
                            <h3 class="text-lg font-semibold mb-4">
                                {{
                                    editingRule
                                        ? $t("Edit Rule")
                                        : $t("Register View and Function")
                                }}
                            </h3>
                            <form
                                @submit.prevent="
                                    editingRule ? updateRule() : registerRule()
                                "
                            >
                                <div class="mb-4">
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                    >
                                        {{ $t("Role") }}
                                    </label>
                                    <select
                                        v-model="form.role_type"
                                        class="mt-1 block w-full border rounded px-3 py-2"
                                        required
                                    >
                                        <option
                                            v-for="role in roles"
                                            :key="role.type"
                                            :value="role.type"
                                        >
                                            {{ role.name }}
                                        </option>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label class="flex items-center">
                                        <input
                                            type="checkbox"
                                            v-model="form.is_inviter"
                                            class="mr-2"
                                        />
                                        {{ $t("Is Inviter") }}
                                    </label>
                                </div>
                                <div class="mb-4">
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                    >
                                        {{ $t("View") }}
                                    </label>
                                    <input
                                        v-model="form.view"
                                        :placeholder="$t('View Placeholder')"
                                        class="mt-1 block w-full border rounded px-3 py-2"
                                        required
                                    />
                                </div>
                                <div class="mb-4">
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                    >
                                        {{ $t("Controller") }}
                                    </label>
                                    <input
                                        v-model="form.controller"
                                        :placeholder="
                                            $t('Controller Placeholder')
                                        "
                                        class="mt-1 block w-full border rounded px-3 py-2"
                                    />
                                </div>
                                <div class="mb-4">
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                    >
                                        {{ $t("Function") }}
                                    </label>
                                    <input
                                        v-model="form.function"
                                        :placeholder="
                                            $t('Function Placeholder')
                                        "
                                        class="mt-1 block w-full border rounded px-3 py-2"
                                        required
                                    />
                                </div>
                                <div class="flex justify-end space-x-2">
                                    <button
                                        type="submit"
                                        class="px-3 py-2 bg-green-600 text-white rounded hover:bg-green-500"
                                    >
                                        {{
                                            editingRule
                                                ? $t("Update")
                                                : $t("Register")
                                        }}
                                    </button>
                                    <button
                                        type="button"
                                        @click="
                                            showModal = false;
                                            editingRule = null;
                                            resetForm();
                                        "
                                        class="px-3 py-2 bg-gray-500 text-white rounded hover:bg-gray-600"
                                    >
                                        {{ $t("Close") }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Tabla desplegable -->
                    <div v-if="Object.keys(rules).length" class="mt-6">
                        <div
                            v-for="role in Object.keys(rules)"
                            :key="role"
                            class="mb-4"
                        >
                            <details class="border rounded">
                                <summary
                                    class="px-4 py-2 bg-gray-100 cursor-pointer"
                                >
                                    {{
                                        rules[role][
                                            Object.keys(rules[role])[0]
                                        ][0].role_name
                                    }}
                                </summary>
                                <div
                                    v-for="view in Object.keys(rules[role])"
                                    :key="view"
                                    class="ml-4 mt-2"
                                >
                                    <details class="border rounded">
                                        <summary
                                            class="px-4 py-2 bg-gray-50 cursor-pointer"
                                        >
                                            {{ view }}
                                        </summary>
                                        <table
                                            class="min-w-full divide-y divide-gray-200 border border-gray-300 mt-2 ml-4"
                                        >
                                            <thead class="bg-gray-100">
                                                <tr>
                                                    <th
                                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                                    >
                                                        {{ $t("Type") }}
                                                    </th>
                                                    <th
                                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                                    >
                                                        {{ $t("Function") }}
                                                    </th>
                                                    <th
                                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                                    >
                                                        {{ $t("Controller") }}
                                                    </th>
                                                    <th
                                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                                    >
                                                        {{ $t("Allowed") }}
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
                                                    v-for="rule in rules[role][
                                                        view
                                                    ]"
                                                    :key="rule.id"
                                                    class="border-b border-gray-300 hover:bg-gray-50"
                                                >
                                                    <td class="px-6 py-4">
                                                        {{
                                                            rule.is_inviter
                                                                ? $t("Inviter")
                                                                : $t("Invitee")
                                                        }}
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        {{ rule.function }}
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        {{
                                                            rule.controller ||
                                                            "N/A"
                                                        }}
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        <label class="switch">
                                                            <input
                                                                type="checkbox"
                                                                :checked="
                                                                    rule.allowed
                                                                "
                                                                @click="
                                                                    toggleAllowed(
                                                                        rule,
                                                                        $event
                                                                    )
                                                                "
                                                            />
                                                            <span
                                                                class="slider"
                                                            ></span>
                                                        </label>
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        <button
                                                            @click="
                                                                editRule(rule)
                                                            "
                                                            class="text-blue-600 hover:underline mr-2"
                                                        >
                                                            {{ $t("Edit") }}
                                                        </button>
                                                        <button
                                                            @click="
                                                                deleteRule(rule)
                                                            "
                                                            class="text-red-600 hover:underline"
                                                        >
                                                            {{ $t("Delete") }}
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </details>
                                </div>
                            </details>
                        </div>
                    </div>
                    <div v-else class="text-center text-gray-500 py-4">
                        {{ $t("No Rules") }}
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.switch {
    position: relative;
    display: inline-block;
    width: 40px;
    height: 20px;
}
.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}
.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: 0.4s;
    border-radius: 20px;
}
.slider:before {
    position: absolute;
    content: "";
    height: 16px;
    width: 16px;
    left: 2px;
    bottom: 2px;
    background-color: white;
    transition: 0.4s;
    border-radius: 50%;
}
input:checked + .slider {
    background-color: #28a745;
}
input:checked + .slider:before {
    transform: translateX(20px);
}
</style>
