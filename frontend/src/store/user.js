import { defineStore } from "pinia";
import api from "../axios.js"; 

export const useUserStore = defineStore('user', {
  state: () => ({
    user: null,
    token: localStorage.getItem('auth_token') || null,
    loading: false,
  }),

  actions: {
    async fetchUser() {
      if (!this.token) {
        console.warn('No token found. User data not fetched.');
        return;
      }

      this.loading = true;
      console.log('Fetching user data...');
      try {
        const { data } = await api.get('/api/user'); 
        console.log('User data fetched:', data);
        this.user = data;
      } catch (error) {
        console.error('Error fetching user data:', error);
      } finally {
        this.loading = false;
      }
    },

    setToken(token) {
      this.token = token;
      localStorage.setItem('auth_token', token);
      api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    },

    clearToken() {
      this.token = null;
      localStorage.removeItem('auth_token');
      api.defaults.headers.common['Authorization'] = '';
    },
  },
});
