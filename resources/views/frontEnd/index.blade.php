<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Global Visioners International</title>
  <meta name="description" content="">
  <meta name="keywords" content="">  
  <link rel="shortcut icon" href="{{ asset('assets/custom-images/gvi-icon.png')}}" />
  <link href="{{ asset('append-template/assets/img/apple-touch-icon.png')}}" rel="apple-touch-icon">  
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
 
  <link href="{{ asset('append-template/assets/vendor/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet">
  <link href="{{ asset('append-template/assets/vendor/bootstrap-icons/bootstrap-icons.css')}}" rel="stylesheet">
  <link href="{{ asset('append-template/assets/vendor/aos/aos.css')}}" rel="stylesheet">
  <link href="{{ asset('append-template/assets/vendor/glightbox/css/glightbox.min.css')}}" rel="stylesheet">
  <link href="{{ asset('append-template/assets/vendor/swiper/swiper-bundle.min.css')}}" rel="stylesheet"> 
  <link href="{{ asset('append-template/assets/css/main.css')}}" rel="stylesheet"> 
</head>

<body class="index-page">

  <header id="header" class="header d-flex align-items-center fixed-top">
    <div class="container-fluid position-relative d-flex align-items-center justify-content-between">

      <a href="{{ route('index') }}" class="logo d-flex align-items-center me-auto me-xl-0"> 
        {{-- <img src="{{ asset('append-template/images/gvi-final.png')}}" alt=""> --}}
         <h1 class="sitename">GVI</h1><span>.</span> 
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="#hero" class="active">Home</a></li>
          <li><a href="#about">About</a></li> 
          {{-- <li><a href="#certification">Certifications</a></li>  --}}
          <li><a href="#contact">Contact</a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
       
      </nav> 
      <div class="d-flex align-items-center gap-2">
        <a class="btn-getstarted" data-bs-toggle="modal" data-bs-target="#calcModal"
           href="#" onclick="return false;"
           style="background:transparent; border:2px solid #DFC82E; color:#DFC82E; margin:0;">
            <i class="bi bi-calculator" style="margin-right:4px;"></i> Calculator
        </a>
        <a class="btn-getstarted" href="{{ route('login') }}" style="margin:0; background:transparent; border:2px solid #DFC82E; color:#DFC82E;">
            <i class="bi bi-box-arrow-in-right" style="margin-right:4px;"></i> Login
        </a>
      </div>
    </div>
  </header>

  <main class="main">  
    
    <!-- Hero Section -->
    <section id="hero" class="hero section dark-background"> 

      
      <img src="{{ asset('append-template/images/student-background.png') }}" alt="" data-aos="fade-in"> 
      <div class="container">
        <div class="row">
         
          <div class="text-center col-lg-12">
            <div class="text-center col-lg-12">
              <img  data-aos="fade-up" data-aos-delay="100" src="{{ asset('append-template/images/education-logo.png')}}" alt="" style="width:60%;position:inherit;display:inline"/>
            </div>
            
            <br>
            {{-- <h2 data-aos="fade-up" data-aos-delay="100">Welcome to Global Visioners International</h2>
            <p data-aos="fade-up" data-aos-delay="200">E-Commerce and Affiliate Marketing</p> --}}
          </div> 
        </div>
      </div> 
    </section><!-- /Hero Section -->

    

    <!-- About Section -->
    <section id="about" class="about section light-background">

      <div class="container" data-aos="fade-up" data-aos-delay="100">
        <div class="row align-items-xl-center gy-5">

          <div class="col-xl-5 content"> 
            <h2>About Us </h2>
            <p>Welcome to Global Visioners International, where innovation meets opportunity. As a premier platform in e-commerce and affiliate marketing, we are committed to empowering individuals and businesses worldwide.</p> 

            <p>At Global Visioners International, we don't just embrace the future—we create it. Join us in shaping a world of limitless possibilities.</p> 
          </div>

          <div class="col-xl-7">
            <div class="row gy-4 icon-boxes">

              <div class="col-md-6" data-aos="fade-up" data-aos-delay="200">
                <div class="icon-box">
                  <i class="bi bi-buildings"></i>
                  <h3>Vision</h3>
                  <p>To be the global leader in e-commerce and affiliate marketing, fostering a dynamic ecosystem where innovation, collaboration, and growth empower individuals and businesses to achieve their fullest potential.</p>
                </div>
              </div> <!-- End Icon Box -->

              <div class="col-md-6" data-aos="fade-up" data-aos-delay="300">
                <div class="icon-box">
                  <i class="bi bi-graph-up-arrow"></i>
                  <h3>Mission</h3>
                  <p>  Our mission is to transform the digital marketplace by providing accessible, innovative, and result-driven solutions in e-commerce and affiliate marketing.</p>
                </div>
              </div> <!-- End Icon Box -->

               

            </div>
          </div>

        </div>
      </div>

    </section><!-- /About Section -->

    <!-- Stats Section -->
    <section id="stats" class="stats section dark-background">

      <img src="{{ asset('append-template/assets/img/stats-bg.jpg')}}" alt="" data-aos="fade-in">

      <div class="container position-relative" data-aos="fade-up" data-aos-delay="100">

        <div class="row gy-4">

          <div class="col-lg-4 col-md-6">
            <div class="text-center stats-item w-100 h-100">
              <span data-purecounter-start="0" data-purecounter-end="232" data-purecounter-duration="1" class="purecounter"></span>
              <p>Partners</p>
            </div>
          </div><!-- End Stats Item -->

          <div class="col-lg-4 col-md-6">
            <div class="text-center stats-item w-100 h-100">
              <span data-purecounter-start="0" data-purecounter-end="6" data-purecounter-duration="1" class="purecounter"></span>
              <p>Projects</p>
            </div>
          </div><!-- End Stats Item -->

          <div class="col-lg-4 col-md-6">
            <div class="text-center stats-item w-100 h-100">
              <span data-purecounter-start="0" data-purecounter-end="5" data-purecounter-duration="1" class="purecounter"></span>
              <p>Countries Served:</p>
            </div>
          </div><!-- End Stats Item -->

          

        </div>

      </div>

    </section><!-- /Stats Section -->

    <!-- Services Section -->
    

    <!-- Features Section -->
    <section id="features" class="features section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>CEO Message</h2>
        <p>Empowering businesses and entrepreneurs through innovative e-commerce and affiliate marketing solutions.</p>
      </div><!-- End Section Title -->

      <div class="container">

        <div class="row gy-4 align-items-center features-item">
          <div class="order-2 col-lg-12 order-lg-1" data-aos="fade-up" data-aos-delay="200">
            
            <p>
              At Global Visioners International, we are committed to driving innovation and success in e-commerce, affiliate marketing, and the real estate industry. Our platform empowers businesses and entrepreneurs worldwide by connecting them to new opportunities and markets. <br/><br/>

              In the dynamic real estate sector, we are focused on helping businesses leverage digital solutions for growth and success. We believe in harnessing the power of technology to simplify processes, enhance decision-making, and unlock new potential for our clients.<br/><br/>
              
              Our vision is to create a global network where businesses and individuals can collaborate, grow, and achieve their aspirations. By fostering innovation and embracing emerging trends, we ensure that our partners stay ahead in a rapidly evolving market landscape.<br/><br/>
              
              Thank you for trusting us as your partner in growth. Together, we’ll continue to create new opportunities, drive impactful change, and shape the future of industries across the globe
            </p> 
          </div> 
        </div><!-- Features Item -->

        <div class="row gy-4 align-items-stretch justify-content-between features-item ">
          <div class="col-lg-6 d-flex align-items-center features-img-bg" data-aos="zoom-out">
            <img src="{{ asset('append-template/assets/img/features-light-3.jpg') }}" class="img-fluid" alt="">
          </div>
          <div class="col-lg-5 d-flex justify-content-center flex-column" data-aos="fade-up">
            <h3>Join The GVI Family</h3>
            <p>Become a part of the Global Visioners International family and unlock limitless opportunities for growth, success, and collaboration. Together, we can achieve extraordinary results!.</p>
            <ul>
              <li>
                <i class="bi bi-check"></i> <span>Strong Foundation.</span> 
              </li>
              <li><i class="bi bi-check"></i><span> Unlimited Growth.</span></li>
              <li><i class="bi bi-check"></i> <span>Global Reach</span>.</li>
            </ul>
           
          </div>
        </div><!-- Features Item -->

      </div>

    </section><!-- /Features Section -->

    
    

  

    <!-- Call To Action Section -->
    <section id="call-to-action" class="call-to-action section dark-background">

      <img src="{{ asset('append-template/assets/img/cta-bg.jpg')}}" alt="">

      <div class="container">
        <div class="row justify-content-center" data-aos="zoom-in" data-aos-delay="100">
          <div class="col-xl-10">
            <div class="text-center">
              <h3>Call To Action</h3>
              <p>Ready to take your business to the next level? Join us today and unlock new opportunities for growth, innovation, and success!</p>
              <a class="cta-btn" href="#" tel="232323232">Call To Action</a>
            </div>
          </div>
        </div>
      </div>

    </section><!-- /Call To Action Section -->

    <!-- Testimonials Section -->
    {{-- <section id="certification" class="testimonials section light-background">

      <div class="container">

        <div class="row align-items-center">

          <div class="col-lg-4 info" data-aos="fade-up" data-aos-delay="100">
            <h3>Certifications</h3>
            <p>
              Our certifications reflect our commitment to excellence, quality, and industry standards. We are proud to hold recognized credentials that empower us to deliver trusted solutions in e-commerce, affiliate marketing, and real estate
            </p>
          </div>

          <div class="col-lg-8" data-aos="fade-up" data-aos-delay="200">

            <div class="swiper init-swiper">
              <script type="application/json" class="swiper-config">
                {
                  "loop": true,
                  "speed": 600,
                  "autoplay": {
                    "delay": 5000
                  },
                  "slidesPerView": "auto",
                  "pagination": {
                    "el": ".swiper-pagination",
                    "type": "bullets",
                    "clickable": true
                  }
                }
              </script>
              <div class="swiper-wrapper">

                <div class="swiper-slide">
                  <div class="testimonial-item">
                    <div class="d-flex">
                      
                      <div>
                        <h3>Federal Board of Revenue</h3> 
                      </div>
                    </div>
                    <p>
                      <img src="{{ asset('append-template/images/certificate-1-500x500.png')}}" class="" alt="">
                    </p>
                  </div>
                </div><!-- End testimonial item -->


                <div class="swiper-slide">
                  <div class="testimonial-item">
                    <div class="d-flex">
                      
                      <div>
                        <h3>Federal Board of Revenue</h3> 
                      </div>
                    </div>
                    <p>
                      <img src="{{ asset('append-template/images/certificate-1-500x500.png')}}" class="" alt="">
                    </p>
                  </div>
                </div><!-- End testimonial item -->


                <div class="swiper-slide">
                  <div class="testimonial-item">
                    <div class="d-flex">
                      
                      <div>
                        <h3>Federal Board of Revenue</h3> 
                      </div>
                    </div>
                    <p>
                      <img src="{{ asset('append-template/images/certificate-1-500x500.png')}}" class="" alt="">
                    </p>
                  </div>
                </div><!-- End testimonial item -->

                <div class="swiper-slide">
                  <div class="testimonial-item">
                    <div class="d-flex">
                      
                      <div>
                        <h3>Federal Board of Revenue</h3> 
                      </div>
                    </div>
                    <p>
                      <img src="{{ asset('append-template/images/certificate-1-500x500.png')}}" class="" alt="">
                    </p>
                  </div>
                </div><!-- End testimonial item -->

               

              </div>
              <div class="swiper-pagination"></div>
            </div>

          </div>

        </div>

      </div>

    </section> --}}
    <!-- /Testimonials Section -->

     
    <!-- Contact Section -->
    <section id="contact" class="contact section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>Contact</h2>
        <p>Get in Touch – We’re Here to Help!</p>
      </div><!-- End Section Title -->

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row gy-4">

          <div class="col-lg-6">

            <div class="row gy-4">
              <div class="col-md-6">
                <div class="info-item" data-aos="fade" data-aos-delay="200">
                  <i class="bi bi-geo-alt"></i>
                  <h3>Address</h3>
                  <p>A108 Adam Street</p>
                  <p>New York, NY 535022</p>
                </div>
              </div><!-- End Info Item -->

              <div class="col-md-6">
                <div class="info-item" data-aos="fade" data-aos-delay="300">
                  <i class="bi bi-telephone"></i>
                  <h3>Call Us</h3>
                  <p>-----------</p>
                  <p>-----------</p>
                </div>
              </div><!-- End Info Item -->

              <div class="col-md-6">
                <div class="info-item" data-aos="fade" data-aos-delay="400">
                  <i class="bi bi-envelope"></i>
                  <h3>Email Us</h3>
                  <p>info@globalvisioners.com</p>
                  <p>contact@globalvisioners.com</p>
                </div>
              </div><!-- End Info Item -->

              <div class="col-md-6">
                <div class="info-item" data-aos="fade" data-aos-delay="500">
                  <i class="bi bi-clock"></i>
                  <h3>Open Hours</h3>
                  <p>Mon - Tue - Wed - Thu - Fri - Sat - Sun</p>
                  <p>24/7</p>
                </div>
              </div><!-- End Info Item -->

            </div>

          </div>

          <div class="col-lg-6">
            <form action="forms/contact.php" method="post" class="php-email-form" data-aos="fade-up" data-aos-delay="200">
              <div class="row gy-4">

                <div class="col-md-6">
                  <input type="text" name="name" class="form-control" placeholder="Your Name" required="">
                </div>

                <div class="col-md-6 ">
                  <input type="email" class="form-control" name="email" placeholder="Your Email" required="">
                </div>

                <div class="col-12">
                  <input type="text" class="form-control" name="subject" placeholder="Subject" required="">
                </div>

                <div class="col-12">
                  <textarea class="form-control" name="message" rows="6" placeholder="Message" required=""></textarea>
                </div>

                <div class="text-center col-12">
                  <div class="loading">Loading</div>
                  <div class="error-message"></div>
                  <div class="sent-message">Your message has been sent. Thank you!</div>

                  <button type="submit">Send Message</button>
                </div>

              </div>
            </form>
          </div><!-- End Contact Form -->

        </div>

      </div>

    </section><!-- /Contact Section -->

  </main>

  <footer id="footer" class="footer position-relative light-background">

    <div class="container footer-top">
      <div class="row gy-4">
        <div class="col-lg-5 col-md-12 footer-about">
          <a href="" class="logo d-flex align-items-center">
            <span class="sitename">Global Visioners International</span>
          </a>
          <p>Explore Our Projects and See How We Drive Success! Discover the innovative solutions and impactful results we deliver across e-commerce, affiliate marketing, and real estate, empowering businesses to grow and thrive in the digital age.</p>
          <div class="mt-4 social-links d-flex">
            <a href="https://www.snapchat.com/Alawan,s786">  <i class="bi bi-snapchat"></i></a>
            <a href="https://www.facebook.com/Al Awan Visioners"><i class="bi bi-facebook"></i></a>
            <a href="https://www.tiktok.com/en/Al Awan Visioners">  <i class="bi bi-tiktok"></i> </a>
            <a href="https://www.instagram.com/Al Awan Visioners"><i class="bi bi-instagram"></i></a>
          </div>
        </div>

        <div class="col-lg-2 col-6 footer-links">
          <h4>Useful Links</h4>
          <ul>
            <li><a href="#hero">Home</a></li>
            <li><a href="#about">About us</a></li>
            
            <li><a href="#">Terms of service</a></li>
            <li><a href="#">Privacy policy</a></li>
          </ul>
        </div>

        

        <div class="text-center col-lg-5 col-md-12 footer-contact text-md-start">
          <h4>Contact Us</h4> 
          <p>New York, NY 535022</p>
          <p>United States</p>
          <p class="mt-4"><strong>Mobile:</strong> <span>-------</span></p>
          <p class=""><strong>Phone:</strong> <span>-------</span></p>
          <p><strong>Email:</strong> <span>info@globalvisioners.com</span></p>
        </div>

      </div>
    </div>

    <div class="container mt-4 text-center copyright">
      <p>© <span>Copyright</span> <strong class="sitename">Global Visioners International</strong> <span>All Rights Reserved</span></p>
      <div class="credits"> 
        Designed by <a href="">Dev 97</a>
      </div>
    </div>

  </footer>

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Preloader -->
  <div id="preloader"></div>

  <!-- Vendor JS Files -->
  <script src="{{ asset('append-template/assets/vendor/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
  <script src="{{ asset('append-template/assets/vendor/php-email-form/validate.js')}}"></script>
  <script src="{{ asset('append-template/assets/vendor/aos/aos.js')}}"></script>
  <script src="{{ asset('append-template/assets/vendor/glightbox/js/glightbox.min.js')}}"></script>
  <script src="{{ asset('append-template/assets/vendor/purecounter/purecounter_vanilla.js')}}"></script>
  <script src="{{ asset('append-template/assets/vendor/imagesloaded/imagesloaded.pkgd.min.js')}}"></script>
  <script src="{{ asset('append-template/assets/vendor/isotope-layout/isotope.pkgd.min.js')}}"></script>
  <script src="{{ asset('append-template/assets/vendor/swiper/swiper-bundle.min.js')}}"></script> 
  <!-- Main JS File -->
  <script src="{{ asset('append-template/assets/js/main.js')}}"></script>

