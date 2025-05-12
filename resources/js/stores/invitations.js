import { defineStore } from 'pinia';
import axios from 'axios';
import { useToast } from 'vue-toastification';

export const useInvitationStore = defineStore('invitation', {
    state: () => ({
        invitations: [], // Lista de invitaciones (puede expandirse más adelante)
        loading: false,  // Estado de carga para mostrar spinners
        error: null,     // Mensaje de error si algo falla
    }),

    actions: {
        async sendInvitation(email, identityId, role) {
            const toast = useToast();
            this.loading = true;
            this.error = null;

            try {
                const response = await axios.post('/invitations', {
                    email,
                    identity_id: identityId,
                    role,
                });
                toast.success(response.data.message || 'Invitación enviada con éxito');
            } catch (error) {
                this.error = error.response?.data?.message || 'Error al enviar la invitación';
                toast.error(this.error);
            } finally {
                this.loading = false;
            }
        },

        // Podemos añadir más acciones como fetchInvitations o acceptInvitation más adelante
    },
});