<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
</script>

<template>
    <AppLayout title="Type A Dashboard">
        <div>Type C Dashboard for Identity: {{ $page.props.identity_id }}</div>
    </AppLayout>
</template>