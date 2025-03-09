import { createRouter, createWebHistory } from "vue-router";
import Home from "./pages/Client/Home.vue";
import Login from "./pages/Guest/Login.vue";
import Signup from "./pages/Guest/Signup.vue";
import NotFound from "./pages/Guest/NotFound.vue";
import Landing from "./pages/Guest/Landing.vue";
import LessorSign from "./pages/Guest/LessorSign.vue";
import LessorHome from "./pages/Lessor/LessorHome.vue";
import { useUserStore } from "./store/user";
import useLessorStore from "./store/lessor";
import ForgetPassword from "./pages/Guest/ForgetPassword.vue";
import ResetPassword from "./pages/Guest/ResetPassword.vue";
import Listings from "./pages/Lessor/Listings.vue";
import AddListings from "./pages/Lessor/AddListings.vue";

import Messages from "./pages/Lessor/Messages.vue";
import Profile from "./pages/Lessor/Profile.vue";
import Reservations from "./pages/Lessor/Reservations.vue";
import ClientProfile from "./pages/Client/ClientProfile.vue";




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
    { path: "/forget-password", name: "ForgetPassword", component: ForgetPassword },
    { path: "/reset-password", name: "ResetPassword", component: ResetPassword },

    // client
    { path: "/home", name: "Home", component: Home, beforeEnter: clientGuard },
    { path: "/client-profile", name: "client-profile", component: ClientProfile , beforeEnter: clientGuard },

    // lessor
    { path: "/lessorhome", name: "LessorHome", component: LessorHome, beforeEnter: lessorGuard },
    { path: "/listings", name: "Listings", component: Listings, beforeEnter: lessorGuard },
    { path: "/add-listings" , name: "AddListings", component: AddListings, beforeEnter: lessorGuard },
    { path: "/dms", name: "DMs", component: Messages, beforeEnter: lessorGuard },
    { path: "/lessor-profile", name: "Profile", component: Profile, beforeEnter: lessorGuard },
    { path: "/reservations", name: "Reservations" , component: Reservations, beforeEnter: lessorGuard },


    
    { path: "/:pathMatch(.*)*", name: "NotFound", component: NotFound }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

export default router;
