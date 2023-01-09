<!DOCTYPE html> 
<html lang="en">
@include('layouts.head')
<body>  
    @include('layouts.header')
    <!-- ======= Sidebar ======= -->
    <main id="main" class="main">
        <div class="row">
            <div class="col-12">
                <table class="table">
                    <thead>
                        <th>ID</th>
                        <th>Email</th>
                        <th>Name</th>
                        <th>Store name</th>
                        <th>Store domain</th>
                    </thead>
                    <tbody>
                        @foreach($users as $user) 
                            <tr>
                                <td>{{$user->id}}</td>
                                <td>{{$user->email}}</td>
                                <td>{{$user->name}}</td>
                                <td>{{$user->getShopifyStore->name}}</td>
                                <td>{{$user->getShopifyStore->myshopify_domain}}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    @include('layouts.footer')
</body>
</html>
