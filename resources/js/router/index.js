import { createRouter, createWebHistory } from 'vue-router';

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
    { path: '/cart', component: () => import('../views/Cart.vue') },
    { path: '/wishlist', component: () => import('../views/Wishlist.vue') },
    { path: '/account-settings', component: () => import('../views/AccountSettings.vue'), meta: { requiresAuth: true } },
    { path: '/notifications', component: () => import('../views/Notifications.vue'), meta: { requiresAuth: true } },
    { path: '/payment-methods', component: () => import('../views/PaymentMethods.vue'), meta: { requiresAuth: true } },
    { path: '/messages', component: () => import('../views/Messages.vue'), meta: { requiresAuth: true } },
    { path: '/admin', component: AdminDashboard, meta: { requiresAuth: true } },
    { path: '/instructor', component: InstructorDashboard, meta: { requiresAuth: true } },
    { path: '/course/:slug', component: CourseDetail, props: true },
    {
        path: '/learn/course/:courseId/lecture/:lectureId?',
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

// Mock Auth Guard
router.beforeEach((to, from, next) => {
    // In real app check pinia auth store
    next();
});

export default router;
