import axios from "axios";
import router from "./router.js";
axios.defaults.withCredentials = true;

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL,
  // withCredentials: true,
  // withXSRFToken: true
});


api.interceptors.response.use((response) => {
    return response;
  }, error => {
    if (error.response && error.response.status === 401) {
      router.push({name: 'Login'});
    }
  
    throw error;
  })

  
  export default api