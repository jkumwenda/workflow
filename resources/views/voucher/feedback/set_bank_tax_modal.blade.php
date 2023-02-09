<div class="modal fade" id="set_bank_tax_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Set expenditure and withholding tax</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            {!! Form::model($voucher, ['method' => 'POST', 'route' => ['voucher/setBankTax', $voucher->id], 'autocomplete' => 'off']) !!}
                <div class="modal-body">
                    <h5><span class="text-primary">Procurement #:  </span>{{ idFormatter('procurement', $procurement->id) }}</h5>
                    <h4><span class="text-primary">Title: </span>{{ $procurement->title }}</h4>

                    <table class="table table-bordered table-hover table-info mt-4">
                        <tr>
                            <td>REQUISITION AMOUNT:</td>
                            <td class="text-right h4"><span id="total_amount" data-total_amount="{{ $voucher->total_amount }}">{{ moneyFormatter($voucher->total_amount) }}</span></td>
                            <td>EXEMPTED AMOUNT(VAT,LEVY):</td>
                            <td class="text-right h4">{!! Form::number('excepted_tax', null, ['step' => '0.01', 'class' => 'form-control', 'style' => 'width:100%;']) !!}</td>
                        </tr>
                    </table>


                    <div class="card mt-3">
                        <div class="card-body">
                            <table class="table table-bordered table-hover">
                                <tr>
                                    <th></th>
                                    <th>Code</th>
                                    <th style="width:170px"></th>
                                    <th style="width:90px">Amount</th>
                                </tr>
                                <tr>
                                    <td>EXPENDITURE</td>
                                    <td>{!! Form::text('expenditure_code', null, ['class' => 'form-control', 'style' => 'width:100%;', 'required' => 'required']) !!}</td>
                                    <td class="text-right"></td>
                                    <td class="text-right"><span class="expenditure">{{ moneyFormatter($voucher->excepted_tax + ($voucher->taxable - $voucher->taxedAmount)) }}</span></td>
                                </tr>
                                <tr>
                                    <td>WITHHOLDING TAX</td>
                                    <td>{!! Form::text('withholding_tax_code', null, ['class' => 'form-control', 'style' => 'width:100%;']) !!}</td>
                                    <td class="text-right">{!! Form::number('tax_applied', null, ['class' => 'form-control float-left', 'style' => 'width:100px;', 'min' => 0, 'max' => 100 ]) !!} <span class="float-left">&nbsp;%</span></td>
                                    <td class="text-right"><span class="taxed_amount">{{ moneyFormatter($voucher->taxedAmount) }}</span></td>
                                </tr>
                            </table>
                            <div><strong>TAXABLE:</strong> <span class="taxable">{{ moneyFormatter($voucher->taxable) }}</span> (Requisition Amount {{ moneyFormatter($voucher->total_amount) }} - Excepted tax <span class="excepted_tax">{{ moneyFormatter($voucher->excepted_tax) }} )</span></div>
                            <div><strong>TAXED AMOUNT:</strong> <span class="taxed_amount">{{ moneyFormatter($voucher->taxedAmount) }}</span> (Taxable <span class="taxable">{{ moneyFormatter($voucher->taxable) }}</span> * <span class="tax_applied">{{ $voucher->tax_applied ?? 0 }}</span><span>%</span> )</div>
                            <a href="javascript:void(0);" class="btn btn-outline-success" id="calc_amount"><i class="fa fa-calculator"></i> Calculate</a>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    {!! Form::button('<i class="fa fa-plus"></i> Set', ['type' => 'submit', 'class' => 'btn btn-danger']) !!}
                </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
