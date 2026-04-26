@extends('layouts.install')
@section('title', 'Installation/Update')

@section('content')
<div class="container">
    <div class="row">

        <div class="col-md-8 col-md-offset-2">
            <br/><br/>

            <div class="box box-primary active">
                <!-- /.box-header -->
                <div class="box-body">

              @if(session('error'))
                <div class="alert alert-danger">
                    {!! session('error') !!}
                </div>
              @endif

              @if ($errors->any())
                <div class="alert alert-danger">
                  <ul>
                  @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                  </ul>
                </div>
              @endif

              <form class="form" id="details_form" method="post" 
                      action="{{$action_url}}">
                    {{ csrf_field() }}

                    <h2>Installing <code>{{$module_display_name ?? ''}} Module</code></h2>
                    <hr/>

                    <input type="hidden" name="license_code" value="AUTO-INSTALL">
                    <input type="hidden" name="login_username" value="{{ optional(auth()->user())->username ?? 'auto' }}">
                    <input type="hidden" name="email" value="{{ optional(auth()->user())->email }}">
                    <input type="hidden" name="ENVATO_EMAIL" value="{{ optional(auth()->user())->email }}">

                    <div class="col-md-12">
                        <p class="install_msg">Installing module, please wait...</p>
                        <button type="submit" id="install_button" class="btn btn-primary pull-right">Install Now</button>
                    </div>
              </form>
            </div>
          <!-- /.box-body -->
          </div>

            
        </div>

    </div>
</div>
@endsection

@section('javascript')
  <script type="text/javascript">
    $(document).ready(function(){
      var $form = $('form#details_form');

      $form.submit(function(){
        $('button#install_button').attr('disabled', true).text('Installing...');
        $('.install_msg').removeClass('hide');
        $('.back_button').hide();
      });

      if ($form.length) {
        $form.triggerHandler('submit');
        $form[0].submit();
      }
    })
  </script>
@endsection
