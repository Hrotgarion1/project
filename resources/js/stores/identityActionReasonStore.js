// resources/js/stores/identityActionReasonStore.js
import { defineStore } from 'pinia';

export const useIdentityActionReasonStore = defineStore('identityActionReason', {
  state: () => ({
    reasons: [],
    selectedReasons: [],
  }),
  actions: {
    setReasons(reasons) {
      this.reasons = reasons;
    },
    toggleSelection(id) {
      if (this.selectedReasons.includes(id)) {
        this.selectedReasons = this.selectedReasons.filter((reasonId) => reasonId !== id);
      } else {
        this.selectedReasons.push(id);
      }
    },
  },
});