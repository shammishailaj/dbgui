<!doctype html>

<html lang="en"> 

	<head>
				
		<meta name="csrf-token" content="{{csrf_token()}}">
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">		
			
		@isset($title)<title>{{$title}}</title>@endisset
		@isset($description)<meta name="description" content="{{$description}}" />@endisset
		@isset($keywords)<meta name="keywords" content="{{$keywords}}" />	@endisset
		
		<link rel="shortcut icon" href="{{ env('APP_URL') }}/dbgui/dbgui.ico" />
		
		<link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro&display=swap" rel="stylesheet">
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
		<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
		<link rel="stylesheet" href="{{ env('APP_URL')}}/dbgui/crud.css">
		
		@yield('css')
    
	</head>  
	
	<body>
				
		<div class="container">
				
			@include('crud.blocks.navbar')

			@yield('content')

			@include('crud.blocks.bottom')
			
		</div>		 

		<script src="https://kit.fontawesome.com/83f2bdf771.js" crossorigin="anonymous"></script>

		<script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
		<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
		<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
		<script src="{{ env('APP_URL')}}/dbgui/crud.js"></script>

		@yield('javascript')
	
	</body>
	
</html>
