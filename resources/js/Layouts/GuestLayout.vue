<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue'; 

defineProps({
    height: {
        type: String,
    },  
}); 
const screenWidth = ref(window.innerWidth);
const baseURL = ref('http://mlm.test/'); 
const updateScreenWidth = () => {
  screenWidth.value = window.innerWidth;
};
onMounted(() => {
  window.addEventListener('resize', updateScreenWidth);
});
onUnmounted(() => {
  window.removeEventListener('resize', updateScreenWidth);
});
const isMobile = computed(() => screenWidth.value <= 768);
</script>

<template>

    <!--begin::Main-->
		<div class="d-flex flex-column flex-root"  
		  :style="isMobile ? null : { height: height + 'rem' }"> 
			<!--begin::Login-->
			<div class="login login-1 login-signin-on d-flex flex-column flex-lg-row flex-column-fluid bg-white" id="kt_login">
				<!--begin::Aside-->
				<div class="login-aside d-flex flex-row-auto bgi-size-cover bgi-no-repeat p-10 p-lg-10" 
				:style="{ backgroundImage: `url(${baseURL}assets/media/bg/bg-4.jpg)` }">
					<!--begin: Aside Container-->
					<div class="d-flex flex-row-fluid flex-column justify-content-between">
						<Link :href="route('index')"  class="flex-column-auto mt-5 pb-lg-0 pb-10">
							<img :src="`${baseURL}assets/media/logos/logo-letter-1.png`" class="max-h-70px" alt="" />
						</Link>
						
						<!--begin: Aside content-->
						<div class="flex-column-fluid d-flex flex-column justify-content-center">
							<h3 class="font-size-h1 mb-5 text-white">Welcome to <br> Global Visioners International!</h3>
							<p class="font-weight-lighter text-white opacity-80">The ultimate Bootstrap, Angular 8, React &amp; VueJS admin theme framework for next generation web apps.</p>
						</div>
						<!--end: Aside content-->
						<!--begin: Aside footer for desktop-->
						<div class="d-none flex-column-auto d-lg-flex justify-content-between mt-10">
							<div class="opacity-70 font-weight-bold text-white">
								<Link :href="route('index')"  class="text-white">
									© 2020 GVI
                            	</Link>   
							</div>
							<div class="d-flex">
								<a href="#" class="text-white">Privacy Policy</a>  
								<a href="#" class="text-white ml-10">Terms & Condition</a> 
								<a href="#" class="text-white ml-10">Contact</a>  
							</div>
						</div>
						<!--end: Aside footer for desktop-->
					</div>
					<!--end: Aside Container-->
				</div>
				<!--begin::Aside-->
				<slot />
			</div>
			<!--end::Login-->
		</div>
		<!--end::Main-->


   
</template>
