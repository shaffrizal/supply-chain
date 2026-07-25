@if($errors->any())
<div class="alert alert-danger sc-alert" role="alert"><i class="fas fa-exclamation-circle"></i><div><strong>Please check the submitted information.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>
@endif
@if(session('success'))<div class="alert alert-success sc-alert"><i class="fas fa-check-circle"></i><div>{{ session('success') }}</div></div>@endif
