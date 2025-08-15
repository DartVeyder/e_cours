@extends('platform::auth')
@section('title',__('Sign in to your account'))

@section('content')
    <div class="d-flex flex-column align-items-center">
        <h1 class="h4 text-body-emphasis mb-4">{{__('Sign in to your account')}}</h1>

        <a href="/auth/google/redirect"><img src=" {{asset('/images/login_google.svg')}}" alt=""></a>
        @foreach ($errors->all() as $error)

            <div  class="alert alert-danger rounded mt-3" role="alert">{{ $error }}</div>

        @endforeach
    </div>

@endsection
