<div class="modal fade" id="sendPaymentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Send Payment</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            {!! Form::open(['method' => 'POST', 'route' => ['purchase/sendPayment', $purchase->id], 'autocomplete' => 'off']) !!}
                <div class="modal-body">
                    @if (empty($voucherNext['user']) && !empty($voucherNext['message']))
                    <div class="alert alert-danger">
                        {{ $voucherNext['message'] }}
                    </div>
                    @else
                    <div class="alert alert-info">
                        Click <strong>Send</strong> to submit purchase requisition
                    </div>
                    @endif
                    <fieldset>
                        <legend class="text-info">Destination</legend>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <?php
                                    $voucherNextUsers = $voucherNext['users']->count();
                                ?>
                                @if ($voucherNextUsers == 1)
                                <span class="name h3">{{ $voucherNext['users'][0]->name }}</span><br/>
                                {!! Form::hidden('next_user_id', $voucherNext['users'][0]->id) !!}
                                @elseif ($voucherNextUsers > 1)
                                {!! Form::select('next_user_id', $voucherNext['users']->pluck('name', 'id'), null, ['class' => 'form-control form-control-lg font-weight-bold','required' => 'required', 'placeholder' => ' - Select - ']) !!}
                                @else
                                <span class="h3">N/A</span>
                                @endif
                                <span class="destination"> {{ $voucherNext['flow']->role->description }} ({{ $voucherNext['flow']->requisitionStatus->name}})</span>
                            </div>
                            <div class="col-md-12">
                                <?php
                                    $nextUsers = $next['users']->count();
                                ?>
                                @if ($nextUsers == 1)
                                <span class="name h3">{{ $next['users'][0]->name }}</span><br/>
                                @endif
                                <span class="destination"> {{ $next['flow']->role->description }} ({{ $next['flow']->requisitionStatus->name}})</span>
                            </div>
                        </div>
                    </fieldset>


                    <fieldset>
                        <div class="row">
                            <div class="col-md-12">
                                <label for="comment">Comment to member:</label>
                                {!! Form::textarea('comment', null, ['class' => 'form-control']) !!}
                            </div>
                        </div>
                    </fieldset>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default {{ empty($voucherNext['user']) ? 'disabled' : '' }}" data-dismiss="modal">Cancel</button>
                    {!! Form::button('<i class="fa fa-paper-plane"></i> Send', ['type' => 'submit', 'class' => 'btn btn-danger']) !!}
                </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>