<div class="modal fade" id="modal-transferencia-itens">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Transferir itens</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-transferencia" method="POST" action="{{route('set-transferencia')}}">
                @csrf

                <div class="modal-body">
                    <input type="hidden" id="mesa-atual-tranferencia" name="mesa-atual-tranferencia">

                    <div class="card-body" style="height:400px; overflow-y:scroll;">
                        <div class="custom-control custom-radio">
                            <input class="custom-control-input" type="radio" id="transfere-todos" name="opcao-transferencia" value="1" onchange="handleChange(this)">
                            <label for="transfere-todos" class="custom-control-label">Transferir todos os itens</label>
                        </div>
                        <div class="custom-control custom-radio">
                            <input class="custom-control-input" type="radio" id="transfere-alguns" name="opcao-transferencia" value="2" onchange="handleChange(this)">
                            <label for="transfere-alguns" class="custom-control-label">Escolher itens para trasferência</label>
                        </div>

                        <div class="content-products-order-list" style="display: none; flex-direction: column;">

                        </div>

                        <div class="form-group" style="margin-top:10px">
                            <label>Transferir para a mesa:</label>
                            <select class="form-control" id="mesas-disponiveis-transferencia" name="mesas_disponiveis_transferencia">
                                <option selected value="0">Selecione</option>
                            </select>
                        </div>
                    </div>

                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
                    <button type="submit" onclick="transferir(event)" class="btn btn-primary">Transferir</button>
                </div>  
            </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>