import { defineStore } from 'pinia';
import { router } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import axios from 'axios';

export const useAreaDefinicionStore = defineStore('areaDefinicion', {
    state: () => ({
        areas: {},
        loading: false,
        error: null,
    }),
    actions: {
        setAreaData(areaName, { definiciones, totales, categorias = [], pagination }) {
            console.debug('Seteando datos para área:', areaName, { definiciones, totales, categorias, pagination });
            this.areas[areaName] = {
                definiciones: definiciones || [],
                totales: totales || { propuestos: 0, verificados: 0, total: 0 },
                categorias: categorias || [],
                pagination: pagination || { current_page: 1, last_page: 1, per_page: 10, total: 0 },
            };
        },
        async fetchAreaData(areaName, page = 1) {
            this.loading = true;
            this.error = null;
            try {
                const response = await axios.get(`/area-${areaName.toLowerCase()}`, {
                    params: { page, per_page: this.areas[areaName]?.pagination.per_page || 10 },
                    headers: { Accept: 'application/json' },
                });
                console.debug('Respuesta de fetchAreaData para área:', areaName, response.data);
                if (!response.data.definiciones?.data || !Array.isArray(response.data.definiciones.data)) {
                    console.error('Respuesta inválida, no se encontraron definiciones válidas:', response.data);
                    this.error = `No se encontraron definiciones válidas para área ${areaName}`;
                    return;
                }
                this.setAreaData(areaName, {
                    definiciones: response.data.definiciones.data,
                    totales: response.data.totales || { propuestos: 0, verificados: 0, total: 0 },
                    categorias: response.data.categorias || [],
                    pagination: {
                        current_page: response.data.definiciones.current_page || 1,
                        last_page: response.data.definiciones.last_page || 1,
                        per_page: response.data.definiciones.per_page || 10,
                        total: response.data.definiciones.total || 0,
                    },
                });
                this.error = null;
            } catch (error) {
                console.error(`Error al obtener datos para área ${areaName}:`, error.response?.data || error.message);
                this.error = `Error al cargar datos para área ${areaName}: ${error.message}`;
                throw error;
            } finally {
                this.loading = false;
            }
        },
        async deleteDefinicion(id, areaName) {
            this.loading = true;
            this.error = null;
            const toast = useToast();

            try {
                const routeName = `skyfall.area-${areaName.toLowerCase()}.destroy`;
                console.debug('Eliminando definición:', { id, areaName, routeName });

                await router.delete(route(routeName, id), {
                    preserveState: true,
                    preserveScroll: true,
                    onSuccess: async () => {
                        await this.fetchAreaData(areaName, this.areas[areaName]?.pagination.current_page || 1);
                        toast.success('Registro eliminado correctamente');
                    },
                    onError: (errors) => {
                        this.error = Object.values(errors).join(', ') || 'Error al eliminar registro';
                        toast.error(this.error);
                        throw new Error(this.error);
                    },
                });
            } catch (error) {
                this.error = error.message || 'Error al eliminar registro';
                toast.error(this.error);
                throw error;
            } finally {
                this.loading = false;
            }
        },
        async verifyDefinicion(id, areaName) {
            this.loading = true;
            this.error = null;
            const toast = useToast();

            try {
                const routeName = `skyfall.area-${areaName.toLowerCase()}.verify`;
                console.debug('Verificando definición:', { id, areaName, routeName });

                await router.post(route(routeName, id), { status: '2' }, {
                    preserveState: true,
                    preserveScroll: true,
                    onSuccess: async () => {
                        await this.fetchAreaData(areaName, this.areas[areaName]?.pagination.current_page || 1);
                        toast.success('Registro verificado correctamente');
                    },
                    onError: (errors) => {
                        this.error = Object.values(errors).join(', ') || 'Error al verificar registro';
                        toast.error(this.error);
                        throw new Error(this.error);
                    },
                });
            } catch (error) {
                this.error = error.message || 'Error al verificar registro';
                toast.error(this.error);
                throw error;
            } finally {
                this.loading = false;
            }
        },
    },
    getters: {
        definicionesByArea: (state) => (areaId) => {
            const areaMap = {
                1: 'A',
                2: 'B',
                3: 'C',
                4: 'D',
                5: 'E',
                6: 'F',
                7: 'G',
                8: 'H',
            };
            if (!areaId || !areaMap[areaId]) {
                console.error('Invalid area_id:', areaId);
                state.error = 'Invalid area ID provided';
                return [];
            }
            const areaName = areaMap[areaId];
            console.debug('Obteniendo definiciones para area_id:', areaId, 'areaName:', areaName, 'definiciones:', state.areas[areaName]?.definiciones || []);
            return state.areas[areaName]?.definiciones || [];
        },
        totalesByArea: (state) => (areaId) => {
            const areaMap = {
                1: 'A',
                2: 'B',
                3: 'C',
                4: 'D',
                5: 'E',
                6: 'F',
                7: 'G',
                8: 'H',
            };
            if (!areaId || !areaMap[areaId]) {
                console.error('Invalid area_id:', areaId);
                state.error = 'Invalid area ID provided';
                return { propuestos: 0, verificados: 0, total: 0 };
            }
            const areaName = areaMap[areaId];
            return state.areas[areaName]?.totales || { propuestos: 0, verificados: 0, total: 0 };
        },
        categoriasByArea: (state) => (areaId) => {
            const areaMap = {
                1: 'A',
                2: 'B',
                3: 'C',
                4: 'D',
                5: 'E',
                6: 'F',
                7: 'G',
                8: 'H',
            };
            if (!areaId || !areaMap[areaId]) {
                console.error('Invalid area_id:', areaId);
                state.error = 'Invalid area ID provided';
                return [];
            }
            const areaName = areaMap[areaId];
            return state.areas[areaName]?.categorias || [];
        },
        paginationByArea: (state) => (areaId) => {
            const areaMap = {
                1: 'A',
                2: 'B',
                3: 'C',
                4: 'D',
                5: 'E',
                6: 'F',
                7: 'G',
                8: 'H',
            };
            if (!areaId || !areaMap[areaId]) {
                console.error('Invalid area_id:', areaId);
                state.error = 'Invalid area ID provided';
                return { current_page: 1, last_page: 1, per_page: 10, total: 0 };
            }
            const areaName = areaMap[areaId];
            return state.areas[areaName]?.pagination || { current_page: 1, last_page: 1, per_page: 10, total: 0 };
        },
        getDefinicionById: (state) => (id, areaName) => {
            return state.areas[areaName]?.definiciones.find(d => d.id === id) || null;
        },
        getPuntuacion3: () => (record) => {
            const p1 = Number(record.puntuacion_1 || record.total_puntuacion_1 || 0);
            const p2 = Number(record.puntuacion_2 || record.total_puntuacion_2 || 0);
            return p1 + p2;
        },
    },
});