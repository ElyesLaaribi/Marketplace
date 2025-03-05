import { defineStore } from "pinia";
import axiosClient from "../axios";

const useLessorStore = defineStore("lessor", {
  state: () => ({
    lessor: null,
  }),
  actions: {
    fetchLessor() {
      return axiosClient
        .get("/api/lessor") 
        .then(({ data }) => {
          console.log(data);
          this.lessor = data;
        })
        .catch((error) => {
          console.error("Error fetching lessor data:", error);
        });
    },
  },
});

export default useLessorStore;
