import { createRouter, createWebHistory } from "vue-router";
import Home from "./pages/Client/Home.vue";
import Login from "./pages/Guest/Login.vue";
import Signup from "./pages/Guest/Signup.vue";
import NotFound from "./pages/Guest/NotFound.vue";
import Landing from "./pages/Guest/Landing.vue";
import LessorSign from "./pages/Guest/LessorSign.vue";
import Admin from "./pages/Admin/Admin.vue";
import LessorHome from "./pages/Lessor/LessorHome.vue";
import LessorDash from "./pages/Lessor/LessorDash.vue";
import useUserStore from "./store/user";
import LessorProfile from "./pages/Lessor/LessorProfile.vue";
import useLessorStore from "./store/lessor";

// client
const authGuard = async (to, from, next) => {
    try {
        const userStore = useUserStore();
        await userStore.fetchUser(); 
        next(); 
    } catch (error) {
        console.error('Failed to fetch data:', error);
        next(false); 
    }
};

// lessor
const lessorAuthGuard = async (to, from, next) => {
    try {
        const lessorStore = useLessorStore();
        await lessorStore.fetchLessor(); 
        next(); 
    } catch (error) {
        console.error('Failed to fetch lessor data:', error);
        next(false); 
    }
};




const routes = [
    
    {
        path: '/home',
        name: 'Home',
        component: Home,
        beforeEnter: authGuard 
    },
    {
        path: '/admin',
        name: "Admin",
        component: Admin,
        beforeEnter: authGuard
    },
    {
        path: '/lessorhome',
        name: 'LessorHome',
        component: LessorHome,
        beforeEnter: lessorAuthGuard
    },
    {
        path: '/',
        name: 'Landing',
        component: Landing
    },
    { 
        path: '/login',
        name: 'Login',
        component: Login
    },
    {
        path: '/signup',
        name: 'Signup',
        component: Signup
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'NotFound',
        component: NotFound
    },
    {
        path: '/signuplessor',
        name: 'Signuplessor',
        component: LessorSign
    },
    // {
    //     path: '/lessorhome',
    //     name: 'Lessorhome',
    //     component: LessorHome
    // },
    {
        path: '/lessordash',
        name: 'Lessdash',
        component: LessorDash,
        beforeEnter: lessorAuthGuard
    },
    {
        path: '/lessorprofile',
        name: 'Lessprofile',
        component: LessorProfile    
    }

]

const router = createRouter({
    history: createWebHistory(),
    routes
})

export default router