<!-- ── Calculator Modal ──────────────────────────────── -->
<div class="modal fade" id="calcModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width:680px;">
    <div class="modal-content" style="border:none; border-radius:0; overflow:hidden; box-shadow:0 24px 64px rgba(0,0,0,0.65); background:#0e1015;">

      <!-- Header -->
      <div style="background:#DFC82E; padding:1rem 1.4rem; display:flex; justify-content:space-between; align-items:center;">
        <div style="display:flex; align-items:center; gap:.6rem;">
          <i class="bi bi-calculator-fill" style="font-size:1.1rem; color:#000;"></i>
          <div>
            <div style="font-size:.97rem; font-weight:800; color:#000; line-height:1.2;">Investment Calculator</div>
            <div style="font-size:.7rem; color:#3a2f00; font-weight:600; letter-spacing:.4px;">25-MONTH SAVING PLAN</div>
          </div>
        </div>
        <button type="button" data-bs-dismiss="modal"
                style="background:rgba(0,0,0,0.15); border:none; color:#000; width:28px; height:28px; display:flex; align-items:center; justify-content:center; font-size:1rem; cursor:pointer; border-radius:0; font-weight:700; line-height:1;">
          &times;
        </button>
      </div>

      <div style="padding:1.4rem 1.5rem 1.5rem;">

        <!-- Input row -->
        <div style="margin-bottom:1rem;">
          <label style="font-size:.68rem; font-weight:700; color:#9ca3af; text-transform:uppercase; letter-spacing:.7px; display:block; margin-bottom:.35rem;">
            Invest Amount (Rs.)
          </label>
          <div style="display:flex; height:46px;">
            <input type="number" id="calcAmount" min="1" placeholder="e.g. 4000"
                   style="flex:1; height:100%; padding:0 1rem; background:#1a1d24; border:1.5px solid #2a2d36; border-right:none; border-radius:0; font-size:1rem; color:#fff; outline:none; transition:border-color .15s; box-sizing:border-box;"
                   onfocus="this.style.borderColor='#DFC82E'"
                   onblur="this.style.borderColor='#2a2d36'"
                   onkeydown="if(event.key==='Enter') calcRun()">
            <button onclick="calcRun()"
                    style="height:100%; padding:0 1.5rem; background:#DFC82E; color:#000; border:none; border-radius:0; font-size:.88rem; font-weight:800; cursor:pointer; white-space:nowrap; letter-spacing:.3px; transition:background .15s;"
                    onmouseover="this.style.background='#c9b428'" onmouseout="this.style.background='#DFC82E'">
              CALCULATE
            </button>
          </div>
        </div>

        <!-- Options -->
        <div style="display:flex; gap:1.5rem; margin-bottom:1.1rem; flex-wrap:wrap; padding:.65rem .9rem; background:#1a1d24; border-left:3px solid #DFC82E;">
          <label style="display:flex; align-items:center; gap:.45rem; font-size:.84rem; color:#d1d5db; cursor:pointer; margin:0;">
            <input type="checkbox" id="calcAdb" onchange="calcRun()" style="accent-color:#DFC82E; width:15px; height:15px; border-radius:0;">
            ADB Option <span style="color:#DFC82E; font-size:.75rem; font-weight:600;">(Rs. 3 / 1000)</span>
          </label>
          <label style="display:flex; align-items:center; gap:.45rem; font-size:.84rem; color:#d1d5db; cursor:pointer; margin:0;">
            <input type="checkbox" id="calcFisp" onchange="calcRun()" style="accent-color:#DFC82E; width:15px; height:15px; border-radius:0;">
            FISP Option <span style="color:#DFC82E; font-size:.75rem; font-weight:600;">(Rs. 4 / 1000)</span>
          </label>
        </div>

        <!-- Results -->
        <div id="calcResults" style="display:none;">

          <!-- 4 cards -->
          <div style="display:grid; grid-template-columns:1fr 1fr; gap:1px; margin-bottom:1rem; background:#2a2d36;">
            <div style="background:#0e1015; padding:.85rem 1rem; border-left:3px solid #DFC82E;">
              <div style="font-size:.65rem; color:#DFC82E; font-weight:700; text-transform:uppercase; letter-spacing:.6px; margin-bottom:.3rem;">Sum Assured</div>
              <div id="rSumAssured" style="font-size:1.05rem; font-weight:700; color:#fff;"></div>
            </div>
            <div style="background:#0e1015; padding:.85rem 1rem; border-left:3px solid #4ade80;">
              <div style="font-size:.65rem; color:#4ade80; font-weight:700; text-transform:uppercase; letter-spacing:.6px; margin-bottom:.3rem;">Maturity Amount</div>
              <div id="rMaturity" style="font-size:1.05rem; font-weight:700; color:#fff;"></div>
            </div>
            <div style="background:#0e1015; padding:.85rem 1rem; border-left:3px solid #60a5fa;">
              <div style="font-size:.65rem; color:#60a5fa; font-weight:700; text-transform:uppercase; letter-spacing:.6px; margin-bottom:.3rem;">Death Benefit</div>
              <div id="rNDB" style="font-size:1.05rem; font-weight:700; color:#fff;"></div>
            </div>
            <div style="background:#1a1500; padding:.85rem 1rem; border-left:3px solid #DFC82E;">
              <div style="font-size:.65rem; color:#DFC82E; font-weight:700; text-transform:uppercase; letter-spacing:.6px; margin-bottom:.3rem;">Total Return</div>
              <div id="rTotal" style="font-size:1.05rem; font-weight:700; color:#DFC82E;"></div>
            </div>
          </div>

          <!-- Breakdown -->
          <div style="background:#1a1d24; border-left:3px solid #2a2d36; padding:.7rem 1rem; font-size:.8rem; margin-bottom:1rem;">
            <div style="display:flex; justify-content:space-between; padding:.28rem 0; border-bottom:1px solid #2a2d36;">
              <span style="color:#6b7280;">Invest Amount</span>
              <span id="rInvest" style="font-weight:600; color:#e5e7eb;"></span>
            </div>
            <div style="display:flex; justify-content:space-between; padding:.28rem 0; border-bottom:1px solid #2a2d36; align-items:center;">
              <span style="color:#6b7280;">Processing Fee <span style="font-size:.7rem;">(Month 1 only)</span></span>
              <span id="rProcessingFee" style="font-weight:600; color:#e5e7eb;"></span>
            </div>
            <div id="rAdbRow" style="display:none; justify-content:space-between; padding:.28rem 0; border-bottom:1px solid #2a2d36;">
              <span style="color:#6b7280;">ADB <span style="font-size:.7rem;">× 25 months</span></span>
              <span id="rAdbVal" style="font-weight:600; color:#e5e7eb;"></span>
            </div>
            <div id="rFispRow" style="display:none; justify-content:space-between; padding:.28rem 0;">
              <span style="color:#6b7280;">FISP <span style="font-size:.7rem;">× 25 months</span></span>
              <span id="rFispVal" style="font-weight:600; color:#e5e7eb;"></span>
            </div>
          </div>

          <!-- Schedule button -->
          <button onclick="calcShowSchedule()"
                  style="width:100%; padding:.6rem; background:transparent; color:#DFC82E; border:1.5px solid #DFC82E; border-radius:0; font-size:.87rem; font-weight:700; cursor:pointer; letter-spacing:.3px; transition:all .15s;"
                  onmouseover="this.style.background='#DFC82E'; this.style.color='#000';"
                  onmouseout="this.style.background='transparent'; this.style.color='#DFC82E';">
            <i class="bi bi-calendar3" style="margin-right:6px;"></i> VIEW 25-MONTH PAYMENT SCHEDULE
          </button>
        </div>

      </div>
    </div>
  </div>
