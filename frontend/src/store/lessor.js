import { defineStore } from "pinia";
import api from "../axios";

export const useLessorStore = defineStore("lessor", {
  state: () => ({
    lessor: null,
    token: localStorage.getItem('auth_token') || null,
    loading: false,
  }),

  actions: {
    async fetchLessor() {
      if (!this.token) {
        console.warn('No token found. Lessor data not fetched.');
        return;
      }

      this.loading = true;
      console.log('Fetching lessor data...');
      try {
        const { data } = await api.get("/api/user"); 
        console.log('Data fetched:', data);
        if (data.role === "lessor") {
          this.lessor = data;
        } else {
          console.warn('Fetched data is not a lessor.');
        }
      } catch (error) {
        console.error("Error fetching lessor data:", error);
      } finally {
        this.loading = false;
      }
    },
  },
});

export default useLessorStore;
