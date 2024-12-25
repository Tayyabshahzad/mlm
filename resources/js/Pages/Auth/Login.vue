<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
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
        <Head title="Global Visioners International : "></Head>  
        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>
        <!--begin::Content-->
        <div class="d-flex flex-column flex-row-fluid position-relative p-7 overflow-hidden">
            <!--begin::Content header-->
            <div class="position-absolute top-0 right-0 text-right mt-5 mb-15 mb-lg-0 flex-column-auto justify-content-center py-5 px-10">
                <span class="font-weight-bold text-dark-50">Dont have an account yet?</span>
                <Link :href="route('register')" class="font-weight-bold ml-2" id="kt_login_signup">
                    Register!
				</Link> 
            </div>
            <!--end::Content header-->
            <!--begin::Content body-->
            <div class="d-flex flex-column-fluid flex-center mt-30 mt-lg-0">
                <!--begin::Signin-->
                <div class="login-form login-signin">
                    <div class="text-center mb-10 mb-lg-20">
                        <h3 class="font-size-h1">Sign In</h3>
                        <p class="text-muted font-weight-bold">Enter your email and password</p>
                    </div>
                    <!--begin::Form-->
                    <form class="form" novalidate="novalidate"   @submit.prevent="submit">
                        <div class="form-group">
                            <input class="form-control form-control-solid h-auto py-5 px-6"
                             v-model="form.email"
                             required
                             autofocus 
                             type="email" name="email"  placeholder="Email" autocomplete="off" />
                             <InputError class="mt-2" :message="form.errors.email" />

                        </div>
                        <div class="form-group">
                            <input class="form-control form-control-solid h-auto py-5 px-6" 
                            type="password" 
                            v-model="form.password"
                            required 
                            placeholder="Password" name="password" autocomplete="off" />
                            <InputError class="mt-2" :message="form.errors.password" />
                        </div>
                        <!--begin::Action-->
                        <div class="form-group d-flex flex-wrap justify-content-between align-items-center">
                            <Link 
                              v-if="canResetPassword"
                                :href="route('password.request')" class="text-dark-50 text-hover-primary my-3 mr-2" id="kt_login_forgot">
                                Forgot Password ?
                            </Link>   
                            <button 
                             :class="{ 'opacity-25': form.processing }"
                             :disabled="form.processing"
                            id="kt_login_signin_submit" class="btn btn-primary font-weight-bold px-9 py-4 my-3">Log In</button>
                        </div>
                        <!--end::Action-->
                    </form>
                    <!--end::Form-->
                </div> 
            </div>
            <!--end::Content body-->
            <!--begin::Content footer for mobile-->
            <div class="d-flex d-lg-none flex-column-auto flex-column flex-sm-row justify-content-between align-items-center mt-5 p-5">
                <div class="text-dark-50 font-weight-bold order-2 order-sm-1 my-2">© 2020 Global Visioners International</div>
                <div class="d-flex order-1 order-sm-2 my-2">
                    <a href="#" class="text-dark-75">Privacy Policy</a>
                    <a href="#" class="text-dark-75 ml-4">Terms & Condition</a>
                    <a href="#" class="text-dark-75 ml-4">Contact</a>
                </div>
            </div>
            <!--end::Content footer for mobile-->
        </div>
        <!--end::Content-->
    </GuestLayout>
</template>
