<div class="modal fade" id="opcoes_mesa">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"></h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="overlay-wrapper" style="margin-top: 15px;">
                <div class="overlay d-none">
                    <i class="fas fa-2x fa-sync-alt fa-spin"></i>
                </div>
                <div class="col-12 content-option" style="cursor:pointer;">
                    <div id="div-ocupar-mesa" class="info-box bg-gradient-info btn-opcoes-mesa">
                        <i class="fas fa-user-plus"></i>
                        <span class="info-box-text">Abrir</span>
                    </div>
                    @if (auth()->user()->user_type != 2)
                    <div id="div-vincular-mesa" class="info-box bg-gradient-info btn-opcoes-mesa">
                        <i class="fa fa-link" aria-hidden="true"></i>
                        <span class="info-box-text">Juntar</span>
                        <span class="info-box-text">mesas</span>
                    </div>
                    @endif

                    <div id="div-fechar-mesa" class="info-box bg-gradient-info btn-opcoes-mesa">
                        <i class="fa fa-hashtag" aria-hidden="true"></i>
                        <span class="info-box-text">Fechar</span>
                    </div>
                </div>

                <div class="col-12 content-option" style="cursor:pointer;">
                    @if (auth()->user()->user_type != 2)
                    <div id="div-trocar-mesa" class="info-box bg-gradient-info btn-opcoes-mesa" onclick="acaoMesa('2', '3')">
                        <i class="fa fa-arrow-left" aria-hidden="true"></i>
                        <span class="info-box-text">Trocar de</span>
                        <span class="info-box-text">mesa</span>
                    </div>
                    @endif

                    @if (auth()->user()->user_type != 2 && auth()->user()->user_type != 3)
                    <div id="div-pagar-mesa" class="info-box bg-gradient-info btn-opcoes-mesa" onclick="acaoMesa('2', '3')">
                        <i class="fa fa-credit-card" aria-hidden="true"></i>
                        <span class="info-box-text">Pagar</span>
                    </div>
                    @endif

                    @if (auth()->user()->user_type != 2 && auth()->user()->user_type != 3)
                    <div id="div-inativar-mesa" class="info-box bg-gradient-info btn-opcoes-mesa">
                        <i class="fa fa-ban" aria-hidden="true"></i>
                        <span id="text-ativacao" class="info-box-text">Inativar</span>
                        <span class="info-box-text">mesa</span>
                    </div>
                    @endif
                </div>

                <div class="col-12 content-option" style="cursor:pointer;">
                    <select class="form-control" id="vincular-mesas-disponiveis" name="vincular-mesas-disponiveis" style="width: 95%; display:none;">
                        <option selected value="0">Selecione</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
                <!-- <button type="button" class="btn btn-primary"></button> -->
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>