</div>

<!-- ── Payment Schedule Modal ─────────────────────────── -->
<div class="modal fade" id="calcScheduleModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width:min(96vw,700px);">
    <div class="modal-content" style="border:none; border-radius:14px; overflow:hidden; box-shadow:0 24px 64px rgba(0,0,0,0.55); background:#111318;">

      <div style="padding:1.2rem 1.5rem .5rem; display:flex; justify-content:space-between; align-items:flex-start;">
        <div>
          <div style="font-size:1rem; font-weight:700; color:#fff; display:flex; align-items:center; gap:.45rem;">
            <span style="background:#DFC82E; color:#000; border-radius:6px; width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center; font-size:.8rem;">
              <i class="bi bi-calendar3"></i>
            </span>
            25-Month Payment Schedule
          </div>
          <div id="schedSubtitle" style="font-size:.74rem; color:#6b7280; margin-top:.3rem; padding-left:37px;"></div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" style="opacity:.5;"></button>
      </div>

      <div style="height:1px; background:linear-gradient(90deg,#DFC82E33,#DFC82E88,#DFC82E33); margin:0 1.5rem .15rem;"></div>

      <div style="overflow-x:auto; max-height:58vh; overflow-y:auto; margin:.5rem 0;">
        <table style="width:100%; border-collapse:collapse; font-size:.87rem;">
          <thead>
            <tr style="background:#1c1f26; position:sticky; top:0; z-index:2;">
              <th style="padding:.65rem 1rem; font-size:.7rem; font-weight:700; color:#DFC82E; text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid #2a2d36; text-align:center; width:70px;">Month</th>
              <th style="padding:.65rem 1rem; font-size:.7rem; font-weight:700; color:#9ca3af; text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid #2a2d36; text-align:right;">Instalment</th>
              <th style="padding:.65rem 1rem; font-size:.7rem; font-weight:700; color:#9ca3af; text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid #2a2d36;">Extra Charges</th>
              <th style="padding:.65rem 1rem; font-size:.7rem; font-weight:700; color:#9ca3af; text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid #2a2d36; text-align:right;">Total Payable</th>
            </tr>
          </thead>
          <tbody id="schedBody"></tbody>
        </table>
      </div>

      <div style="height:1px; background:#2a2d36; margin:0 1.5rem;"></div>
      <div id="schedFooter" style="padding:.75rem 1.5rem; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:.4rem; font-size:.81rem;"></div>

      <div style="padding:.85rem 1.5rem 1.2rem; display:flex; gap:.5rem; justify-content:flex-end; border-top:1px solid #2a2d36;">
        <button type="button" data-bs-dismiss="modal"
                style="padding:.4rem .9rem; font-size:.82rem; border-radius:7px; border:1px solid #2a2d36; background:transparent; color:#9ca3af; cursor:pointer;">
          Close
        </button>
        <button onclick="bootstrap.Modal.getInstance(document.getElementById('calcScheduleModal')).hide(); new bootstrap.Modal(document.getElementById('calcModal')).show();"
                style="padding:.4rem .9rem; font-size:.82rem; border-radius:7px; border:1.5px solid #DFC82E; background:transparent; color:#DFC82E; cursor:pointer; font-weight:600;">
          <i class="bi bi-arrow-left" style="margin-right:4px;"></i> Back to Calculator
        </button>
      </div>

    </div>
  </div>
