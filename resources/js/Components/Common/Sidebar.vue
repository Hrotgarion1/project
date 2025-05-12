<script setup>
import { defineProps, defineEmits } from 'vue';
import Desplegable from '@/Components/Common/Desplegable.vue';
import SubDesplegable from '@/Components/Common/SubDesplegable.vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const invitationsCount = page.props.invitationsCount || 0;

// Lógica de prioridad para el rol (basada en roles crudos)
const getPriorityRoleIndex = (roles) => {
    const priorityOrder = [
        'tipo A2', 'tipo B2', 'tipo C2', // Nivel 2 tiene prioridad
        'tipo A1', 'tipo B1', 'tipo C1', // Nivel 1
        'tipo A', 'tipo B', 'tipo C',   // Propietarios
    ];
    for (let role of priorityOrder) {
        const index = roles.indexOf(role);
        if (index !== -1) return index;
    }
    return 0; // Primer rol por defecto si no hay coincidencia
};

// Combinar rutas de todos los roles
const getCombinedMenuItems = (roles, identityMenus, identitySlug) => {
    let combinedItems = [];
    roles.forEach(role => {
        const items = identityMenus[role]?.map(item => ({
            label: item.label,
            route: item.route,
            slug: identitySlug,
        })) || [];
        combinedItems = [...combinedItems, ...items];
    });
    // Eliminar duplicados por 'label'
    return Array.from(new Map(combinedItems.map(item => [item.label, item])).values());
};

const props = defineProps({
    showingSidebar: Boolean,
});

const emit = defineEmits();

const toggleSidebar = () => {
    emit('update:showingSidebar', !props.showingSidebar);
};
</script>

<template>
    <aside
        :class="`fixed top-35 left-0 z-40 w-48 sm:w-64 h-screen transition-transform bg-neutral-0 dark:bg-neutral-2 border-r border-neutral-4 dark:border-neutral-2 shadow-sm ${!showingSidebar ? '-translate-x-full' : ''}`"
        aria-label="Sidebar"
    >
        <div class="h-full px-3 pb-4 overflow-y-auto">
            <!-- Elemento de prueba para confirmar renderizado -->
            <div v-if="!$page.props.user" class="p-4 text-red-500">
                Error: $page.props.user no está definido
            </div>
            <div>
                <!-- Admin Panel -->
                <Desplegable
                    v-if="$page.props.user.permissions && $page.props.user.permissions.includes('Admin panel')"
                    :buttonText="$t('Admin Panel')"
                    :menuItems="[
                        { label: $t('Users List'), route: 'users.index' },
                        { label: $t('Suspension rules'), route: 'suspension.rules' },
                        { label: $t('Countries'), route: 'paises.index' },
                        { label: $t('Categories'), route: 'categories.index' },
                        { label: $t('Identities Panel'), route: 'identity-panel.index' },
                        { label: $t('Identities Types'), route: 'identity-types.index' },
                        { label: $t('Identities Action Reasons'), route: 'identity-action-reasons.index' }
                    ]"
                    :type="'admin'"
                />

                <!-- Identidades Aprobadas (Propietario) -->
                <template v-for="(identities, type) in $page.props.user.approvedIdentities" :key="type">
                    <Desplegable
                        v-for="identity in identities"
                        :key="identity.id"
                        :buttonText="identity.name"
                        :menuItems="$page.props.identityMenus[type]?.map(item => ({
                            label: $t(item.label),
                            route: item.route,
                            slug: identity.slug
                        })) || []"
                        :type="type"
                    />
                </template>

                <!-- Identidades Invitadas -->
                <template v-for="(identities, type) in $page.props.user.invitedIdentities" :key="'invited-' + type">
                    <Desplegable
                        v-for="identity in identities"
                        :key="identity.id"
                        :buttonText="`${identity.name} (${identity.roleNames[getPriorityRoleIndex(identity.roles)]})`"
                        :menuItems="getCombinedMenuItems(identity.roles, $page.props.identityMenus, identity.slug).map(item => ({
                            label: $t(item.label),
                            route: item.route,
                            slug: item.slug
                        }))"
                        :type="type"
                    />
                </template>

                <!-- Partner Panel -->
                <Desplegable
                    v-if="$page.props.user.permissions && $page.props.user.permissions.includes('Partner panel')"
                    :buttonText="$t('Partners')"
                    :menuItems="[
                        { label: $t('List'), route: '#' },
                        { label: $t('Dashboard'), route: '#' }
                    ]"
                    :type="'partner'"
                />

                <!-- User Panel -->
                <Desplegable
                    v-if="$page.props.user.permissions && $page.props.user.permissions.includes('Users panel')"
                    :buttonText="$t('User panel')"
                    :menuItems="[
                        { label: $t('My Invitations'), route: 'invitations.received' },
                        { label: $t('My Identities'), route: 'my-requests.index' }
                    ]"
                    :type="'user'"
                />
                <!-- Subdesplegable para Areas Views -->
                <SubDesplegable
                    v-if="$page.props.user.permissions && $page.props.user.permissions.includes('Users panel')"
                    :buttonText="$t('Areas Views')"
                    :menuItems="$page.props.areas.map(area => ({
                        label: area.name,
                        route: area.route
                    }))"
                />
            </div>
        </div>
    </aside>
</template>