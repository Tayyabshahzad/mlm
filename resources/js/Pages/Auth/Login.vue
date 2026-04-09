<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {

};
</script>

<template>
    <GuestLayout :height="'56.8'">
        <Head title="Global Visioners International : Login"></Head>

        <!-- Right Panel -->
        <div class="d-flex flex-column flex-row-fluid position-relative overflow-hidden auth-right-panel">

            <!-- Top nav -->
            <div class="d-flex justify-content-end align-items-center px-8 pt-6">
                <span class="text-muted me-2" style="font-size:0.9rem;">Don't have an account?</span>
                <Link :href="route('register')" class="auth-link-btn">
                    Sign Up
                </Link>
            </div>

            <!-- Form Center -->
            <div class="d-flex flex-column-fluid flex-center px-4 py-6">
                <div class="auth-card">

                    <!-- Brand Icon -->
                    <div class="text-center mb-7">
                        <div class="auth-brand-icon mx-auto mb-4">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
                                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <h2 class="auth-title">Welcome Back</h2>
                        <p class="auth-subtitle">Sign in to your GVI account</p>
                    </div>

                    <!-- Status -->
                    <div v-if="status" class="alert alert-success py-3 mb-4 rounded-3 text-sm">
                        {{ status }}
                    </div>

                    <!-- Form -->
                    <form @submit.prevent="submit">

                        <!-- Email -->
                        <div class="auth-field-group mb-4">
                            <div class="auth-field-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    <polyline points="22,6 12,13 2,6" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <input
                                class="auth-field"
                                v-model="form.email"
                                type="email"
                                placeholder="Email address"
                                autocomplete="off"
                                required
                            />
                        </div>
                        <InputError class="mt-1 mb-3" :message="form.errors.email" />

                        <!-- Password -->
                        <div class="auth-field-group mb-2">
                            <div class="auth-field-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2" stroke="#94a3b8" stroke-width="1.8"/>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <input
                                class="auth-field"
                                type="password"
                                v-model="form.password"
                                placeholder="Password"
                                autocomplete="off"
                                required
                            />
                        </div>
                        <InputError class="mt-1 mb-3" :message="form.errors.password" />

                        <!-- Actions -->
                        <div class="d-flex justify-content-between align-items-center mb-5 mt-3">
                            <Link
                                v-if="canResetPassword"
                                :href="route('password.request')"
                                class="auth-forgot-link"
                            >
                                Forgot password?
                            </Link>
                            <span v-else></span>
                        </div>

                        <button
                            type="submit"
                            class="auth-submit-btn w-100"
                            :disabled="form.processing"
                            :class="{ 'opacity-50': form.processing }"
                        >
                            <span v-if="!form.processing">Sign In</span>
                            <span v-else>Signing in...</span>
                        </button>

                    </form>

                    <p class="text-center mt-5 mb-0" style="font-size:0.85rem; color:#94a3b8;">
                        New to GVI?
                        <Link :href="route('register')" style="color:#4f46e5; font-weight:600; text-decoration:none;">Create an account</Link>
                    </p>

                </div>
            </div>

            <!-- Footer mobile -->
            <div class="d-flex d-lg-none justify-content-between align-items-center px-6 py-4 mt-auto">
                <span style="font-size:0.78rem; color:#94a3b8;">© 2024 Global Visioners International</span>
                <div class="d-flex gap-3">
                    <a href="#" style="font-size:0.78rem; color:#94a3b8; text-decoration:none;">Privacy</a>
                    <a href="#" style="font-size:0.78rem; color:#94a3b8; text-decoration:none;">Terms</a>
                </div>
            </div>

        </div>
    </GuestLayout>
</template>

<style scoped>
.auth-right-panel {
    background: linear-gradient(160deg, #f0f4ff 0%, #e8edf8 100%);
    min-height: 100vh;
}

.auth-card {
    background: #ffffff;
    border-radius: 24px;
    padding: 2.5rem;
    width: 100%;
    max-width: 440px;
    box-shadow: 0 8px 40px rgba(79, 70, 229, 0.08), 0 2px 12px rgba(0,0,0,0.05);
}

.auth-brand-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 8px 20px rgba(79, 70, 229, 0.35);
}

.auth-title {
    font-size: 1.6rem;
    font-weight: 700;
    color: #1e1b4b;
    margin-bottom: 0.25rem;
}

.auth-subtitle {
    font-size: 0.9rem;
    color: #94a3b8;
    margin-bottom: 0;
}

.auth-link-btn {
    background: rgba(79, 70, 229, 0.08);
    color: #4f46e5;
    font-weight: 600;
    font-size: 0.85rem;
    padding: 0.4rem 1rem;
    border-radius: 20px;
    text-decoration: none;
    transition: all 0.2s;
}
.auth-link-btn:hover {
    background: rgba(79, 70, 229, 0.15);
    color: #3730a3;
}

.auth-field-group {
    position: relative;
    display: flex;
    align-items: center;
}

.auth-field-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    display: flex;
    align-items: center;
    pointer-events: none;
    z-index: 1;
}

.auth-field {
    width: 100%;
    padding: 0.75rem 1rem 0.75rem 2.75rem;
    border: 1.5px solid #e8edf0;
    border-radius: 12px;
    background: #f8fafc;
    font-size: 0.9rem;
    color: #1e1b4b;
    transition: all 0.2s;
    outline: none;
}

.auth-field:focus {
    border-color: #4f46e5;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.auth-field::placeholder {
    color: #c4c9d4;
}

.auth-forgot-link {
    font-size: 0.85rem;
    color: #4f46e5;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.2s;
}
.auth-forgot-link:hover {
    color: #3730a3;
}

.auth-submit-btn {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    color: #fff;
    border: none;
    padding: 0.85rem 1.5rem;
    border-radius: 12px;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
    letter-spacing: 0.3px;
}
.auth-submit-btn:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(79, 70, 229, 0.4);
}
.auth-submit-btn:active:not(:disabled) {
    transform: translateY(0);
}
</style>
