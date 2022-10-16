<!DOCTYPE html>
<html lang="en">
    @include('layouts.head')
    <body>
      <main>
        <div class="container">
          <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
            <div class="container">
              <div class="row justify-content-center">
                @if(\Session::has('success'))
                <div class="alert alert-primary bg-primary text-light border-0 alert-dismissible fade show" role="alert"> {{\Session::get('success')}} </div>
                @endif
                @if(\Session::has('error'))
                <div class="alert alert-danger bg-danger text-light border-0 alert-dismissible fade show" role="alert"> {{\Session::get('error')}} </div>
                @endif
                <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">
    
                  <div class="d-flex justify-content-center py-4">
                    <a href="#" class="logo d-flex align-items-center w-auto">
                      {{-- <img src="assets/img/logo.png" alt=""> --}}
                      <span class="d-none d-lg-block">DevOps Login</span>
                    </a>
                  </div><!-- End Logo -->
                  <div class="card mb-3">
                    <div class="card-body">
                      <div class="pt-4 pb-2">
                        <h5 class="card-title text-center pb-0 fs-4">Login</h5>
                      </div>
    
                      <form action="{{route('devops.login.submit')}}" method="POST" class="row g-3 needs-validation" novalidate>
                        @csrf
                        <div class="col-12">
                          <label for="email" class="form-label">E-mail</label>
                          <div class="input-group has-validation">
                            <input type="email" name="email" class="form-control" id="email" required>
                            <div class="invalid-feedback">Please enter your username.</div>
                          </div>
                        </div>
                        <div class="col-12">
                          <label for="password" class="form-label">Password</label>
                          <input type="password" name="password" class="form-control" id="password" required>
                          <div class="invalid-feedback">Please enter your password!</div>
                        </div>
                        <div class="col-12">
                          <button class="btn btn-primary w-100" type="submit">Login</button>
                        </div>
                      </form>
    
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </section>
        </div>
      </main><!-- End #main -->
    
      <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
    
      @include('layouts.scripts')
    </body>
    
    </html>