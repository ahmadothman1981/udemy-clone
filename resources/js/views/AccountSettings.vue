<template>
    <div>
        <Navbar />
        <div class="bg-gray-50 min-h-screen py-10">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">Account Settings</h1>

            <!-- Profile Information -->
            <div class="bg-white shadow sm:rounded-lg mb-6">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Profile Information</h3>
                    <div class="mt-2 max-w-xl text-sm text-gray-500">
                        <p>Update your account's profile information.</p>
                    </div>
                    <form @submit.prevent="updateProfileInfo" class="mt-5">
                        <div class="grid grid-cols-6 gap-6">
                            <div class="col-span-6 sm:col-span-4">
                                <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                                <input type="text" name="name" id="name" v-model="profileForm.name" class="mt-1 focus:ring-purple-500 focus:border-purple-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>

                            <div class="col-span-6 sm:col-span-4">
                                <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                                <input type="text" name="email" id="email" v-model="profileForm.email" disabled class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md bg-gray-50 text-gray-500 cursor-not-allowed">
                            </div>
                        </div>
                        <div class="mt-5">
                            <button type="submit" :disabled="profileLoading" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 disabled:opacity-50">
                                <span v-if="profileLoading">Saving...</span>
                                <span v-else>Save Profile</span>
                            </button>
                            <span v-if="profileMessage" :class="{'text-green-600': profileSuccess, 'text-red-600': !profileSuccess}" class="ml-3 text-sm font-medium">
                                {{ profileMessage }}
                            </span>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Password Update -->
             <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Update Password</h3>
                    <div class="mt-2 max-w-xl text-sm text-gray-500">
                        <p>Ensure your account is using a long, random password to stay secure.</p>
                    </div>
                    <form @submit.prevent="updatePassword" class="mt-5">
                        <div class="grid grid-cols-6 gap-6">
                            <div class="col-span-6 sm:col-span-4">
                                <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                                <input type="password" name="password" id="password" v-model="passwordForm.password" class="mt-1 focus:ring-purple-500 focus:border-purple-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>

                            <div class="col-span-6 sm:col-span-4">
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                                <input type="password" name="password_confirmation" id="password_confirmation" v-model="passwordForm.password_confirmation" class="mt-1 focus:ring-purple-500 focus:border-purple-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>
                        </div>
                        <div class="mt-5">
                            <button type="submit" :disabled="passwordLoading" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 disabled:opacity-50">
                                <span v-if="passwordLoading">Changing...</span>
                                <span v-else>Change Password</span>
                            </button>
                             <span v-if="passwordMessage" :class="{'text-green-600': passwordSuccess, 'text-red-600': !passwordSuccess}" class="ml-3 text-sm font-medium">
                                {{ passwordMessage }}
                            </span>
                        </div>
                    </form>
                </div>
            </div>
        </div>
</div>
    </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useRouter } from 'vue-router';
import Navbar from '../components/Navbar.vue';

const authStore = useAuthStore();
const router = useRouter();

const profileForm = reactive({
    name: '',
    email: ''
});

const passwordForm = reactive({
    password: '',
    password_confirmation: ''
});

const profileLoading = ref(false);
const profileMessage = ref('');
const profileSuccess = ref(false);

const passwordLoading = ref(false);
const passwordMessage = ref('');
const passwordSuccess = ref(false);

onMounted(async () => {
    if (!authStore.user) {
        await authStore.fetchUser();
    }
    if (authStore.user) {
        profileForm.name = authStore.user.name;
        profileForm.email = authStore.user.email;
    }
});

const updateProfileInfo = async () => {
    profileLoading.value = true;
    profileMessage.value = '';
    
    try {
        await authStore.updateProfile({ name: profileForm.name });
        profileSuccess.value = true;
        profileMessage.value = 'Profile updated successfully.';
    } catch (error) {
        profileSuccess.value = false;
        profileMessage.value = error.response?.data?.message || 'Failed to update profile.';
    } finally {
        profileLoading.value = false;
    }
};

const updatePassword = async () => {
    if (passwordForm.password !== passwordForm.password_confirmation) {
        passwordSuccess.value = false;
        passwordMessage.value = 'Passwords do not match.';
        return;
    }

    if (passwordForm.password.length < 8) {
        passwordSuccess.value = false;
        passwordMessage.value = 'Password must be at least 8 characters.';
        return;
    }

    passwordLoading.value = true;
    passwordMessage.value = '';

    try {
        await authStore.updateProfile({ 
            password: passwordForm.password,
            password_confirmation: passwordForm.password_confirmation
        });
        passwordSuccess.value = true;
        passwordMessage.value = 'Password changed successfully. Redirecting to login...';
        passwordForm.password = '';
        passwordForm.password_confirmation = '';

        setTimeout(() => {
            authStore.logout();
            router.push('/login');
        }, 1500);
    } catch (error) {
        passwordSuccess.value = false;
        passwordMessage.value = error.response?.data?.message || 'Failed to change password.';
    } finally {
        passwordLoading.value = false;
    }
};
</script>
