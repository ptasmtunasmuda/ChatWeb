<template>
  <div class="min-h-screen flex items-center justify-center py-12 px-4 bg-gradient-to-br from-white to-blue-50">
    <div class="max-w-md w-full space-y-8">
      <!-- Header -->
      <div class="text-center">
        <div class="mx-auto w-16 h-16 rounded-2xl flex items-center justify-center mb-8" style="background: var(--primary-gradient)">
          <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
          </svg>
        </div>
        <h2 class="text-3xl font-bold text-gray-900 mb-2">Forgot Password?</h2>
        <p class="text-gray-600">No worries, we'll send you reset instructions</p>
      </div>

      <!-- Success Message -->
      <div v-if="emailSent" class="card">
        <div class="text-center">
          <div class="mx-auto w-16 h-16 bg-green-100 rounded-2xl flex items-center justify-center mb-6">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 19v-8.93a2 2 0 01.89-1.664l7-4.666a2 2 0 012.22 0l7 4.666A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M12 12v7"></path>
            </svg>
          </div>
          <h3 class="text-xl font-semibold text-gray-900 mb-4">Check Your Email!</h3>
          <p class="text-gray-600 mb-6 leading-relaxed">
            We've sent a password reset link to <span class="font-medium">{{ form.email }}</span>.
            Click the link in the email to reset your password.
          </p>
          <p class="text-sm text-gray-500 mb-6">
            Didn't receive the email? Check your spam folder or
            <button @click="resendEmail" class="text-blue-600 font-medium">
              resend the email
            </button>
          </p>
          <router-link
            to="/login"
            class="inline-flex items-center px-6 py-3 bg-gray-100 text-gray-700 rounded-xl font-medium"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Login
          </router-link>
        </div>
      </div>

      <!-- Reset Form -->
      <div v-else class="card">
        <form @submit.prevent="handleForgotPassword" class="space-y-6">
          <!-- Email Field -->
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
              Email Address
            </label>
            <div class="relative">
              <input
                id="email"
                v-model="form.email"
                type="email"
                required
                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors"
                :class="{ 'border-red-500 focus:ring-red-500': errors.email }"
                placeholder="Enter your email address"
              />
              <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                </svg>
              </div>
            </div>
            <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email[0] }}</p>
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="loading"
            class="btn-primary w-full disabled:opacity-50"
          >
            <span v-if="loading" class="flex items-center justify-center">
              <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Sending reset link...
            </span>
            <span v-else>Send Reset Link</span>
          </button>

          <!-- Back to Login -->
          <div class="text-center">
            <p class="text-sm text-gray-600">
              Remember your password?
              <router-link to="/login" class="text-blue-600 font-medium ml-1">
                Back to login
              </router-link>
            </p>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import { useNotificationStore } from '../../stores/notifications';
import axios from 'axios';

const router = useRouter();
const notificationStore = useNotificationStore();

const loading = ref(false);
const emailSent = ref(false);
const errors = ref({});

const form = reactive({
  email: ''
});

const handleForgotPassword = async () => {
  loading.value = true;
  errors.value = {};

  try {
    await axios.post('/api/auth/forgot-password', {
      email: form.email
    });

    emailSent.value = true;
    notificationStore.success('Reset Link Sent!', 'Check your email for the password reset instructions.');
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {};
    }
    notificationStore.error('Reset Failed', error.response?.data?.message || 'An error occurred while sending the reset link.');
  } finally {
    loading.value = false;
  }
};

const resendEmail = async () => {
  await handleForgotPassword();
};
</script>
