<div class="row col-md-12 mt-2 variant_info">
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
    <div class="col-md-1 mt-2">
        @if(isset($count) && $count > 1)<a class="btn btn-danger remove_variant">X</a>@endif
    </div>
</div>