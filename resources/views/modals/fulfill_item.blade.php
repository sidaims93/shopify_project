<div class="modal fade" id="fulfill_items_modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Fulfill Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body fulfillment_form">
                <form class="row g-3">
                    <input type="hidden" id="lineItemId" />
                    <div class="col-md-6">
                        <label for="number" class="form-label">Number</label>
                        <input type="text" class="form-control" id="number">
                    </div>
                    <div class="col-md-6">
                        <label for="shipping_company" class="form-label">Shipping Company</label>
                        <input type="text" class="form-control" id="shipping_company">
                    </div>
                    <div class="col-md-6">
                        <label for="no_of_packages" class="form-label">No of Packages</label>
                        <select class="form-control" id="no_of_packages"></select>
                    </div>
                    <div class="col-md-6">
                        <label for="message" class="form-label">Message</label>
                        <input type="text" class="form-control" placeholder="Enter your custom message" id="message">
                    </div>
                    <div class="col-12">
                        <label for="tracking_url" class="form-label">Tracking URL</label>
                        <input type="text" class="form-control" id="tracking_url" placeholder="Enter tracking URL">
                    </div>
                    
                    <div class="col-12" style="align-items: center;">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="notify_customer">
                            <label class="form-check-label" for="notify_customer">
                                Notify the customer
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="justify-content: right">
                <button type="button" class="btn btn-primary fulfill_submit">Fulfill</button>
                <div class="spinner-border fulfill_loading text-primary" role="status" style="display: none;">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    </div>
</div>