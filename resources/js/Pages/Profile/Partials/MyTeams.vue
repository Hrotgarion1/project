<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';

const invitacionesRecibidas = ref([]);
const misInvitaciones = ref([]);

onMounted(async () => {
    try {
        const response = await axios.get('/invitaciones');
        invitacionesRecibidas.value = response.data.invitacionesRecibidas;
        misInvitaciones.value = response.data.misInvitaciones;
    } catch (error) {
        console.error('Error al cargar las invitaciones:', error);
    }
});

const aceptarInvitacion = async (invitacionId) => {
    try {
        await axios.post(`/invitaciones/${invitacionId}/aceptar`);
        const response = await axios.get('/invitaciones');
        invitacionesRecibidas.value = response.data.invitacionesRecibidas;
        misInvitaciones.value = response.data.misInvitaciones;
    } catch (error) {
        console.error('Error al aceptar la invitación:', error);
    }
};

const salirDeGrupo = async (invitacionId) => {
    try {
        await axios.delete(`/invitaciones/${invitacionId}`);
        const response = await axios.get('/invitaciones');
        invitacionesRecibidas.value = response.data.invitacionesRecibidas;
        misInvitaciones.value = response.data.misInvitaciones;
    } catch (error) {
        console.error('Error al salir del grupo:', error);
    }
};
</script>

<template>
    <div class="mt-10">  <h3 class="text-lg font-medium text-gray-900 dark:text-white">Mis Grupos e Invitaciones</h3>

        <div v-if="invitacionesRecibidas.length > 0" class="mt-4">
            <h4 class="text-md font-medium text-gray-700 dark:text-gray-300">Invitaciones Pendientes:</h4>
            <ul class="mt-2 space-y-2">
                <li v-for="invitacion in invitacionesRecibidas" :key="invitacion.id" class="border rounded p-2 flex justify-between items-center">
                    <span>Invitación de {{ invitacion.invitador.name }} ({{ invitacion.tipo_invitador }})</span>
                    <button @click="aceptarInvitacion(invitacion.id)" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                        Aceptar
                    </button>
                </li>
            </ul>
        </div>
        <div v-else class="mt-4">
            <p class="text-gray-500 dark:text-gray-400">No tienes invitaciones pendientes.</p>
        </div>

        <div v-if="misInvitaciones.length > 0" class="mt-4">
            <h4 class="text-md font-medium text-gray-700 dark:text-gray-300">Mis Grupos:</h4>
            <ul class="mt-2 space-y-2">
                <li v-for="invitacion in misInvitaciones" :key="invitacion.id" class="border rounded p-2 flex justify-between items-center">
                    <span>{{ invitacion.invitador.name }} ({{ invitacion.tipo_invitador }})</span>
                    <button @click="salirDeGrupo(invitacion.id)" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                        Salir
                    </button>
                </li>
            </ul>
        </div>
        <div v-else class="mt-4">
            <p class="text-gray-500 dark:text-gray-400">No perteneces a ningún grupo.</p>
        </div>
    </div> 
    
    </template>