import { defineStore } from 'pinia';
import axios from 'axios';

export const useBelongingStore = defineStore('belonging', {
    state: () => ({
        options: [],
        loading: false,
    }),
    actions: {
        async search(countryId, query) {
            this.loading = true;
            try {
                const response = await axios.get('/belongings/search', {
                    params: { country_id: countryId, query },
                });
                this.options = response.data;
            } catch (error) {
                console.error('Error searching belongings:', error);
            } finally {
                this.loading = false;
            }
        },
        async addBelonging(name, countryId) {
            try {
                const response = await axios.post('/belongings', { name, country_id: countryId });
                this.options.push(response.data);
                return response.data;
            } catch (error) {
                console.error('Error adding belonging:', error);
                throw error;
            }
        },
    },
});