
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <meta name="generator" content="Hugo 0.84.0">
    <title>Global Visioners International</title>

    <link rel="canonical" href="https://getbootstrap.com/docs/5.0/examples/offcanvas-navbar/">

    

    <!-- Bootstrap core CSS -->
<link href="https://getbootstrap.com/docs/5.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <!-- Favicons -->
<link rel="apple-touch-icon" href="https://getbootstrap.com/docs/5.0/assets/img/favicons/apple-touch-icon.png" sizes="180x180">
<link rel="icon" href="https://getbootstrap.com/docs/5.0/assets/img/favicons/favicon-32x32.png" sizes="32x32" type="image/png">
<link rel="icon" href="https://getbootstrap.com/docs/5.0/assets/img/favicons/favicon-16x16.png" sizes="16x16" type="image/png">
<link rel="manifest" href="https://getbootstrap.com/docs/5.0/assets/img/favicons/manifest.json">
<link rel="mask-icon" href="https://getbootstrap.com/docs/5.0/assets/img/favicons/safari-pinned-tab.svg" color="#7952b3">
<link rel="icon" href="https://getbootstrap.com/docs/5.0/assets/img/favicons/favicon.ico">
<meta name="theme-color" content="#7952b3">


    <style>
      .bd-placeholder-img {
        font-size: 1.125rem;
        text-anchor: middle;
        -webkit-user-select: none;
        -moz-user-select: none;
        user-select: none;
      }

      @media (min-width: 768px) {
        .bd-placeholder-img-lg {
          font-size: 3.5rem;
        }
      }
    </style>

    
    <!-- Custom styles for this template -->
    <link href="offcanvas.css" rel="stylesheet">
  </head>
  <body class="bg-light">
    
 

<main class="container">
    <div class="row">
            <div class="col-lg-3"> 
            </div>

            <div class="col-lg-6">
                <div class="  p-3 my-5  bg-purple   shadow-sm text-white rounded-0"  style="background-color: #252F3D;"> 
                    <div class=" r">
                      <h1 class="h4 mb-0  lh-1 text-center" style="text-align: center!important;">Global Visioners International</h1> 
                    </div>
                  </div> 
                  <div class=" 3 p-3 bg-body rounded shadow-sm"> 
                    <div class="d-flex text-muted  "> 
                      <p class="  mb-0 small lh-sm  om"> 
                        Dear, {{ $user->name }} <br>  <br> 
                        <b class="">Welcome to Global Visioners International!</b> <br>
                        <br>
                        We are thrilled to have you join a community where innovation fuels opportunity. As a leading platform in e-commerce and affiliate marketing, our mission is to empower individuals and businesses to achieve their fullest potential on a global scale.
                        <br> <br>
                        At Global Visioners International, we don't just adapt to the future—we pioneer it. Together, let’s unlock a world of endless possibilities and shape a brighter tomorrow.
                        <br> <br>
                        Welcome aboard!
                        <br> <br>   
                        <strong>The GVI Support Team</strong>
                      </p>  
                    </div> 
                    <p class="p-3 pt-5 mb-0 small lh-sm  text-center">
                        <a href="{{ route('login') }}" target="_blank" class="btn btn-secondary   rounded-1" style="background-color: #252F3D;padding:4px 50px">Access Your Portal</a>  
                    </p>  
                  </div> 
            </div>

            <div class="col-lg-3"> 
            </div>

    </div>
  
  
</main>


    <script src="/docs/5.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

      <script src="offcanvas.js"></script>
  </body>
</html>