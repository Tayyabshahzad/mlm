import{T as b,c as m,w as i,o as n,a,u as s,Z as h,f as y,t as v,g as f,b as e,d as u,j as c,e as _,i as p,k as g,n as k}from"./app-sgKYE_2L.js";import{_ as V}from"./GuestLayout-BkQz8dXS.js";import{_ as x}from"./InputError-BySU8Plo.js";const P={key:0,class:"mb-4 text-sm font-medium text-green-600"},B={class:"d-flex flex-column flex-row-fluid position-relative p-7 overflow-hidden"},C={class:"position-absolute top-0 right-0 text-right mt-5 mb-15 mb-lg-0 flex-column-auto justify-content-center py-5 px-10"},j={class:"d-flex flex-column-fluid flex-center mt-30 mt-lg-0"},I={class:"login-form login-signin"},N={class:"form-group"},S={class:"form-group"},T={class:"form-group d-flex flex-wrap justify-content-between align-items-center"},q=["disabled"],z={__name:"Login",props:{canResetPassword:{type:Boolean},status:{type:String}},setup(r){const o=b({email:"",password:"",remember:!1}),w=()=>{o.post(route("login"),{onFinish:()=>o.reset("password")})};return(d,t)=>(n(),m(V,{height:"56.8"},{default:i(()=>[a(s(h),{title:"Global Visioners International : "}),r.status?(n(),y("div",P,v(r.status),1)):f("",!0),e("div",B,[e("div",C,[t[3]||(t[3]=e("span",{class:"font-weight-bold text-dark-50"},"Dont have an account yet?",-1)),a(s(c),{href:d.route("register"),class:"font-weight-bold ml-2",id:"kt_login_signup"},{default:i(()=>t[2]||(t[2]=[u(" Register! ")])),_:1},8,["href"])]),e("div",j,[e("div",I,[t[5]||(t[5]=e("div",{class:"text-center mb-10 mb-lg-20"},[e("h3",{class:"font-size-h1"},"Sign In"),e("p",{class:"text-muted font-weight-bold"},"Enter your email and password")],-1)),e("form",{class:"form",novalidate:"novalidate",onSubmit:_(w,["prevent"])},[e("div",N,[p(e("input",{class:"form-control form-control-solid h-auto py-5 px-6","onUpdate:modelValue":t[0]||(t[0]=l=>s(o).email=l),required:"",autofocus:"",type:"email",name:"email",placeholder:"Email",autocomplete:"off"},null,512),[[g,s(o).email]]),a(x,{class:"mt-2",message:s(o).errors.email},null,8,["message"])]),e("div",S,[p(e("input",{class:"form-control form-control-solid h-auto py-5 px-6",type:"password","onUpdate:modelValue":t[1]||(t[1]=l=>s(o).password=l),required:"",placeholder:"Password",name:"password",autocomplete:"off"},null,512),[[g,s(o).password]]),a(x,{class:"mt-2",message:s(o).errors.password},null,8,["message"])]),e("div",T,[r.canResetPassword?(n(),m(s(c),{key:0,href:d.route("password.request"),class:"text-dark-50 text-hover-primary my-3 mr-2",id:"kt_login_forgot"},{default:i(()=>t[4]||(t[4]=[u(" Forgot Password ? ")])),_:1},8,["href"])):f("",!0),e("button",{class:k([{"opacity-25":s(o).processing},"btn btn-primary font-weight-bold px-9 py-4 my-3"]),disabled:s(o).processing,id:"kt_login_signin_submit"},"Log In",10,q)])],32)])]),t[6]||(t[6]=e("div",{class:"d-flex d-lg-none flex-column-auto flex-column flex-sm-row justify-content-between align-items-center mt-5 p-5"},[e("div",{class:"text-dark-50 font-weight-bold order-2 order-sm-1 my-2"},"© 2020 Global Visioners International"),e("div",{class:"d-flex order-1 order-sm-2 my-2"},[e("a",{href:"#",class:"text-dark-75"},"Privacy Policy"),e("a",{href:"#",class:"text-dark-75 ml-4"},"Terms & Condition"),e("a",{href:"#",class:"text-dark-75 ml-4"},"Contact")])],-1))])]),_:1}))}};export{z as default};