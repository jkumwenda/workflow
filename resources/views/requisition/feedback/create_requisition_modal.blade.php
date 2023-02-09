<div class="modal fade" role="dialog" id="createRequisitionModal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Create Requisition</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
          Which requisition do you want to create?

          <div class="row mt-3">
            <div class="col-md-10 ml-auto m-auto">
                <div class="d-flex flex-column">
                    <a class="btn btn-block btn-outline-warning" href="{{ route('procurement/create') }}"><i class="fa rplus-icon-procurement"></i> Procurement</a>
                    <a class="btn btn-block btn-outline-warning" href="{{ route('travel/create') }}"><i class="fa rplus-icon-travel"></i> Travel</a>
                    {{--
                    <a class="btn btn-block btn-outline-primary disabled" href="#"><i class="fa rplus-icon-maintenance"></i> Maintenance</a>
                    <a class="btn btn-block btn-outline-primary disabled" href="#"><i class="fa rplus-icon-booking"></i> Room Booking</a>
                    <a class="btn btn-block btn-outline-primary disabled" href="#"><i class="fa rplus-icon-claim"></i> Claim for Expenses</a>
                    <a class="btn btn-block btn-outline-primary disabled" href="#"><i class="fa rplus-icon-loan"></i> Loan</a>
                    --}}
                </div>
            </div>
          </div>
      </div>
    </div>
  </div>
</div>
