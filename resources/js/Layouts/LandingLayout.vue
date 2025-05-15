<script setup>
import { ref } from 'vue';
import { Head, Link} from '@inertiajs/vue3';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import NavLink from '@/Components/NavLink.vue';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import DarkMode from '@/Components/DarkMode.vue';
import SocialLinks from '@/Components/SocialLinks.vue';
import LogInOut from '@/Components/LogInOut.vue';
import Footer from '@/Components/Footer.vue';
import DropdownMoreOptions from '@/Components/DropdownMoreOptions.vue';
import LanguageSelector from '@/Components/LanguageSelector.vue';

defineProps({
    title: String,
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
});

const isDarkMode = ref(true);

const handleToggleDarkMode = (newMode) => {
  isDarkMode.value = newMode;
  document.documentElement.classList.toggle('dark', newMode);
};

const showingNavigationDropdown = ref(false);

const auth = ref(null)

</script>

<template>
    <div>
        <Head :title="title" />

        <Banner />
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            <nav class="bg-white dark:bg-gray-800 border-b border-primary-4 dark:border-gray-700">
                <!-- Primary Navigation Menu -->
                <div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                        <!-- Logo -->                     
                            <div class="shrink-0 flex items-center">
                                <Link :href="route('welcome')">
                                    <ApplicationMark class=" h-16 w-auto" />
                                </Link>
                            </div>
                        
                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                <NavLink :href="route('landing.students')" :active="route().current('landing.students')">
                                    {{$t('Students')}}
                                </NavLink>
                                <NavLink :href="route('landing.employees')" :active="route().current('landing.employees')">
                                    {{$t('Employee')}}
                                </NavLink>
                                <NavLink :href="route('landing.studycenters')" :active="route().current('landing.studycenters')">
                                    {{$t('Study centers')}}
                                </NavLink>
                                <NavLink :href="route('landing.companies')" :active="route().current('landing.companies')">
                                    {{$t('Companies')}}
                                </NavLink>
                                <NavLink :href="route('landing.socialentities')" :active="route().current('landing.socialentities')">
                                    {{$t('Social Entities')}}
                                </NavLink>
                                <DropdownMoreOptions class="mt-4" />
                                
                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ms-6">
                            <LanguageSelector />
                            
                            <!-- Settings Dropdown -->
                            <div class="ms-3 relative">
                                <Dropdown align="right" width="60">
                            <template #trigger>        
                                <button class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                                <svg
                                    class="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24">
                                    <path
                                        :class="{'hidden': showingNavigationDropdown, 'inline-flex': ! showingNavigationDropdown }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{'hidden': ! showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                                </button>

                            </template>

                                    <template #content>
                                        <div class="block px-4 py-2 text-xs text-gray-400">
                                            {{$t('Start here!')}}
                                        </div>
                                        <div class="block px-4 py-2 text-gray-400">
                                            <LogInOut/>
                                        </div>
                                        <div class="border-t border-gray-200 dark:border-gray-600" />
                                        <div class="block px-4 py-2 text-xs text-gray-400">
                                            {{$t('Share')}}
                                        </div>
                                        <div class="block px-4 py-2 text-gray-400">
                                            <SocialLinks class="ml-2"/>
                                        </div>
                                        <div class="border-t border-gray-200 dark:border-gray-600" />
                                        <div class="block px-4 py-2 text-gray-400">
                                            {{$t('Switch to light or dark mode')}}
                                            <DarkMode @toggleDarkMode="handleToggleDarkMode" />
                                        </div>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out" @click="showingNavigationDropdown = ! showingNavigationDropdown">
                                <svg
                                    class="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{'hidden': showingNavigationDropdown, 'inline-flex': ! showingNavigationDropdown }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{'hidden': ! showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
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
                <div :class="{'block': showingNavigationDropdown, 'hidden': ! showingNavigationDropdown}" class="sm:hidden">
                    <div class="pt-2 pb-3 space-y-1">
                        <ResponsiveNavLink :href="route('landing.students')" :active="route().current('landing.students')">
                            {{$t('Students')}}
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('landing.employees')" :active="route().current('landing.employees')">
                            {{$t('Employee')}}
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('landing.studycenters')" :active="route().current('landing.studycenters')">
                            {{$t('Study centers')}}
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('landing.companies')" :active="route().current('landing.companies')">
                            {{$t('Companies')}}
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('landing.socialentities')" :active="route().current('landing.socialentities')">
                            {{$t('Social Entities')}}
                        </ResponsiveNavLink>
                        <div class="border-t border-gray-200 dark:border-gray-600" />
                        <div class="block px-4 py-2 text-xs text-gray-400">
                                {{$t('Start here!')}}
                        </div>
                        <div class="block px-4 py-2 text-gray-400">
                            <LogInOut/>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-600" />
                        <div class="block px-4 py-2 text-xs text-gray-400">
                                {{$t('Share')}}
                        </div>
                        <div class="block px-4 py-2 text-gray-400">
                            <SocialLinks class="ml-2"/>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-600" />
                        <div class="flex justify-between px-4 py-2 text-xs text-gray-400">
                            <div class="mt-2">
                                {{$t('Switch to light or dark mode')}}
                            </div>
                            <DarkMode @toggleDarkMode="handleToggleDarkMode" />
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-600" />
                        <div class="flex justify-between px-4 py-2 text-xs text-gray-400">
                            <div class="mt-2">
                                {{$t('Language settings')}}
                            </div>  
                            <LanguageSelector />
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-600" />
                        <div class="flex justify-between px-4 py-2 text-xs text-gray-400 ">
                            <div class="mt-2">
                                {{$t('More options')}}
                            </div> 
                            <DropdownMoreOptions /> 
                        </div>                        
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
                        <div class="mt-3 space-y-1">
                            <!-- <ResponsiveNavLink :href="route('profile.show')" :active="route().current('profile.show')">
                                {{$t('Profile')}}
                            </ResponsiveNavLink> -->
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
        
    </div>
    <Footer />
</template>
