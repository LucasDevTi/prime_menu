<?php
if ($status == '0') {
    $bgColor = 'success';
} else if ($status == '1') {
    $bgColor = 'info';
} else if ($status == '2') {
    $bgColor = 'danger';
} else if ($status == '3') {
    $bgColor = 'secondary';
} else {
    $bgColor = 'warning';
}
?>

<div class="col-md-2 col-sm-6 col-12" style="cursor:pointer;">
    <div class="info-box bg-gradient-{{$bgColor}} mesa" data-id-mesa="{{$id}}" onclick="showModalOptionsTable('{{$id}}', '{{$status}}')">
        <span class="info-box-icon">{{$id}}</i></span>

        <div class="info-box-content align-items-end">
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