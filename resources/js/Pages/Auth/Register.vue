<script setup>
import { onMounted } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

const props = defineProps({
    refLink: {
        type: String,
        default: '2232'
    }
});

const form = useForm({
    name: '',
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
    referral_link: props.refLink,
    amount_src: '',
    account_type: 'standard_investment',
});

watch(() => form.username, (newValue) => {
    const cleanedUsername = newValue.replace(/[^a-zA-Z0-9]/g, '');
    if (cleanedUsername !== newValue) {
        form.username = cleanedUsername;
    }
});

const submit = () => {
    form.post(route('register.user'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

onMounted(() => {
    console.log('ref' + props.refLink)
});
</script>

<template>
    <GuestLayout :height="'auto'">
        <Head title="Global Visioners International : Register"></Head>

        <!-- Right Panel -->
        <div class="d-flex flex-column flex-row-fluid position-relative overflow-hidden auth-right-panel">

            <!-- Top nav -->
            <div class="d-flex justify-content-end align-items-center px-8 pt-6">
                <span class="text-muted me-2" style="font-size:0.9rem;">Already have an account?</span>
                <Link :href="route('login')" class="auth-link-btn">
                    Sign In
                </Link>
            </div>

            <!-- Form Center -->
            <div class="d-flex flex-column-fluid flex-center px-4 py-6">
                <div class="auth-card">

                    <!-- Brand Icon -->
                    <div class="text-center mb-6">
                        <div class="auth-brand-icon mx-auto mb-4">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
                                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <h2 class="auth-title">Create Account</h2>
                        <p class="auth-subtitle">Join Global Visioners International today</p>
                    </div>

                    <!-- Form -->
                    <form @submit.prevent="submit">

                        <!-- Account Type Dropdown -->
                        <div class="mb-4">
                            <label class="auth-label">Account Type</label>
                            <div class="auth-field-group">
                                <div class="auth-field-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                        <rect x="2" y="3" width="20" height="14" rx="2" stroke="#94a3b8" stroke-width="1.8"/>
                                        <path d="M8 21h8M12 17v4" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                </div>
                                <select class="auth-field auth-select" v-model="form.account_type">
                                    <option value="standard_investment">Standard Investment</option>
                                    <option value="installment_plan">Installment Plan</option>
                                </select>
                                <div class="auth-select-arrow">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                                        <path d="M6 9l6 6 6-6" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                            </div>
                            <!-- Account type badge hint -->
                            <div class="account-type-hint mt-2">
                                <span v-if="form.account_type === 'standard_investment'" class="account-badge badge-investment">
                                    One-time full investment upfront
                                </span>
                                <span v-else class="account-badge badge-installment">
                                    Pay in scheduled installments
                                </span>
                            </div>
                        </div>

                        <!-- Two columns: Name + Username -->
                        <div class="row g-3 mb-0">
                            <div class="col-6">
                                <label class="auth-label">Full Name</label>
                                <div class="auth-field-group">
                                    <div class="auth-field-icon">
                                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none">
                                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round"/>
                                            <circle cx="12" cy="7" r="4" stroke="#94a3b8" stroke-width="1.8"/>
                                        </svg>
                                    </div>
                                    <input
                                        class="auth-field"
                                        type="text"
                                        v-model="form.name"
                                        placeholder="Your name"
                                        autocomplete="off"
                                        required
                                    />
                                </div>
                                <InputError class="mt-1" :message="form.errors.name" />
                            </div>
                            <div class="col-6">
                                <label class="auth-label">Username</label>
                                <div class="auth-field-group">
                                    <div class="auth-field-icon">
                                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none">
                                            <circle cx="12" cy="12" r="10" stroke="#94a3b8" stroke-width="1.8"/>
                                            <path d="M14.31 8a4 4 0 1 0 1.64 4.36" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round"/>
                                            <path d="M12 12h.01" stroke="#94a3b8" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                    </div>
                                    <input
                                        class="auth-field"
                                        type="text"
                                        v-model="form.username"
                                        placeholder="username"
                                        autocomplete="off"
                                        required
                                    />
                                </div>
                                <InputError class="mt-1" :message="form.errors.username" />
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="mt-3 mb-0">
                            <label class="auth-label">Email Address</label>
                            <div class="auth-field-group">
                                <div class="auth-field-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round"/>
                                        <polyline points="22,6 12,13 2,6" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                </div>
                                <input
                                    class="auth-field"
                                    type="email"
                                    v-model="form.email"
                                    placeholder="your@email.com"
                                    autocomplete="off"
                                    required
                                />
                            </div>
                            <InputError class="mt-1" :message="form.errors.email" />
                        </div>

                        <!-- Two columns: Password + Confirm -->
                        <div class="row g-3 mt-0">
                            <div class="col-6">
                                <label class="auth-label">Password</label>
                                <div class="auth-field-group">
                                    <div class="auth-field-icon">
                                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none">
                                            <rect x="3" y="11" width="18" height="11" rx="2" stroke="#94a3b8" stroke-width="1.8"/>
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
                                <InputError class="mt-1" :message="form.errors.password" />
                            </div>
                            <div class="col-6">
                                <label class="auth-label">Confirm</label>
                                <div class="auth-field-group">
                                    <div class="auth-field-icon">
                                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none">
                                            <rect x="3" y="11" width="18" height="11" rx="2" stroke="#94a3b8" stroke-width="1.8"/>
                                            <path d="M7 11V7a5 5 0 0 1 10 0v4" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round"/>
                                            <path d="M9 16l2 2 4-4" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>
                                    <input
                                        class="auth-field"
                                        type="password"
                                        v-model="form.password_confirmation"
                                        placeholder="Confirm"
                                        autocomplete="off"
                                        required
                                    />
                                </div>
                                <InputError class="mt-1" :message="form.errors.password_confirmation" />
                            </div>
                        </div>

                        <!-- Payment Screenshot -->
                        <div class="mt-3 mb-0">
                            <label class="auth-label">Payment Screenshot</label>
                            <label class="auth-file-upload">
                                <input
                                    type="file"
                                    class="d-none"
                                    @input="form.amount_src = $event.target.files[0]"
                                    accept="image/*"
                                />
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" class="me-2">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" stroke="#4f46e5" stroke-width="1.8" stroke-linecap="round"/>
                                    <polyline points="17,8 12,3 7,8" stroke="#4f46e5" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    <line x1="12" y1="3" x2="12" y2="15" stroke="#4f46e5" stroke-width="1.8" stroke-linecap="round"/>
                                </svg>
                                <span style="color:#4f46e5; font-weight:500;">
                                    {{ form.amount_src ? form.amount_src.name : 'Click to upload screenshot' }}
                                </span>
                            </label>
                            <progress v-if="form.progress" :value="form.progress.percentage" max="100" class="w-100 mt-1" style="height:4px;">
                                {{ form.progress.percentage }}%
                            </progress>
                            <InputError class="mt-1" :message="form.errors.amount_src" />
                        </div>

                        <!-- Referral Link -->
                        <div class="mt-3 mb-0">
                            <label class="auth-label">Referral Code</label>
                            <div class="auth-field-group">
                                <div class="auth-field-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                        <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round"/>
                                        <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                </div>
                                <input
                                    class="auth-field"
                                    type="text"
                                    v-model="form.referral_link"
                                    placeholder="Referral code"
                                    autocomplete="off"
                                    required
                                />
                            </div>
                            <InputError class="mt-1" :message="form.errors.referral_link" />
                        </div>

                        <!-- Submit -->
                        <button
                            type="submit"
                            class="auth-submit-btn w-100 mt-5"
                            :disabled="form.processing"
                            :class="{ 'opacity-50': form.processing }"
                        >
                            <span v-if="!form.processing">Create Account</span>
                            <span v-else>Creating account...</span>
                        </button>

                    </form>

                    <p class="text-center mt-4 mb-0" style="font-size:0.85rem; color:#94a3b8;">
                        Already registered?
                        <Link :href="route('login')" style="color:#4f46e5; font-weight:600; text-decoration:none;">Sign in here</Link>
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
    overflow-y: auto;
}

.auth-card {
    background: #ffffff;
    border-radius: 24px;
    padding: 2rem 2.25rem;
    width: 100%;
    max-width: 520px;
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
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e1b4b;
    margin-bottom: 0.25rem;
}

.auth-subtitle {
    font-size: 0.88rem;
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

.auth-label {
    display: block;
    font-size: 0.8rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.4rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.auth-field-group {
    position: relative;
    display: flex;
    align-items: center;
}

.auth-field-icon {
    position: absolute;
    left: 13px;
    top: 50%;
    transform: translateY(-50%);
    display: flex;
    align-items: center;
    pointer-events: none;
    z-index: 1;
}

.auth-field {
    width: 100%;
    padding: 0.7rem 1rem 0.7rem 2.6rem;
    border: 1.5px solid #e8edf0;
    border-radius: 10px;
    background: #f8fafc;
    font-size: 0.88rem;
    color: #1e1b4b;
    transition: all 0.2s;
    outline: none;
    appearance: none;
    -webkit-appearance: none;
}

.auth-field:focus {
    border-color: #4f46e5;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.auth-field::placeholder {
    color: #c4c9d4;
}

.auth-select {
    padding-right: 2.5rem;
    cursor: pointer;
}

.auth-select-arrow {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
}

.account-badge {
    display: inline-block;
    font-size: 0.75rem;
    font-weight: 500;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
}

.badge-investment {
    background: rgba(16, 185, 129, 0.1);
    color: #059669;
}

.badge-installment {
    background: rgba(245, 158, 11, 0.1);
    color: #d97706;
}

.auth-file-upload {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    border: 1.5px dashed #c7d2fe;
    border-radius: 10px;
    background: #f8f7ff;
    cursor: pointer;
    transition: all 0.2s;
    width: 100%;
    font-size: 0.88rem;
}

.auth-file-upload:hover {
    border-color: #4f46e5;
    background: #f0f0ff;
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
