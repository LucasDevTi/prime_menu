<x-layout>
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
                <div class="d-flex justify-content-between">
                  <h3 class="card-title">Online Store Visitors</h3>
                </div>
              </div>
              <div class="card-body">
                <!-- =========================================================== -->
                <h5 class="mt-4 mb-2">Info Box With <code>bg-gradient-*</code></h5>
                <div class="row">
                  <div class="col-md-2 col-sm-6 col-12" style="cursor:pointer;">
                    <div class="info-box bg-gradient-info">
                      <span class="info-box-icon">2</i></span>

                      <div class="info-box-content">
                        <span class="info-box-text"><i class="fas fa-user"></i> <span class="reserve">1</span></span>
                        <span class="info-box-number">41,50</span>

                        <!-- <div class="progress">
                          <div class="progress-bar" style="width: 70%"></div>
                        </div> -->
                        <span class="progress-description">
                          10:30
                        </span>
                      </div>
                      <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                  </div>
                  <!-- /.col -->
                  <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box bg-gradient-success">
                      <span class="info-box-icon"><i class="far fa-thumbs-up"></i></span>

                      <div class="info-box-content">
                        <span class="info-box-text">Likes</span>
                        <span class="info-box-number">41,410</span>

                        <div class="progress">
                          <div class="progress-bar" style="width: 70%"></div>
                        </div>
                        <span class="progress-description">
                          70% Increase in 30 Days
                        </span>
                      </div>
                      <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                  </div>
                  <!-- /.col -->
                  <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box bg-gradient-warning">
                      <span class="info-box-icon"><i class="far fa-calendar-alt"></i></span>

                      <div class="info-box-content">
                        <span class="info-box-text">Events</span>
                        <span class="info-box-number">41,410</span>

                        <div class="progress">
                          <div class="progress-bar" style="width: 70%"></div>
                        </div>
                        <span class="progress-description">
                          70% Increase in 30 Days
                        </span>
                      </div>
                      <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                  </div>
                  <!-- /.col -->
                  <div class="col-md-3 col-sm-6 col-12">
                    <div class="info-box bg-gradient-danger">
                      <span class="info-box-icon"><i class="fas fa-comments"></i></span>

                      <div class="info-box-content">
                        <span class="info-box-text">Comments</span>
                        <span class="info-box-number">41,410</span>

                        <div class="progress">
                          <div class="progress-bar" style="width: 70%"></div>
                        </div>
                        <span class="progress-description">
                          70% Increase in 30 Days
                        </span>
                      </div>
                      <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                  </div>
                  <!-- /.col -->
                </div>
                <!-- /.row -->
              </div>
            </div>
            <!-- /.card -->
          </div>
        </div>
        <!-- /.row -->
      </div>
      <!-- /.container-fluid -->
    </div>
    <!-- /.content -->
  </div>
</x-layout>