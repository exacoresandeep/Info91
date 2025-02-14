<!doctype html>
<html lang="en">
  <head>
  	<title>Info91 | Admin Panel</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<link href="https://fonts.googleapis.com/css?family=Lato:300,400,700&display=swap" rel="stylesheet">

	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	
	<link rel="stylesheet" href="{{ url ('dist/css/style.css')}}">

	</head>
	<body >
	<section class="ftco-section">
		<div class="container mb-3">
			<div class="row justify-content-center">
				<div class="col-md-6 text-center mb-5">
				<img src="{{asset('cs/assets/Logo.svg')}}" width="160">
					<!-- <h2 class="heading-section"></h2> -->
				</div>
			</div>
			<div class="row justify-content-center">
				<div class="col-md-6 col-lg-5">
					<div class="login-wrap p-4 p-md-5">
		      	<div class="icon d-flex align-items-center justify-content-center">
		      		<span class="fa fa-user-o"></span>
		      	</div>
		      	<h3 class="text-center mb-4">Info91 | Admin Panel</h3>
            @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                @foreach ($errors->all() as $error)
                    {{ $error }}<br>
                @endforeach
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true" style="color:red">&times;</span>
                </button>
            </div>
            @endif
						<form method="POST" action="{{ url('auth_login') }}" class="login-form">
            @csrf    
		      		<div class="form-group">
		      			<input type="text" class="form-control rounded-left @error('username') is-invalid @enderror" placeholder="Username"  name="username" required autocomplete="off">
		      		</div>
              @error('username')
                <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
                </span>
              @enderror
	            <div class="form-group d-flex">
	              <input type="password" class="form-control rounded-left @error('password') is-invalid @enderror" placeholder="Password" name="password" id="password" required autocomplete="off">
	              <span toggle="#password" class="fa fa-eye field-icon toggle-password" style="cursor: pointer;position:absolute;right:0px;top:15px;right:10px;"></span>
	            </div>
              @error('password')
                <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
                </span>
              @enderror
	            <div class="form-group d-md-flex">
	            	<div class="w-50">
	            		<label class="checkbox-wrap checkbox-primary">Remember Me
									  <input type="checkbox" checked>
									  <span class="checkmark"></span>
									</label>
								</div>
								<div class="w-50 text-md-right">
									<a href="#">Forgot Password</a>
								</div>
	            </div>
	            <div class="form-group">
	            	<button type="submit" class="btn btn-primary rounded submit">Login</button>
	            </div>
	          </form>
	        </div>
				</div>
			</div>
		</div>
	</section>

	<script src="{{ asset ('dist/js/jquery.min.js')}}"></script>
	<script src="{{ asset ('dist/js/popper.js')}}"></script>
	<script src="{{ asset ('dist/js/bootstrap.min.js')}}"></script>
	<script src="{{ asset ('dist/js/main.js')}}"></script>

	<script>
	  $(document).ready(function() {
	      $(".toggle-password").click(function() {
	          $(this).toggleClass("fa-eye fa-eye-slash");
	          var input = $($(this).attr("toggle"));
	          if (input.attr("type") === "password") {
	              input.attr("type", "text");
	          } else {
	              input.attr("type", "password");
	          }
	      });
	  });
	</script>

	</body>
</html>
