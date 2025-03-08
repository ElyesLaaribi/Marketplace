import { createRouter, createWebHistory } from "vue-router";
import Home from "./pages/Client/Home.vue";
import Login from "./pages/Guest/Login.vue";
import Signup from "./pages/Guest/Signup.vue";
import NotFound from "./pages/Guest/NotFound.vue";
import Landing from "./pages/Guest/Landing.vue";
import LessorSign from "./pages/Guest/LessorSign.vue";
import LessorHome from "./pages/Lessor/LessorHome.vue";
import LessorDash from "./pages/Lessor/LessorDash.vue";
import LessorProfile from "./pages/Lessor/LessorProfile.vue";
import { useUserStore } from "./store/user";
import useLessorStore from "./store/lessor";


const clientGuard = async (to, from, next) => {
    const userStore = useUserStore();

    if (!userStore.user) {
        await userStore.fetchUser();
    }

    if (userStore.user?.role === "client") {
        next();
    } else {
        next("/notFound"); 
    }
};


const lessorGuard = async (to, from, next) => {
    const lessorStore = useLessorStore();

    if (!lessorStore.lessor) {
        await lessorStore.fetchLessor();
    }

    if (lessorStore.lessor?.role === "lessor") {
        next();
    } else {
        next("/notFound");
    }
};

const routes = [
    { path: "/", name: "Landing", component: Landing },
    { path: "/login", name: "Login", component: Login },
    { path: "/signup", name: "Signup", component: Signup },
    { path: "/signuplessor", name: "Signuplessor", component: LessorSign },

    // client
    { path: "/home", name: "Home", component: Home, beforeEnter: clientGuard },

    // lessor
    { path: "/lessorhome", name: "LessorHome", component: LessorHome, beforeEnter: lessorGuard },
    { path: "/lessordash", name: "Lessdash", component: LessorDash, beforeEnter: lessorGuard },
    { path: "/lessorprofile", name: "Lessprofile", component: LessorProfile, beforeEnter: lessorGuard },

    
    { path: "/:pathMatch(.*)*", name: "NotFound", component: NotFound }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

export default router;
