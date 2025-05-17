<script setup>
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
</script>

<template>
    <div class="ms-3 relative">
        <Dropdown align="right" width="48">
            <template #trigger>
                <span class="inline-flex rounded-md">
                    <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-800 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-400 dark:hover:text-gray-300 focus:outline-none focus:bg-gray-50 dark:focus:bg-gray-700 active:bg-gray-50 dark:active:bg-gray-700 transition ease-in-out duration-150">
                        {{ $t('User options') }}
                        <svg class="ms-2 -me-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                </span>
            </template>

            <template #content>
                <DropdownLink :href="route('dashboard.employees')">
                    {{$t('Student or worker')}}
                </DropdownLink>
                <div class="border-t border-gray-200 dark:border-gray-600" />
                <DropdownLink :href="route('dashboard.entrepreneurs')">
                    {{$t('Entrepreneurs')}}
                </DropdownLink>
                <div class="border-t border-gray-200 dark:border-gray-600" /> 
                <DropdownLink :href="route('dashboard.selfemployed')">
                    {{$t('Self-employed')}}
                </DropdownLink>
                <div class="border-t border-gray-200 dark:border-gray-600" /> 
                <DropdownLink :href="route('dashboard.onlinecourses')">
                    {{$t('Online courses')}}
                </DropdownLink>
                <div class="border-t border-gray-200 dark:border-gray-600" /> 
                <DropdownLink :href="route('dashboard.studycenters')">
                    {{$t('Study centers')}}
                </DropdownLink>
                <div class="border-t border-gray-200 dark:border-gray-600" /> 
                <DropdownLink :href="route('dashboard.companies')">
                    {{$t('Companies')}}
                </DropdownLink>
                <div class="border-t border-gray-200 dark:border-gray-600" /> 
                <DropdownLink :href="route('dashboard.socialentities')">
                    {{$t('Social Entities')}}
                </DropdownLink>  
                <div class="border-t border-gray-200 dark:border-gray-600" />
                <DropdownLink :href="route('dashboard.investors')">
                    {{$t('Investors')}}
                </DropdownLink>   
                <div class="border-t border-gray-200 dark:border-gray-600" />
                <DropdownLink :href="route('dashboard.recruiters')">
                    {{$t('Recruiters')}}
                </DropdownLink> 
                    
            </template>
        </Dropdown>
    </div>
</template>

<style scoped>

</style>