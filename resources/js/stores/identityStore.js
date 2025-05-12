import { defineStore } from 'pinia';
import axios from 'axios';

export const useIdentityStore = defineStore('identity', {
  state: () => ({
    selectedIdentities: [],
    handledIdentities: [],
    allIdentities: [],
  }),
  actions: {
    toggleSelection(identityId) {
      const index = this.selectedIdentities.indexOf(identityId);
      if (index === -1) {
        this.selectedIdentities.push(identityId);
      } else {
        this.selectedIdentities.splice(index, 1);
      }
    },
    clearSelection() {
      this.selectedIdentities = [];
    },
    setHandledIdentities(identities) {
      this.handledIdentities = identities;
    },
    setAllIdentities(identities) {
      this.allIdentities = identities;
    },
    getSelectedSlugs() {
      const allIdentities = [...this.handledIdentities, ...this.allIdentities];
      return this.selectedIdentities
        .map(id => {
          const identity = allIdentities.find(i => i.id === id);
          return identity ? identity.slug : null;
        })
        .filter(slug => slug);
    },
    async bulkReassign(handlerId) {
      if (!this.selectedIdentities.length) return;
      try {
        const response = await axios.post(route('identities.bulk-reassign'), {
          identity_slugs: this.getSelectedSlugs(), // Use slugs
          handled_by: handlerId,
        });
        this.handledIdentities = response.data.handledIdentities;
        this.allIdentities = response.data.allIdentities;
        this.clearSelection();
        return response.data.message;
      } catch (error) {
        throw new Error(error.response?.data?.message || error.message);
      }
    },
  },
});