@extend('errors/layouts/general_error')

@section('title', 'Error ' . $code)

@section('content')

<h1>Error {{ $code }} - {{ $msg }}.</h1>

@if (isset($method)):
<h2>The <span>{{ $given_method }}</span> method is not supported for this route. Supported methods: <span>{{ $method }}</span>.</h2>
@endif

@endSection('content')