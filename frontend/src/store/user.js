import { defineStore } from "pinia";
import axiosClient from "../axios";

const useUserStore = defineStore('user', {
  state: () => ({
    user: null,
    loading: false,
  }),
  actions: {
    fetchUser() {
      this.loading = true;
      return axiosClient.get('/api/user')
        .then(({data}) => {
          this.user = data;
        })
        .finally(() => {
          this.loading = false;
        });
    },
  }
});

export default useUserStore;
