<script setup>
import { ref, onMounted } from 'vue'; 
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
    referral_link: props.refLink ,
    amount_src : ''
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
    <GuestLayout :height="'56.8'">
        <Head title="Global Visioners International : Register "></Head>  
        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>
        <!--begin::Content-->
        <div class="d-flex flex-column flex-row-fluid position-relative p-7 overflow-hidden">
            <!--begin::Content header-->
            <div class="position-absolute top-0 right-0 text-right mt-5 mb-15 mb-lg-0 flex-column-auto justify-content-center py-5 px-10">
                <span class="font-weight-bold text-dark-50">Have already account?</span>
                <Link :href="route('login')" class="font-weight-bold ml-2" id="kt_login_signup">
                    Login!
				</Link> 
            </div>
            <!--end::Content header-->
            <!--begin::Content body-->
            <div class="d-flex flex-column-fluid flex-center mt-30 mt-lg-0">
                <!--begin::Signin-->
                <div class="login-form login-signin">
                    <div class="text-center mb-10 mb-lg-20">
                        <h3 class="font-size-h1">Sign Up</h3>
                        <p class="text-muted font-weight-bold">Enter your details to create your account</p>
                    </div>
                    <!--begin::Form-->
                    <form class="form" novalidate="novalidate"   @submit.prevent="submit">
                        <div class="form-group">
                            <input class="form-control form-control-solid h-auto py-5 px-6"
                            id="name"
                            type="text" 
                            v-model="form.name"
                            required
                            autofocus
                            placeholder="Enter Your Name" autocomplete="off" /> 
                            <InputError class="mt-2" :message="form.errors.name" /> 
                        </div>

                        <div class="form-group">
                            <input class="form-control form-control-solid h-auto py-5 px-6"
                            id="name"
                            type="text" 
                            v-model="form.username"
                            required
                            autofocus
                            placeholder="Enter Username" autocomplete="off" /> 
                            <InputError class="mt-2" :message="form.errors.username" /> 
                        </div>


                        <div class="form-group">
                            <input class="form-control form-control-solid h-auto py-5 px-6"
                            id="email"
                            type="text" 
                            v-model="form.email"
                            required
                            autofocus
                            placeholder="Enter Your Email" autocomplete="off" /> 
                            <InputError class="mt-2" :message="form.errors.email" /> 
                        </div>


                        <div class="form-group">
                            <input class="form-control form-control-solid h-auto py-5 px-6"
                            id="password"
                            type="password" 
                            v-model="form.password"
                            required
                            autofocus
                            placeholder="Enter Your Password" autocomplete="off" /> 
                            <InputError class="mt-2" :message="form.errors.password" /> 
                        </div>

                        <div class="form-group">
                            <input class="form-control form-control-solid h-auto py-5 px-6"
                            id="password_confirmation"
                            type="password" 
                            v-model="form.password_confirmation"
                            required
                            autofocus
                            placeholder="Confirm Your Password" autocomplete="off" /> 
                            <InputError class="mt-2" :message="form.errors.password_confirmation" /> 
                        </div> 

                        <div class="form-group">
                            <input class="form-control form-control-solid h-auto py-5 px-6" 
                            type="file" 
                            @input="form.amount_src = $event.target.files[0]" 
                            required
                            autofocus
                            placeholder="Enter Referral Link" autocomplete="off" /> 
                            <progress v-if="form.progress" :value="form.progress.percentage" max="100">
                                {{ form.progress.percentage }}%
                            </progress>
                            <InputError class="mt-2" :message="form.errors.amount_src" /> 
                        </div>

                        

                        <div class="form-group">
                            <input class="form-control form-control-solid h-auto py-5 px-6"
                            id="referral_link"
                            type="text" 
                            v-model="form.referral_link"
                            required
                            autofocus
                            placeholder="Enter Referral Link" autocomplete="off" /> 
                            <InputError class="mt-2" :message="form.errors.referral_link" /> 
                        </div>





                        <!--begin::Action-->
                        <div class="form-group d-flex flex-wrap justify-content-between align-items-center">
                            <Link  
                                :href="route('login')" class="text-dark-50 text-hover-primary my-3 mr-2" id="kt_login_forgot">
                                Already registered?
                            </Link>   
                            <button 
                             :class="{ 'opacity-25': form.processing }"
                             :disabled="form.processing"
                            id="kt_login_signin_submit" class="btn btn-primary font-weight-bold px-9 py-4 my-3">Register</button>
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
