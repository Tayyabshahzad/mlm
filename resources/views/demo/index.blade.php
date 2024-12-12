<!DOCTYPE html>
<html class="no-js" lang="en">
<head>

    <!--- basic page needs
    ================================================== -->
    <meta charset="utf-8">
    <title>Global Visioners International</title>
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- mobile specific metas
    ================================================== -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSS
    ================================================== -->
    <link rel="stylesheet" href="{{ asset('coming-soon/css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('coming-soon/css/vendor.css') }}">
    <link rel="stylesheet" href="{{ asset('coming-soon/css/main.css') }}">

    <!-- script
    ================================================== -->
    <script src="{{ asset('coming-soon/js/modernizr.js') }}"></script>
    <script src="{{ asset('coming-soon/js/pace.min.js') }}"></script>

    <!-- favicons
    ================================================== -->
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <link rel="icon" href="{{ asset('coming-soon/favicon.ico')}}" type="image/x-icon">

</head>

<body>

    <!-- home
    ================================================== -->
    <main class="s-home s-home--particles">

        <div id="particles-js" class="home-particles"></div>
        
        <div class="gradient-overlay"></div>

        <div class="home-content">

            <div class="home-logo">
                <a href="index-particles.html">
                    <img src="{{ asset('coming-soon/images/crop.png')}}" style="" alt="Homepage">
                </a>
            </div>

            <div class="row home-content__main">

                <div class="col-eight home-content__text pull-right">
                    <h3>Welcome to Global Visioners International</h3>
                    <h1 style="font-size: 30px;">
                        Something Amazing is Coming Soon
                    </h1>

                    <p>
                        At Global Visioners International, we're building something extraordinary for you.
                        <br/>
                        Our new website is under construction, and we can’t wait to unveil it. Stay tuned as we craft a platform that reflects our vision of excellence and innovation.
                        <br/>
                        Here’s a glimpse of what’s ahead:
                        <ul>
                            <li>Inspiring solutions for your business needs.</li>
                            <li>Cutting-edge insights and updates.</li>
                            <li>A seamless, user-friendly experience.</li>
                        </ul>
                        We appreciate your patience and look forward to sharing our journey with you.<br/>
                    </p>


                    
                </div>  <!-- end home-content__text -->

                <div class="col-four home-content__counter">
                    <h3>Launching In</h3>

                    <div class="home-content__clock">
                        <div class="top">
                            <div class="time days">
                                325
                                <span>Days</span>
                            </div>
                        </div>    
                        <div class="time hours">
                            09
                            <span>H</span>
                        </div>
                        <div class="time minutes">
                            54
                            <span>M</span>
                        </div>
                        <div class="time seconds">
                            30
                            <span>S</span>
                        </div>
                    </div>  <!-- end home-content__clock -->
                </div>  <!-- end home-content__counter -->

            </div>  <!-- end home-content__main -->

            <ul class="home-social">
                <li>
                <a href="#0"><i class="fab fa-facebook-f" aria-hidden="true"></i><span>Facebook</span></a>
                </li>
                <li>
                <a href="#0"><i class="fab fa-twitter" aria-hidden="true"></i><span>Twiiter</span></a>
                </li>
                <li>
                <a href="#0"><i class="fab fa-instagram" aria-hidden="true"></i><span>Instagram</span></a>
                </li>
                <li>
                <a href="#0"><i class="fab fa-behance" aria-hidden="true"></i><span>Behance</span></a>
                </li>
                <li>
                <a href="#0"><i class="fab fa-dribbble" aria-hidden="true"></i><span>Dribbble</span></a>
                </li>
            </ul> <!-- end home-social -->

            <div class="row home-copyright">
                <span>Copyright Global Visioners International 2024-25</span> 
              
            </div> <!-- end home-copyright -->


            <div class="home-content__line"></div>

        </div> <!-- end home-content -->

    </main> <!-- end s-home -->


    <!-- info
    ================================================== -->
    <a class="info-toggle" href="#0">
        <span class="info-menu-icon"></span>
    </a>

    <div class="s-info">

        <div class="row info-wrapper">

            <div class="col-seven tab-full info-main">
                <h1>We are Global Visioners International.</h1>
                 
                
            </div>

            <div class="col-four tab-full pull-right info-contact">

                

                <div class="info-block">
                    <h3>Find Us On</h3>
                    
                    <ul class="info-social">
                        <li>
                            <a href="#0"><i class="fab fa-facebook" aria-hidden="true"></i>
                            <span>Facebook</span></a>
                        </li>
                        <li>
                            <a href="#0"><i class="fab fa-twitter" aria-hidden="true"></i>
                            <span>Twiiter</span></a>
                        </li>
                        <li>
                            <a href="#0"><i class="fab fa-instagram" aria-hidden="true"></i>
                            <span>Instagram</span></a>
                        </li>
                        <li>
                            <a href="#0"><i class="fab fa-behance" aria-hidden="true"></i>
                            <span>Behance</span></a>
                        </li>
                        <li>
                            <a href="#0"><i class="fab fa-dribbble" aria-hidden="true"></i>
                            <span>Dribbble</span></a>
                        </li>
                    </ul>
                </div>
                
            </div>  <!-- end info contact -->

        </div>  <!-- end info wrapper -->

    </div> <!-- end s-info -->


    <!-- preloader
    ================================================== -->
    <div id="preloader">
        <div id="loader">
            <div class="line-scale-pulse-out">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
        </div>
    </div>

    <!-- Java Script
    ================================================== -->
    <script src="{{ asset('coming-soon/js/jquery-3.2.1.min.js')}}"></script>
    <script src="{{ asset('coming-soon/js/plugins.js')}}"></script>
    <script src="{{ asset('coming-soon/js/polygons.js')}}"></script>
    <script src="{{ asset('coming-soon/js/main.js')}}"></script>
	<script>
		// Pass the end date from Laravel to JavaScript
		const endDate = new Date("{{ $endDate->format('Y-m-d H:i:s') }}").getTime();
	
		// Update the countdown every second
		const timer = setInterval(function () {
			const now = new Date().getTime();
			const timeLeft = endDate - now;
	
			if (timeLeft <= 0) {
				clearInterval(timer);
				document.querySelector('.home-content__clock').innerHTML = 'The wait is over!';
				return;
			}
	
			// Calculate days, hours, minutes, and seconds
			const days = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
			const hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
			const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
			const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);
	
			// Update the counter in the HTML
			document.querySelector('.time.days').innerHTML = `${days} <span>Days</span>`;
			document.querySelector('.time.hours').innerHTML = `${hours} <span>H</span>`;
			document.querySelector('.time.minutes').innerHTML = `${minutes} <span>M</span>`;
			document.querySelector('.time.seconds').innerHTML = `${seconds} <span>S</span>`;
		}, 1000);
	</script>
	

</body>

</html>