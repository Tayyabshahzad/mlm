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

      <a class="btn-getstarted" style=""  href="{{ route('login') }}">Login</a>

    </div>
  </header>

  <main class="main">  
    
    <!-- Hero Section -->
    <section id="hero" class="hero section dark-background"> 

      
      <img src="{{ asset('append-template/images/student-background.png') }}" alt="" data-aos="fade-in"> 
      <div class="container">
        <div class="row">
         
          <div class="col-lg-12 text-center">
            <div class="col-lg-12 text-center">
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
            <div class="stats-item text-center w-100 h-100">
              <span data-purecounter-start="0" data-purecounter-end="232" data-purecounter-duration="1" class="purecounter"></span>
              <p>Partners</p>
            </div>
          </div><!-- End Stats Item -->

          <div class="col-lg-4 col-md-6">
            <div class="stats-item text-center w-100 h-100">
              <span data-purecounter-start="0" data-purecounter-end="6" data-purecounter-duration="1" class="purecounter"></span>
              <p>Projects</p>
            </div>
          </div><!-- End Stats Item -->

          <div class="col-lg-4 col-md-6">
            <div class="stats-item text-center w-100 h-100">
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
          <div class="col-lg-12 order-2 order-lg-1" data-aos="fade-up" data-aos-delay="200">
            
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
                      <img src="{{ asset('append-template/images/certificate-1-500x500.png')}}" class=" " alt="">
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
                      <img src="{{ asset('append-template/images/certificate-1-500x500.png')}}" class=" " alt="">
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
                      <img src="{{ asset('append-template/images/certificate-1-500x500.png')}}" class=" " alt="">
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
                      <img src="{{ asset('append-template/images/certificate-1-500x500.png')}}" class=" " alt="">
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

                <div class="col-12 text-center">
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
          <div class="social-links d-flex mt-4">
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

        

        <div class="col-lg-5 col-md-12 footer-contact text-center text-md-start">
          <h4>Contact Us</h4> 
          <p>New York, NY 535022</p>
          <p>United States</p>
          <p class="mt-4"><strong>Mobile:</strong> <span>-------</span></p>
          <p class=""><strong>Phone:</strong> <span>-------</span></p>
          <p><strong>Email:</strong> <span>info@globalvisioners.com</span></p>
        </div>

      </div>
    </div>

    <div class="container copyright text-center mt-4">
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

</body>

</html>