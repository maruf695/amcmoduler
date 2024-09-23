@extends('layouts.main.app') @section('head') @include('layouts.main.headersection',[ 'title'=> __('Upload module'), 'buttons'=>[ [ 'name'=>__('Back'), 'url'=>route('admin.modules.index'), ] ] ]) @endsection @section('content')<div class="row justify-content-center"><div class="col-lg-8 card-wrapper">@if(Session::has('error'))<div class="alert bg-gradient-danger text-white alert-dismissible fade show success-alert" role="alert"><span class="alert-text">{{ Session::get('error') }}</span><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button></div>@endif @if(Session::has('success'))<div class="alert bg-gradient-success text-white alert-dismissible fade show success-alert" role="alert"><span class="alert-text">{{ Session::get('success') }}</span><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button></div>@endif @if(!Session::has('update-data'))<div class="card"><div class="card-header"><div class="row w-100"><div class="col-6"><h3>{{ __('Upload New Module') }}</h3></div></div></div><div class="card-body"><form action="{{ route('admin.modules.store') }}" method="post" class="ajaxform_instant_reload" enctype="multipart/form-data"><div class="form-group"><label for="">{{ __('Upload Module') }}</label><input type="file" class="form-control" required accept=".zip" name="module"></div><div class="form-group"><label for="">{{ __('Purchase Key') }}</label><input type="text" class="form-control" required name="purchase_key"></div><div class="from-group mt-3"><button class="btn btn-neutral submit-button" type="submit">{{ __('Upload') }}</button></div></form></div></div>@endif</div></div>@endsection