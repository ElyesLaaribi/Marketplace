import { defineStore } from "pinia";
import api from "../axios";

const useLessorStore = defineStore("lessor", {
  state: () => ({
    lessor: null,
  }),
  actions: {
    fetchLessor() {
      return api
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
