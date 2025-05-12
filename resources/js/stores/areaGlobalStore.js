import { defineStore } from 'pinia';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

export const useAreaGlobalStore = defineStore('areaGlobal', {
    state: () => ({
        sumas: {
            total_propuestos: 0,
            total_verificados: 0,
            totales: 0,
        },
        sumasPorBelonging: {},
        user: {
            name: '',
            profile_photo_url: '/default-profile.png',
        },
        additionalData: [],
        areas: {}, // Objeto con belonging_id como clave
    }),

    actions: {
        setSumas(sumas) {
            this.sumas = {
                total_propuestos: Number(sumas.total_propuestos) || 0,
                total_verificados: Number(sumas.total_verificados) || 0,
                totales: Number(sumas.totales) || 0,
            };
        },

        setSumasPorBelonging(sumasPorBelonging) {
            this.sumasPorBelonging = sumasPorBelonging;
        },

        setUser(user) {
            this.user = {
                name: user.name || '',
                profile_photo_url: user.profile_photo_url || '/default-profile.png',
            };
        },

        setAdditionalData(data) {
            this.additionalData = data;
        },

        setAreas(belongingId, areas) {
            this.areas[belongingId] = areas.map(area => ({
                name: area.name,
                area_display_name: area.area_display_name || 'Unknown', // Añadimos area_display_name
                total_propuestos: Number(area.total_propuestos) || 0,
                total_verificados: Number(area.total_verificados) || 0,
                totales: Number(area.totales) || 0,
            }));
        },

        areasByBelonging(belongingId) {
            return this.areas[belongingId] || [];
        },

        clearAreas() {
            this.areas = {};
        },

        async fetchGlobalData(belongingId = null) {
            try {
                const params = belongingId !== null ? { belonging_id: belongingId } : {};
                const response = await axios.get('/skyfall/area-global/sumas', { params });
                this.setSumas(response.data.global);
                this.setSumasPorBelonging(response.data.by_belonging);
                console.debug('Sumas actualizadas en store:', {
                    global: this.sumas,
                    by_belonging: this.sumasPorBelonging,
                });
            } catch (error) {
                console.error('Error fetching global data:', error);
                if (error.response?.status === 401) {
                    console.warn('No autorizado. Redirigiendo al login...');
                    router.visit('/login');
                } else if (error.response?.status === 404) {
                    console.error('Ruta /skyfall/area-global/sumas no encontrada');
                }
            }
        },

        async fetchAreas(belongingId) {
            try {
                const response = await axios.get(`/skyfall/area-global/areas/${belongingId}`, {
                    withCredentials: true,
                });
                // Asegurarse de que response.data sea un array de áreas
                const areas = Array.isArray(response.data) ? response.data : response.data.areas?.data || [];
                this.setAreas(belongingId, areas);
                console.debug('Áreas actualizadas en store:', { belongingId, areas });
                return areas;
            } catch (error) {
                console.error('Error fetching areas:', error);
                this.setAreas(belongingId, []);
                if (error.response?.status === 401) {
                    console.warn('No autorizado. Redirigiendo al login...');
                    router.visit('/login');
                } else if (error.response?.status === 404) {
                    console.error(`Ruta /skyfall/area-global/areas/${belongingId} no encontrada`);
                }
                return [];
            }
        },
    },
});