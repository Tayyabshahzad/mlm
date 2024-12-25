<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>
<template>
     <GuestLayout :height="'56.8'">
        <Head title="Email Verification" /> 
        <div class="d-flex flex-column flex-row-fluid position-relative p-7 overflow-hidden"> 
            <div class="position-absolute top-0 right-0 text-right mt-5 mb-15 mb-lg-0 flex-column-auto justify-content-center py-5 px-10"> 
                <Link :href="route('logout')"    method="post"  as="button" class="text-danger font-weight-bold ml-2" id="kt_login_signup">
                    Logout
				</Link> 
            </div> 
            <div class="d-flex flex-column-fluid flex-center mt-30 mt-lg-0"> 
                <div class="login-form login-signin">
                    <div class="text-center mb-10 mb-lg-20"> 
                        <p class="   "> Thanks for signing up! Before getting started, could you verify your
                            email address by clicking on the link we just emailed to you? If you
                            didn't receive the email, we will gladly send you another.</p> 
                        <p class="  font-weight-bold  text-green-600 mt-4"
                            v-if="verificationLinkSent"> A new verification link has been sent to the email address you
                                provided during registration.
                        </p>
                        <form class="form" novalidate="novalidate"   @submit.prevent="submit">  
                        <button 
                             :class="{ 'opacity-25': form.processing }"
                              :disabled="form.processing"
                            id="kt_login_signin_submit" class="btn btn-xs btn-primary font-weight-bold   my-3"> Resend Verification Email</button> 
                        </form> 
                    </div> 
                </div> 
            </div> 
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
