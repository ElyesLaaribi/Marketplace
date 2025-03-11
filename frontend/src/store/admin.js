import { defineStore } from "pinia";
import api from "../axios.js"; 

export const useAdminStore = defineStore('admin', {
  state: () => ({
    admin: null,
    token: localStorage.getItem('auth_token') || null,
    loading: false,
  }),

  actions: {
    async fetchUser() {
      if (!this.token) {
        console.warn('No token found. admin data not fetched.');
        return;
      }

      this.loading = true;
      console.log('Fetching admin data...');
      try {
        const { data } = await api.get('/api/admin'); 
        console.log('Admin data fetched:', data);
        this.admin = data;
      } catch (error) {
        console.error('Error fetching admin data:', error);
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
