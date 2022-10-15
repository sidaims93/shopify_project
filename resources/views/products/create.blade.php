@extends('layouts.app')

@section('content')
<div class="pagetitle">
    <div class="row">
        <div class="col-8">
            <h1>Products</h1>
            <nav>
                <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
                <li class="breadcrumb-item">Create Product</li>
                </ol>
            </nav>
        </div>
        <div class="col-4">
        @can('write-products')
        <table class="table table-borderless">
            <tbody>
            <tr>
                <td><a href="{{route('locations.sync')}}" style="float: right;" class="btn btn-success">Sync Locations</a></td>
                <td><a href="{{route('shopify.products')}}" style="float: right" class="btn btn-primary">Back</a></td>
            </tr>
            </tbody>
        </table>
        @endcan
        </div>
    </div>
</div>
<section class="section">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Create a product</h5>
                    <!-- Floating Labels Form -->
                    <form class="row g-3" method="POST" action="{{route('shopify.product.publish')}}">
                        @csrf
                        <div class="col-md-12">
                            <div class="form-floating">
                            <input type="text" class="form-control" id="floatingName" name="title" placeholder="Product Name/Title" required>
                            <label for="floatingName">Product Name/Title</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating">
                            <textarea class="form-control" placeholder="Product Description" id="floatingTextarea" style="height: 100px;" name="desc" required></textarea>
                            <label for="floatingTextarea">Description</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="col-md-12">
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="product_type" id="floatingProductType" placeholder="Product Type" required>
                                    <label for="floatingProductType">Product Type</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="col-md-12">
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="vendor" id="floatingVendor" placeholder="Vendor" required>
                                    <label for="floatingVendor">Vendor</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="col-md-12">
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="tags" id="floatingCostPerItem" placeholder="Tags" required>
                                    <label for="floatingCostPerItem">Tags</label>
                                </div>
                            </div>
                        </div>
                        <div class="card-body row g-3" id="variantsRow">
                            <h5 class="card-title">Variant Details</h5>
                            @include('products.partials.add_variant', ['count' => 1])
                        </div>
                        <div class="card-body" style="float:left">
                            <a href="#" class="btn btn-success btn-md add_variant">Add a Variant</a>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary" style="width:40%">Create</button>
                        </div>
                    </form><!-- End floating Labels Form -->
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            $('.add_variant').click(function (e) {
                e.preventDefault();
                var count = $('#variantsRow .variant_info').length;
                var url = "{{route('product.add.variant')}}";
                $.ajax({
                    type: 'GET',
                    url: url+'?count='+(parseInt(count)+1),
                    async: false,
                    success: function (response) {
                        $('#variantsRow').append(response.html);
                    }
                })
            });
        });

        $(document).on("click", ".remove_variant", function(e) {
            e.preventDefault();
            $(this).parent().parent().parent().remove();
        });
    </script>
@endsection