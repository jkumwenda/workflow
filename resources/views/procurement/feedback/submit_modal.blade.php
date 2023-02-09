@if (!empty($next['users']) && !empty($next['flow']))
<div class="modal fade" id="confirm_submit" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Submit requisition</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            {!! Form::open(['method' => 'POST', 'route' => ['procurement/submit', $procurement->id], 'autocomplete' => 'off']) !!}
                <div class="modal-body">
                    <div class="alert alert-info">
                        Click <strong>Send</strong> to submit requisition
                    </div>
                    <fieldset>
                        <legend class="text-info">Destination</legend>
                        <div class="row">
                            <div class="col-md-12">
                                <?php
                                    $nextUsers = $next['users']->count();
                                ?>
                                @if ($nextUsers == 1)
                                <span class="name h3">{{ $next['users'][0]->name }}</span><br/>
                                {!! Form::hidden('next_user_id', $next['users'][0]->id) !!}
                                @else
                                {!! Form::select('next_user_id', $next['users']->pluck('name', 'id'), null, ['class' => 'form-control form-control-lg font-weight-bold','required' => 'required', 'placeholder' => ' - Select - ']) !!}
                                @endif
                                <span class="destination"> {{ $next['flow']->role->description }} ({{ $next['flow']->requisitionStatus->name}})</span><br/><br/>
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
                    {!! Form::button('<i class="fa fa-paper-plane"></i> Send', ['type' => 'submit', 'class' => 'btn btn-danger']) !!}
                </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
@endif