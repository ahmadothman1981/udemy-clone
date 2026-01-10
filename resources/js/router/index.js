import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

// Lazy load views
const Home = () => import('../views/CourseCatalog.vue');
const Login = () => import('../views/Login.vue');
const Signup = () => import('../views/Signup.vue');
const CourseDetail = () => import('../views/CourseDetail.vue');
const LearningPlayer = () => import('../views/LearningPlayer.vue');
const MyCourses = () => import('../views/MyCourses.vue');
const InstructorDashboard = () => import('../views/InstructorDashboard.vue');
const AdminDashboard = () => import('../views/AdminDashboard.vue');

const routes = [
    { path: '/', component: Home },
    { path: '/login', component: Login },
    { path: '/signup', component: Signup },
    { path: '/forgot-password', component: () => import('../views/ForgotPassword.vue') },
    { path: '/password-reset/:token', component: () => import('../views/ResetPassword.vue') },
    { path: '/instructor/signup', component: () => import('../views/InstructorSignup.vue') },
    { path: '/cart', component: () => import('../views/Cart.vue') },
    { path: '/wishlist', component: () => import('../views/Wishlist.vue') },
    { path: '/account-settings', component: () => import('../views/AccountSettings.vue'), meta: { requiresAuth: true } },
    { path: '/notifications', component: () => import('../views/Notifications.vue'), meta: { requiresAuth: true } },
    { path: '/payment-methods', component: () => import('../views/PaymentMethods.vue'), meta: { requiresAuth: true } },
    { path: '/messages', component: () => import('../views/Messages.vue'), meta: { requiresAuth: true } },
    { path: '/admin', component: AdminDashboard, meta: { requiresAuth: true, requiresAdmin: true } },
    { path: '/dashboard', component: () => import('../views/StudentDashboard.vue'), meta: { requiresAuth: true } },
    { path: '/instructor', component: InstructorDashboard, meta: { requiresAuth: true, requiresInstructor: true } },
    { path: '/course/:slug', component: CourseDetail, props: true },
    {
        path: '/instructor/course/:id/manage',
        component: () => import('../views/CourseEditor.vue'),
        meta: { requiresAuth: true, requiresInstructor: true }
    },
    {
        path: '/learn/course/:courseSlug',
        component: LearningPlayer,
        props: true,
        meta: { requiresAuth: true }
    },
    {
        path: '/learn/course/:courseSlug/lecture/:lectureId?',
        component: LearningPlayer,
        props: true,
        meta: { requiresAuth: true }
    },
    {
        path: '/my-courses',
        component: MyCourses,
        meta: { requiresAuth: true }
    },
    {
        path: '/checkout',
        component: () => import('../views/Checkout.vue'),
        meta: { requiresAuth: true }
    },
    {
        path: '/checkout/success',
        component: () => import('../views/CheckoutSuccess.vue'),
        meta: { requiresAuth: true }
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

// Auth & Role Guard
router.beforeEach(async (to, from, next) => {
    const authStore = useAuthStore();

    // Check if route requires auth
    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        next('/login');
        return;
    }

    // Role checks
    if (authStore.isAuthenticated) {
        // Ensure user data is loaded for role check
        if (!authStore.user) {
            try {
                await authStore.fetchUser();
            } catch (e) {
                next('/login');
                return;
            }
        }

        // Check Instructor role
        if (to.meta.requiresInstructor && !authStore.isInstructor) {
            next('/dashboard'); // Redirect unauthorized access to student dashboard
            return;
        }

        // Check Admin role
        if (to.meta.requiresAdmin && !authStore.isAdmin) {
            next('/dashboard');
            return;
        }
    }

    next();
});

export default router;
