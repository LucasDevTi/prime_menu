<x-layout>

  <script src="{{asset('src/js/telefone.js')}}"></script>
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
              @if (session('success'))
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              @endif

              <div class="card-header border-0">
                <!-- <div class="d-flex justify-content-between"> -->
                <div class="d-flex">

                  <div class="content_legenda">
                    <x-legenda legenda="Liberada:" bgLegenda="success" />
                    <x-legenda legenda="Aberta:" bgLegenda="info" />
                    <x-legenda legenda="Fechada:" bgLegenda="danger" />
                    <x-legenda legenda="Reservada:" bgLegenda="secondary" />
                    <x-legenda legenda="Inativa:" bgLegenda="warning" />
                  </div>
                </div>
                <div class="d-flex justify-content-end" style="padding-right: 30px;">
                  @if (auth()->user()->user_type != 2)
                  <button class="btn btn-warning mr-2" id="juntarMesasBtn" onclick="ativarModoSelecao()">Juntar Mesas</button>
                  <button id="btn-juntar-mesas" style="display: none;" type="button" class="btn btn-success" data-toggle="modal" data-target="#mesa-principal-modal">
                    Juntar
                  </button>
                  @endif
                  <button id="btn-reserva" type="button" class="btn btn-primary" onclick="resetModal()" data-toggle="modal" data-target="#reserva-modal">
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
                  <x-mesa :id="$mesa->id" :status="$mesa->status" />
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
          <form id="telefoneForm" method="POST" action="{{route('find-client-cel')}}">
            @csrf
            <div class="card-body">
              <div class="form-group">
                <label for="telefone">Telefone/Celular</label>
                <input type="text" class="form-control" id="telefone" name="telefone" placeholder="(xx) xxxxx-xxxx" maxlength="15" oninput="mascaraTelefone(this)">
              </div>
            </div>
          </form>
        </div>
      </x-small_modal>

      <x-small_modal id="mesa-principal-modal" titleModal="MesaPrincipal" textBtn="Continuar">
        <div class="modal-body">
          <form id="mesaPrincipalForm" method="POST" action="{{route('link-tables')}}">
            @csrf
            <div class="card-body">
              <div class="form-group">
                <label for="mesa_principal">Mesa principal</label>
                <input type="number" class="form-control" id="mesa_principal" name="mesa_principal" placeholder="Digite o número da mesa principal">
              </div>
            </div>
          </form>
        </div>
      </x-small_modal>

      <x-medium_modal />

      <x-opcoes_mesas />
    </div>
    <!-- /.content -->
  </div>
  </div>
  <script src="{{asset('src/js/mesas/funcoes.js')}}"></script>

</x-layout>