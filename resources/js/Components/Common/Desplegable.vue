<script setup>
import { ref, defineProps } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import NavLink from '@/Components/NavLink.vue';

// Acceder a los props de Inertia usando usePage
const page = usePage();
const invitationsCount = page.props.invitationsCount || 0;

const props = defineProps({
    buttonText: String,
    menuItems: Array,
    showingDesplegable: Boolean,
    type: String,
});

const menuOpen = ref(false);

const toggleMenu = () => {
    menuOpen.value = !menuOpen.value;
};

const redirectToRoute = (routeName, params = {}) => {
    if (routeName !== '#') {
        try {
            const href = params.slug || params.id ? route(routeName, params) : route(routeName);
            router.visit(href);
        } catch (error) {
            console.error('Error generating route:', error);
        }
    }
};
</script>

<template>
    <div class="mb-2">
        <button
            @click="toggleMenu"
            class="w-full flex items-center px-4 py-2 text-sm sm:text-base font-medium text-neutral-1 dark:text-neutral-0 bg-neutral-0 dark:bg-neutral-2 hover:bg-main-0 hover:text-neutral-0 dark:hover:bg-main-0 dark:hover:text-neutral-0 rounded-lg border border-neutral-4 dark:border-neutral-2 transition-colors duration-200"
        >
            <i class="fa fa-bars"></i>
            <span class="ms-3 truncate">{{ buttonText }}</span>
            <!-- Ícono de sobre para 'User panel' si hay invitaciones pendientes -->
            <i
                v-if="type === 'user' && invitationsCount > 0"
                class="fas fa-envelope w-4 h-4 ml-2 text-main-1 dark:text-main-1"
            ></i>
        </button>
        <ul v-if="menuOpen" class="space-y-1 mt-1 pl-4">
            <li v-for="(item, index) in menuItems" :key="index">
                <template v-if="item.route === '#'">
                    <a
                        @click="redirectToRoute(item.route)"
                        class="flex items-center px-4 py-2 text-sm sm:text-base truncate text-main-1 dark:text-main-1 hover:bg-neutral-3 dark:hover:bg-neutral-1 hover:text-neutral-1 dark:hover:text-neutral-0 rounded-md transition-colors duration-200"
                    >
                        {{ item.label }}
                    </a>
                </template>
                <template v-else>
                    <NavLink
                        :href="item.slug ? route(item.route, { slug: item.slug }) : (item.id ? route(item.route, { id: item.id }) : route(item.route))"
                        :active="route().current(item.route, item.slug ? { slug: item.slug } : (item.id ? { id: item.id } : {}))"
                        class="flex items-center px-4 py-2 text-sm sm:text-base truncate text-main-1 dark:text-main-1 hover:bg-neutral-3 dark:hover:bg-neutral-1 hover:text-neutral-1 dark:hover:text-neutral-0 rounded-md transition-colors duration-200"
                        :class="{ 'bg-main-1 text-neutral-0 dark:bg-main-1 dark:text-neutral-0': route().current(item.route, item.slug ? { slug: item.slug } : (item.id ? { id: item.id } : {})) }"
                    >
                        <span class="flex items-center">
                            {{ item.label }}
                        </span>
                    </NavLink>
                </template>
            </li>
        </ul>
    </div>
</template>