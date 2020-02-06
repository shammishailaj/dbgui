	
	@extends('crud.layout')
	
	@section('content')		
			
		<div class="row mb-3">				
				
			<div class="col-md-3">
					
				@include('crud.blocks.menu')			
		
			</div>
				
			<div class="col-md-9">
				
				@yield('info')
				
			</div>
		
		</div>
					
	@endsection						
