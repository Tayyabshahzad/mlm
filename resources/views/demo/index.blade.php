<!DOCTYPE html>
<html lang="en">
<head>
	<title>Global Visioners International </title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
<!--===============================================================================================-->	
	<link rel="icon" type="image/png" href="{{ asset('under-process/images/icons/favicon.ico')}}"/>
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="{{ asset('under-process/vendor/bootstrap/css/bootstrap.min.css')}}">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="{{ asset('under-process/fonts/font-awesome-4.7.0/css/font-awesome.min.css')}}">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="{{ asset('under-process/vendor/animate/animate.css')}}">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="{{ asset('under-process/vendor/select2/select2.min.css')}}">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="{{ asset('under-process/css/util.css')}}">
	<link rel="stylesheet" type="text/css" href="{{ asset('under-process/css/main.css')}}">
<!--===============================================================================================-->
</head>
<body>
	
	
	<div class="bg-img1 size1 overlay1" style="background-image: url('{{ asset("under-process/images/bg01.jpg") }}');">
		<div class="size1 p-l-15 p-r-15 p-t-10 p-b-50">
			<div class="flex-w flex-sb-m p-l-75 p-r-60 p-b-165 respon1">
				<div class="wrappic1 m-r-30 m-t-10 m-b-10">
					
				</div>

				<div class="flex-w m-t-10 m-b-10">
					<a href="#" class="size4 flex-c-m how-social trans-04 m-r-5 m-b-3 m-t-3">
						<i class="fa fa-facebook"></i>
					</a>

					<a href="#" class="size4 flex-c-m how-social trans-04 m-r-5 m-b-3 m-t-3">
						<i class="fa fa-twitter"></i>
					</a>

					<a href="#" class="size4 flex-c-m how-social trans-04 m-r-5 m-b-3 m-t-3">
						<i class="fa fa-youtube-play"></i>
					</a>
				</div>
			</div>

			<div class="wsize1 m-lr-auto">
                 
				<p class="txt-center l1-txt1 p-b-60">
                   
					Global Visioners International is <span class="l1-txt2">Coming Soon</span>,  <br>follow us for update now!
				</p>

				<form class="w-full flex-w flex-c-m validate-form">

					<div class="wrap-input100 validate-input m-b-20" data-validate = "Valid email is required: ex@abc.xyz">
						<input class="input100 placeholder0 m1-txt1" type="text" name="email" placeholder="Email Address">
						<span class="focus-input100"></span>
					</div>
					
					
					<button class="flex-c-m size3 m1-txt2 how-btn1 trans-04 m-b-20">
						Subscribe
					</button>
				</form>
				
				<p class="txt-center s1-txt1 p-t-5">
					And don’t worry, we hate spam too! You can unsubcribe at anytime.
				</p>
			</div>
				

			<div class="flex-w flex-c-m cd100 wsize1 m-lr-auto p-t-116">
				<div class="flex-col-c-m size2 bor1 m-l-10 m-r-10 m-b-15">
					<span class="l1-txt3 p-b-9 days">30</span>
					<span class="s1-txt2">Days</span>
				</div>

				<div class="flex-col-c-m size2 bor1 m-l-10 m-r-10 m-b-15">
					<span class="l1-txt3 p-b-9 hours">17</span>
					<span class="s1-txt2">Hours</span>
				</div>

				<div class="flex-col-c-m size2 bor1 m-l-10 m-r-10 m-b-15">
					<span class="l1-txt3 p-b-9 minutes">50</span>
					<span class="s1-txt2">Minutes</span>
				</div>

				<div class="flex-col-c-m size2 bor1 m-l-10 m-r-10 m-b-15">
					<span class="l1-txt3 p-b-9 seconds">39</span>
					<span class="s1-txt2">Seconds</span>
				</div>
			</div>
		</div>
	</div>



	

<!--===============================================================================================-->	
	<script src="{{ asset('under-process/vendor/jquery/jquery-3.2.1.min.js')}}"></script>
<!--===============================================================================================-->
	<script src="{{ asset('under-process/vendor/bootstrap/js/popper.js')}}"></script>
	<script src="{{ asset('under-process/vendor/bootstrap/js/bootstrap.min.js')}}"></script>
<!--===============================================================================================-->
	<script src="{{ asset('under-process/vendor/select2/select2.min.js')}}"></script>
<!--===============================================================================================-->
	<script src="{{ asset('under-process/vendor/countdowntime/moment.min.js')}}"></script>
	<script src="{{ asset('under-process/vendor/countdowntime/moment-timezone.min.js')}}"></script>
	<script src="{{ asset('under-process/vendor/countdowntime/moment-timezone-with-data.min.js')}}"></script>
	<script src="{{ asset('under-process/vendor/countdowntime/countdowntime.js')}}"></script>
	<script>
        const endDate = @json($endDate);
        const endtime = moment(endDate); 
        $('.cd100').countdown100({
            endtimeYear: endtime.year(),
            endtimeMonth: endtime.month() + 1, // Month is 0-indexed
            endtimeDate: endtime.date(),
            endtimeHours: endtime.hour(),
            endtimeMinutes: endtime.minute(),
            endtimeSeconds: endtime.second(),
            timeZone: ""
        });
	</script>
<!--===============================================================================================-->
	<script src="{{ asset('under-process/vendor/tilt/tilt.jquery.min.js')}}"></script>
	<script >
		$('.js-tilt').tilt({
			scale: 1.1
		})
	</script>
<!--===============================================================================================-->
	<script src="{{ asset('under-process/js/main.js')}}"></script>

</body>
</html>