import { defineStore } from "pinia";
import api from "../axios.js"; // Import the axios instance for API calls

export const useUserStore = defineStore('user', {
  state: () => ({
    user: null,
    token: localStorage.getItem('auth_token') || null, // Initially check localStorage for token
    loading: false,
  }),

  actions: {
    // Fetch the user data
    async fetchUser() {
      if (!this.token) {
        console.warn('No token found. User data not fetched.');
        return;
      }

      this.loading = true;
      console.log('Fetching user data...');
      try {
        const { data } = await api.get('/api/user'); // API call to get the authenticated user's details
        console.log('User data fetched:', data);
        this.user = data;
      } catch (error) {
        console.error('Error fetching user data:', error);
      } finally {
        this.loading = false;
      }
    },

    // Set the token manually (after login)
    setToken(token) {
      this.token = token;
      localStorage.setItem('auth_token', token);
      api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    },

    // Clear the token (e.g., on logout)
    clearToken() {
      this.token = null;
      localStorage.removeItem('auth_token');
      api.defaults.headers.common['Authorization'] = '';
    },
  },
});
