<div class="modal fade" id="rateSupplierModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Rate Supplier</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            {!! Form::open(['method' => 'POST', 'route' => ['purchase/rate', $purchase->id], 'id' => 'rateSupplierForm', 'autocomplete' => 'off']) !!}
                <div class="modal-body">
                    To help us get better, please rate this supplier.

                    <fieldset>
                        <legend class="text-info">Supplier</legend>
                        <div class="row mb-2">
                            <div class="col-md-12">
                                <span class="h3">{{ $purchase->supplier->name }}</span><br/>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <div class="row">
                            <div class="col-md-12">
                                <label for="comment">Rate:</label>
                                <input id="supplier-rating" name="score" class="kv-ltr-theme-fa-star rating-loading" data-size="md" required="required">
                            </div>
                        </div>
                    </fieldset>


                    <fieldset>
                        <div class="row">
                            <div class="col-md-12">
                                <label for="comment">Comment:</label>
                                {!! Form::textarea('comment', null, ['class' => 'form-control']) !!}
                            </div>
                        </div>
                    </fieldset>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    {!! Form::button('<i class="fa fa-smile"></i><i class="fa fa-frown"></i> Rate', ['type' => 'submit', 'class' => 'btn btn-danger']) !!}
                </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>