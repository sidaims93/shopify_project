@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="row justify-content-md-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header"><h4>Two Factor Authentication</h4></div>
                    <div class="card-body mt-2">
                        <div class="alert alert-dark bg-light text-dark">
                            Two-factor authentication (2FA) strengthens access security by requiring two methods (also referred to as factors) to verify your identity. 
                            It protects against phishing, 
                            social engineering and password brute force attacks and secures your logins from attackers exploiting weak or stolen credentials.
                        </div>

                        @if (session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif
                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if($data['user']->loginSecurity == null)
                            <form class="form-horizontal" method="POST" action="{{ route('generate2faSecret') }}">
                                {{ csrf_field() }}
                                <div class="form-group mt-4 text-center pt-4">
                                    <button type="submit" class="btn btn-primary">
                                        Generate Secret Key to Enable 2FA
                                    </button>
                                </div>
                            </form>
                        @elseif(!$data['user']->loginSecurity->google2fa_enable)
                            <br><h5><b>Enable 2Factor Authentication for your account</b></h5><br>
                            <p><b> Step 1.</b> Scan this QR code with your Google Authenticator App.
                            <!-- <br> Alternatively, you can use the code: <b>{{ $data['secret'] }}</b></p> -->
                            <div class="text-center">
                                <img src="{{$data['google2fa_url'] }}" alt="">
                            </div>
                            <br/><br/>
                            <b> Step 2.</b> Enter the PIN from Google Authenticator app:<br/><br/>
                            <form class="form-horizontal" method="POST" action="{{ route('enable2fa') }}">
                                {{ csrf_field() }}
                                <div class="form-group{{ $errors->has('verify-code') ? ' has-error' : '' }}">
                                    <label for="secret" class="control-label">Authenticator Code</label>
                                    <input id="secret" type="password" class="form-control col-md-4" name="secret" required>
                                    @if ($errors->has('verify-code'))
                                        <span class="help-block">
                                        <strong>{{ $errors->first('verify-code') }}</strong>
                                        </span>
                                    @endif
                                </div>
                                <button type="submit" class="btn btn-primary mt-3">
                                    Enable 2FA
                                </button>
                            </form>
                        @elseif($data['user']->loginSecurity->google2fa_enable)
                            <div class="alert alert-success">
                                2FA is currently <strong>enabled</strong> for your account.
                            </div>
                            <div class="alert alert-danger">If you are looking to disable 2-Factor Authentication. Please confirm your password and Click "Disable 2FA" Button.</div>
                            <form class="form-horizontal" method="POST" action="{{ route('disable2fa') }}">
                                {{ csrf_field() }}
                                <div class="form-group{{ $errors->has('current-password') ? ' has-error' : '' }}">
                                    <label for="change-password" class="control-label">Current Password</label>
                                        <input id="current-password" type="password" class="form-control col-md-4" name="current-password" required>
                                        @if ($errors->has('current-password'))
                                            <span class="help-block">
                                        <strong>{{ $errors->first('current-password') }}</strong>
                                        </span>
                                        @endif
                                </div>
                                <button type="submit" class="btn btn-primary mt-3">Disable 2FA</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection