<div class="modal fade" id="{{ $id }}">
    <div class="modal-dialog {{ $id }}">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ $titleModal }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            {{ $slot }}

            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
                @if ($titleModal === 'Reserva')
                    <button type="button" class="btn btn-primary" onclick="buscaCliente()">{{ $textBtn }}</button>
                @else
                    <button type="button" class="btn btn-primary">{{ $textBtn }}</button>
                @endif
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>