<div class="pb-4" style="border: 1px dotted black">
<div class="row col-md-12 mt-2 variant_info">
    <label for="" class="mt-2 mb-2"><b>Variant #{{$count}}</b>
    @if(isset($count) && $count > 1)<a class="btn btn-danger remove_variant" style="margin-left:50px">X</a>@endif</label>    
    <div class="col-md-4">
        <div class="form-floating">
            <input type="text" class="form-control" id="floatingVariantTitle" name="variant_title[]" placeholder="Title" required>
            <label for="floatingVariantTitle">Title</label>
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-floating">
            <input type="text" class="form-control" id="floatingSKU" name="sku[]" placeholder="SKU" required>
            <label for="floatingSKU">SKU</label>
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-floating">
            <input type="number" class="form-control" id="floatingVariantPrice" name="variant_price[]" placeholder="Price" required>
            <label for="floatingVariantPrice">Price</label>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-floating">
            <input type="number" class="form-control" id="floatingCompareAtVariantPrice" name="variant_caprice[]" placeholder="Compare At Price" required>
            <label for="floatingCompareAtVariantPrice">Compare At Price</label>
        </div>
    </div>
</div>
@isset($locations)
    @if($locations !== null && $locations->count() > 0)
    <div class="row col-md-12 mt-2">
        <label for="" class="mt-2 mb-2"><b>Inventory Info</b></label>
        @foreach($locations as $location)
        <div class="col-md-3">
            <div class="form-floating">
                <input type="number" class="form-control" id="floatingVariantPrice" name="{{$location->id}}_inventory_{{$count}}" placeholder="Price" required>
                <label for="floatingVariantPrice">{{$location->name}}</label>
            </div>
        </div>
        @endforeach
    </div>
    @endif
@endisset
</div>
