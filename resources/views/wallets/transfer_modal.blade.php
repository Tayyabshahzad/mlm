<div class="modal fade" id="WithdrawModel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('wallet.transfer.to.online') }}" method="POST">
                @csrf
                <input type="hidden" name="wallet_type" value="{{ $wallet }}" required>
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Transfer to Online Wallet G</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i aria-hidden="true" class="ki ki-close"></i>
                    </button>
                </div>
                <div class="modal-body">
                <p class="text-center text-danger">
                    5% Will charge on every transaction 
                </p>
                    <div class="form-group row"> 
                        <div class="col-lg-12 col-xl-12">
                            <label for="" class="mr-2 font-weight-bold">
                                Transfer Amount
                            </label>
                            <input type="number" class="mb-2 form-control form-control-sm form-control-solid" 
                             name="amount" min="5.27" step="0.01"
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