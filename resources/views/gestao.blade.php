<x-layout>
  <script src="{{asset('src/js/telefone.js')}}"></script>
  <script src="{{asset('src/js/mesas/funcoes.js')}}"></script>
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Mesas</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="{{ route('gestao')}}">Home</a></li>
              <li class="breadcrumb-item active">Dashboard</li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-lg-12">
            <div class="card">
              <div class="card-header border-0">
                <!-- <div class="d-flex justify-content-between"> -->
                <div class="d-flex">
                  <div class="content_legenda">
                    <x-legenda legenda="Liberada:" bgLegenda="info" />
                    <x-legenda legenda="Ocupada:" bgLegenda="danger" />
                    <x-legenda legenda="Reservada:" bgLegenda="secondary" />
                    <x-legenda legenda="Inativa:" bgLegenda="warning" />
                  </div>
                </div>
                <div class="d-flex justify-content-end">
                  <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#reserva-modal">
                    Reservar
                  </button>
                </div>
              </div>
              <div class="card-body">
                <!-- =========================================================== -->
                <!-- <h5 class="mt-4 mb-2">Info Box With <code>bg-gradient-*</code></h5> -->
                @if ($mesas)
                <div class="row">

                  @foreach ($mesas as $mesa)
                  <x-mesa :id="$mesa->id" :situacao="$mesa->situacao" />
                  @endforeach

                </div>
                <div class="paginacaoLaravel">
                  {{ $mesas->links() }}
                </div>
                @endif
                <!-- /.row -->
              </div>
            </div>
            <!-- /.card -->
          </div>
        </div>
        <!-- /.row -->
      </div>
      <!-- /.container-fluid -->

      <x-small_modal id="reserva-modal" titleModal="Reserva" textBtn="Continuar">
        <div class="modal-body">
          <form>
            <div class="card-body">
              <div class="form-group">
                <label for="telefone">Telefone/Celular</label>
                <input type="text" class="form-control" id="telefone" placeholder="(xx) xxxxx-xxxx" maxlength="15" oninput="mascaraTelefone(this)">
              </div>
            </div>
          </form>
        </div>
      </x-small_modal>
    </div>
    <!-- /.content -->
  </div>

</x-layout>