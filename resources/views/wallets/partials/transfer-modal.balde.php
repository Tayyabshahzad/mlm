<div class="modal fade" id="WithdrawModel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('wallet.transfer.to.online') }}" method="POST">
                @csrf
                <input type="hidden" name="wallet_type" value="direct_indirect" required>
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Transfer to Online Wallet</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i aria-hidden="true" class="ki ki-close"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group row">
                        <div class="col-lg-12 col-xl-12">
                            <label for="" class="font-weight-bold mr-2">
                                Transfer Amount
                            </label>
                            <input type="number" class="form-control form-control-sm form-control-solid mb-2" 
                             name="amount" min="7" step="0.01"
                             required
                             max="{{ $wallets->sum('balance') }}"
                             placeholder="Enter Amount">  
                             <strong class="text-danger">Available Balance : {{ $wallets->sum('balance') }}</strong>
                        </div>  
                    </div>  
                </div>
                <div class="modal-footer">
                    <button type="button" class="rounded-0 btn btn-light-primary btn-sm" data-dismiss="modal">Close</button>
                    <button type="submit" class="rounded-0 btn btn-primary btn-sm">Transfer </button>
                </div>
            </form>
        </div>
    </div>
</div>