</div>

<script>
(function () {
  var _amt = 0, _adb = false, _fisp = false;

  // Rate and fee from settings
  var USD_RATE    = {{ (float)($setting->usd ?? 278) }};          // PKR per $1
  var FEE_USD     = {{ (float)($setting->saving_registration_fee ?? 5) }};  // $5
  var FEE_PKR     = USD_RATE * FEE_USD;                           // e.g. Rs. 1,390

  function fmt(n) {
    return 'Rs. ' + Number(n).toLocaleString('en-PK', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
  }

  function fmtFee() {
    return fmt(FEE_PKR) + ' <span style="color:#6b7280; font-size:.74rem;">(= $' + FEE_USD + ')</span>';
  }

  window.calcRun = function () {
    var raw = parseFloat(document.getElementById('calcAmount').value);
    if (!raw || raw <= 0) return;
    _amt  = raw;
    _adb  = document.getElementById('calcAdb').checked;
    _fisp = document.getElementById('calcFisp').checked;

    var sa  = raw * 25;
    var mat = sa * 0.5;
    var adbC  = _adb  ? (raw / 1000) * 3 : 0;
    var fispC = _fisp ? (raw / 1000) * 4 : 0;

    document.getElementById('rSumAssured').textContent  = fmt(sa);
    document.getElementById('rMaturity').textContent    = fmt(mat);
    document.getElementById('rTotal').textContent       = fmt(sa + mat);
    document.getElementById('rNDB').textContent         = fmt(sa);
    document.getElementById('rInvest').textContent      = fmt(raw);
    document.getElementById('rProcessingFee').innerHTML = fmtFee();

    var aRow = document.getElementById('rAdbRow');
    var fRow = document.getElementById('rFispRow');
    if (_adb)  { aRow.style.display = 'flex'; document.getElementById('rAdbVal').innerHTML  = fmt(adbC) + ' <span style="color:#6b7280;font-size:.72rem;">/mo = ' + fmt(adbC * 25) + ' total</span>';  }
    else         aRow.style.display = 'none';
    if (_fisp) { fRow.style.display = 'flex'; document.getElementById('rFispVal').innerHTML = fmt(fispC) + ' <span style="color:#6b7280;font-size:.72rem;">/mo = ' + fmt(fispC * 25) + ' total</span>'; }
    else         fRow.style.display = 'none';

    document.getElementById('calcResults').style.display = 'block';
  };

  window.calcShowSchedule = function () {
    if (_amt <= 0) return;
    // ADB & FISP are charged every single month for all 25 months
    var adbC         = _adb  ? (_amt / 1000) * 3 : 0;
    var fispC        = _fisp ? (_amt / 1000) * 4 : 0;
    var monthlyExtra = adbC + fispC;  // recurring every month

    var sub = 'Monthly instalment: ' + fmt(_amt);
    if (_adb || _fisp) {
      var opts = [];
      if (_adb)  opts.push('ADB ' + fmt(adbC) + '/mo');
      if (_fisp) opts.push('FISP ' + fmt(fispC) + '/mo');
      sub += '  ·  ' + opts.join(' + ') + ' (all 25 months)';
    }
    document.getElementById('schedSubtitle').textContent = sub;

    var tbody = document.getElementById('schedBody');
    tbody.innerHTML = '';
    var grand = 0;

    for (var m = 1; m <= 25; m++) {
      var isFirst  = m === 1;
      // Processing fee only Month 1 — ADB/FISP every month
      var extra    = monthlyExtra + (isFirst ? FEE_PKR : 0);
      var rowTotal = _amt + extra;
      grand       += rowTotal;
      var bg       = isFirst ? '#1c1f26' : (m % 2 === 0 ? '#161820' : '#111318');

      var parts = [];
      if (isFirst) parts.push(fmt(FEE_PKR) + ' <span style="font-size:.71rem;color:#9ca3af;">(=$' + FEE_USD + ' fee)</span>');
      if (_adb)    parts.push('ADB ' + fmt(adbC));
      if (_fisp)   parts.push('FISP ' + fmt(fispC));
      var extraText = parts.length
        ? '<span style="color:#DFC82E;">' + parts.join(' + ') + '</span>'
        : '<span style="color:#374151;">—</span>';

      var tr = document.createElement('tr');
      tr.style.background = bg;
      tr.innerHTML =
        '<td style="padding:.62rem 1rem; border-bottom:1px solid #1e2028; text-align:center; font-weight:' + (isFirst?'700':'400') + '; color:' + (isFirst?'#DFC82E':'#4b5563') + ';">' + m + '</td>' +
        '<td style="padding:.62rem 1rem; border-bottom:1px solid #1e2028; text-align:right; color:#d1d5db; font-weight:500;">' + fmt(_amt) + '</td>' +
        '<td style="padding:.62rem 1rem; border-bottom:1px solid #1e2028; font-size:.8rem;">' + extraText + '</td>' +
        '<td style="padding:.62rem 1rem; border-bottom:1px solid #1e2028; text-align:right; font-weight:' + (isFirst?'700':'600') + '; color:' + (isFirst?'#DFC82E':'#e5e7eb') + ';">' + fmt(rowTotal) + '</td>';
      tbody.appendChild(tr);
    }

    var note = '25 instalments';
    if (_adb || _fisp) note += '  ·  ADB/FISP applied every month';
    note += '  ·  Processing fee ' + fmt(FEE_PKR) + ' (=$' + FEE_USD + ') in Month 1 only';

    document.getElementById('schedFooter').innerHTML =
      '<span style="color:#4b5563; font-size:.78rem;">' + note + '</span>' +
      '<span style="font-weight:700; color:#DFC82E;">Grand Total: ' + fmt(grand) + '</span>';

    bootstrap.Modal.getInstance(document.getElementById('calcModal')).hide();
    new bootstrap.Modal(document.getElementById('calcScheduleModal')).show();
  };

  // Reset on close
  document.getElementById('calcModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('calcResults').style.display = 'none';
    document.getElementById('calcAmount').value = '';
    document.getElementById('calcAdb').checked  = false;
    document.getElementById('calcFisp').checked = false;
    _amt = 0; _adb = false; _fisp = false;
  });
})();
</script>

</body>

</html>
