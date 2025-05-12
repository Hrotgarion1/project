<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import DarkMode from '@/Components/DarkMode.vue';
import SocialLinks from '@/Components/SocialLinks.vue';
import LanguageSelector from '@/Components/LanguageSelector.vue';
import Sidebar from '@/Components/Common/Sidebar.vue';

defineProps({
    title: String,
});

const isDarkMode = ref(true);

const handleToggleDarkMode = (newMode) => {
    isDarkMode.value = newMode;
    document.documentElement.classList.toggle('dark', newMode);
};

const showingNavigationDropdown = ref(false);

const logout = () => {
    router.post(route('logout'));
};

const showingSidebar = ref(false);

const toggleSidebar = () => {
    showingSidebar.value = !showingSidebar.value;
};

const updateShowingSidebar = (value) => {
    showingSidebar.value = value;
};
</script>

<template>
    <div>
        <Head :title="title" />

        <Banner />

        <div class="min-h-screen bg-neutral-3 dark:bg-neutral-1">
            <nav
                class="bg-neutral-0 dark:bg-neutral-2 border-b border-neutral-4 dark:border-neutral-2 shadow-sm z-50"
            >
                <!-- Primary Navigation Menu -->
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo and Sidebar Toggle -->
                            <div class="shrink-0 flex items-center">
                                <button
                                    @click="toggleSidebar"
                                    class="mr-2 inline-flex items-center justify-center p-2 bg-main-1 dark:bg-main-1 text-neutral-0 dark:text-neutral-0 rounded-lg hover:bg-main-0 dark:hover:bg-main-0 focus:outline-none focus:bg-main-0 focus:text-neutral-0 transition-colors duration-200"
                                    aria-label="Toggle sidebar"
                                >
                                    <svg
                                        class="h-6 w-6"
                                        stroke="currentColor"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            :class="{
                                                hidden: showingSidebar,
                                                'inline-flex': !showingSidebar,
                                            }"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M4 6h16M4 12h16M4 18h16"
                                        />
                                        <path
                                            :class="{
                                                hidden: !showingSidebar,
                                                'inline-flex': showingSidebar,
                                            }"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                </button>
                                <Link
                                    :href="route('welcome')"
                                    :active="route().current('welcome')"
                                >
                                    <ApplicationMark class="h-16 w-auto" />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div
                                class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex"
                            >
                                <NavLink
                                    :href="route('dashboard')"
                                    :active="route().current('dashboard')"
                                >
                                    {{ $t('Dashboard') }}
                                </NavLink>
                           
                            </div>
                            <div
                                v-if="
                                    $page.props.user &&
                                    $page.props.user.permissions &&
                                    $page.props.user.permissions.includes(
                                        'ver panel admin'
                                    )
                                "
                                class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex"
                            >
                                <NavLink
                                    :href="route('users.index')"
                                    :active="route().current('users.*')"
                                >
                                    {{ $t('Users List') }}
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ms-6">
                            <LanguageSelector />
                            <!-- Settings Dropdown -->
                            <div
                                v-if="$page.props.auth && $page.props.auth.user"
                                class="ms-3 relative"
                            >
                                <Dropdown align="right" width="60">
                                    <template #trigger>
                                        <button
                                            v-if="
                                                $page.props.jetstream &&
                                                $page.props.jetstream
                                                    .managesProfilePhotos
                                            "
                                            class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-neutral-4 dark:focus:border-neutral-2 transition"
                                        >
                                            <img
                                                class="h-8 w-8 rounded-full object-cover"
                                                :src="
                                                    $page.props.auth.user
                                                        .profile_photo_url
                                                "
                                                :alt="$page.props.auth.user.name"
                                            />
                                        </button>

                                        <span v-else class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-neutral-2 dark:text-neutral-0 bg-neutral-0 dark:bg-neutral-2 hover:text-main-1 dark:hover:text-main-1 focus:outline-none focus:bg-neutral-3 dark:focus:bg-neutral-1 active:bg-neutral-3 dark:active:bg-neutral-1 transition ease-in-out duration-200"
                                            >
                                                {{ $page.props.auth.user.name }}

                                                <svg
                                                    class="ms-2 -me-0.5 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke-width="1.5"
                                                    stroke="currentColor"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        d="M19.5 8.25l-7.5 7.5-7.5-7.5"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <div
                                            class="block px-4 py-2 text-xs text-neutral-2 dark:text-neutral-0"
                                        >
                                            {{ $t('Share') }}
                                        </div>
                                        <SocialLinks class="ml-2" />
                                        <div
                                            class="border-t border-neutral-4 dark:border-neutral-2"
                                        />
                                        <div
                                            class="block px-4 py-2 text-xs text-neutral-2 dark:text-neutral-0"
                                        >
                                            {{
                                                $t('Switch to light or dark mode')
                                            }}
                                        </div>
                                        <DarkMode
                                            @toggleDarkMode="handleToggleDarkMode"
                                        />
                                        <div
                                            class="border-t border-neutral-4 dark:border-neutral-2"
                                        />
                                        <!-- Account Management -->
                                        <div
                                            class="block px-4 py-2 text-xs text-neutral-2 dark:text-neutral-0"
                                        >
                                            {{ $t('Manage Account') }}
                                        </div>
                                        <DropdownLink
                                            :href="route('profile.show')"
                                        >
                                            {{ $t('Profile') }}
                                        </DropdownLink>
                                        <div
                                            class="border-t border-neutral-4 dark:border-neutral-2"
                                        />
                                        <!-- Authentication -->
                                        <form @submit.prevent="logout">
                                            <DropdownLink as="button">
                                                <div class="text-red-400">
                                                    {{ $t('Log out') }}
                                                </div>
                                            </DropdownLink>
                                        </form>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                class="inline-flex items-center justify-center p-2 rounded-md text-neutral-2 dark:text-neutral-0 hover:text-main-1 dark:hover:text-main-1 hover:bg-neutral-3 dark:hover:bg-neutral-1 focus:outline-none focus:bg-neutral-3 dark:focus:bg-neutral-1 focus:text-main-1 dark:focus:text-main-1 transition duration-200 ease-in-out"
                                @click="
                                    showingNavigationDropdown =
                                        !showingNavigationDropdown
                                "
                            >
                                <svg
                                    class="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex':
                                                !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex': showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="sm:hidden"
                >
                    <div class="pt-2 pb-3 space-y-1">
                        <ResponsiveNavLink
                            :href="route('dashboard')"
                            :active="route().current('dashboard')"
                        >
                            {{ $t('Dashboard') }}
                        </ResponsiveNavLink>
                    </div>

                    <div
                        v-if="
                            $page.props.user &&
                            $page.props.user.permissions &&
                            $page.props.user.permissions.includes(
                                'ver panel admin'
                            )
                        "
                        class="pt-2 pb-3 space-y-1"
                    >
                        <ResponsiveNavLink
                            :href="route('users.index')"
                            :active="route().current('users.*')"
                        >
                            {{ $t('Users List') }}
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div
                        v-if="$page.props.auth && $page.props.auth.user"
                        class="pt-4 pb-1 border-t border-neutral-4 dark:border-neutral-2"
                    >
                        <div class="flex items-center px-4">
                            <div
                                v-if="
                                    $page.props.jetstream &&
                                    $page.props.jetstream.managesProfilePhotos
                                "
                                class="shrink-0 me-3"
                            >
                                <img
                                    class="h-10 w-10 rounded-full object-cover"
                                    :src="
                                        $page.props.auth.user.profile_photo_url
                                    "
                                    :alt="$page.props.auth.user.name"
                                />
                            </div>

                            <div>
                                <div
                                    class="font-medium text-base text-neutral-2 dark:text-neutral-0"
                                >
                                    {{ $page.props.auth.user.name }}
                                </div>
                                <div
                                    class="font-medium text-sm text-neutral-4 dark:text-neutral-2"
                                >
                                    {{ $page.props.auth.user.email }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <div
                                class="border-t border-neutral-4 dark:border-neutral-2"
                            />
                            <div
                                class="flex justify-between px-4 py-2 text-xs text-neutral-2 dark:text-neutral-0"
                            >
                                <div class="mt-2">
                                    {{ $t('Language settings') }}
                                </div>
                                <LanguageSelector />
                            </div>
                            <div
                                class="border-t border-neutral-4 dark:border-neutral-2"
                            />
                            <div
                                class="flex justify-between px-4 py-2 text-xs text-neutral-2 dark:text-neutral-0"
                            >
                                <div class="mt-2">
                                    {{ $t('More options') }}
                                </div>
                                
                            </div>
                            <div
                                class="border-t border-neutral-4 dark:border-neutral-2"
                            />
                            <div
                                class="block px-4 py-2 text-xs text-neutral-2 dark:text-neutral-0"
                            >
                                {{ $t('Share') }}
                            </div>
                            <SocialLinks class="ml-4" />
                            <div
                                class="border-t border-neutral-4 dark:border-neutral-2"
                            />
                            <div
                                class="block px-4 py-2 text-xs text-neutral-2 dark:text-neutral-0"
                            >
                                {{ $t('Switch to light or dark mode') }}
                            </div>
                            <DarkMode
                                class="ml-2"
                                @toggleDarkMode="handleToggleDarkMode"
                            />
                            <div
                                class="border-t border-neutral-4 dark:border-neutral-2"
                            />
                            <div
                                class="block px-4 py-2 text-xs text-neutral-2 dark:text-neutral-0"
                            >
                                {{ $t('Manage Account') }}
                            </div>
                            <ResponsiveNavLink
                                :href="route('profile.show')"
                                :active="route().current('profile.show')"
                            >
                                {{ $t('Profile') }}
                            </ResponsiveNavLink>

                            <!-- Authentication -->
                            <form method="POST" @submit.prevent="logout">
                                <ResponsiveNavLink as="button">
                                    <div class="text-red-400">
                                        {{ $t('Log out') }}
                                    </div>
                                </ResponsiveNavLink>
                            </form>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Contenedor para contenido desplazado -->
            <div
                class="transition-all duration-300"
                :class="{ 'ml-48 sm:ml-64': showingSidebar }"
            >
                <!-- Componente Sidebar -->
                <Sidebar
                    :showingSidebar="showingSidebar"
                    @update:showingSidebar="updateShowingSidebar"
                />

                <!-- Page Heading -->
                <header
                    v-if="$slots.header"
                    class="bg-neutral-0 dark:bg-neutral-2 shadow-sm"
                >
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        <slot name="header" />
                    </div>
                </header>

                <!-- Page Content -->
                <main>
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>