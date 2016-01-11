

<div class="container" ng-controller="todayCtrl" ng-init="init()">
    <!-- XỔ SỐ THỦ ĐÔ -->
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h1 class="panel-title"><a href="/xsmb">XỔ SỐ THỦ ĐÔ</a></h1>
        </div>

        <div class="panel-body resultFull tableResult mb-result">
            <div class="row">
                <div class="col-sm-3 ad-box"></div>
                <div class="col-sm-6">
                    <div id='statistic-page' class="row container-fluid">
                    	<div id='statistic-header' class="row container-fluid">
                    		<h3>THỐNG KÊ LÔ TÔ</h3>
                    	</div>
                        <form method="POST">
                        	<div class="container-fluid">
                        		<select name='city' class="form-control">
									<?php 
									if(isset($cities)) {
										foreach ($cities as $value) {
											echo '<option value="'. $value['_id'] .'" '. (($value['_id'] == '00')?'selected':null) .'>'. $value['city'] .'</option>';
										}
									} 
									?>
								</select>
                        	</div>
                        	<div class="container-fluid">
                        		<input type="text" name="listnumber" class="form-control" placeholder="Nhập các số muốn thống kế">
                        	</div>
                        	<div class="container-fluid">
                        		<input type="date" name="startdate" class="form-control" value="<?php echo $startdate?>">
                        	</div>
                        	<div class="container-fluid">
								<input type="date" name='enddate' class="form-control" value="<?php echo $enddate?>">
                        	</div>
                        	<div class="container-fluid">
                        		<button type="submit" class="btn btn-info pull-right">Thong Ke</button>
                        	</div>
						</form>

						<?php //var_dump($result['result'])?>


						<?php if(!empty($result)) { ?>

						<div class='table'>
							<div class="table-header">
								<div class='table-row'>
									<div class='table-col'>
										<div class='table-cell'>Lô tô</div>
									</div>
									<div class='table-col'>
										<div class="table-cell">
											Số lần về
										</div>
										<div class="table-cell">
											Số ngày về
										</div>
										<div class="table-cell">
											Số ngày chưa về
										</div>
										<div class="table-cell">
											Ngày về gần nhất
										</div>
									</div>
								</div>
							</div>
							<div class='table-body'>
								<?php if(is_array($result)) {
									foreach ($result as $key => $value) { ?>
										<div class='table-row'>
											<div class='table-col'>
												<div class='table-cell'><?php echo $key;?></div>
											</div>
											<div class='table-col'>
												<div class="table-cell">
													<?php echo $value['count'];?>
												</div>
												<div class="table-cell">
													<?php echo count($value['date']);?>
												</div>
												<div class="table-cell">
													<?php echo $value['long'];?>
												</div>
												<div class="table-cell">
													<?php echo $value['date_MAX'];?>
												</div>
											</div>
										</div>
								<?php }}?>
							</div>
						</div>

						<?php }?>
                    </div>
                    
                </div>
                <div class="col-sm-3 ad-box"></div>
            </div>
            <div class="row ad-box"></div>
        </div>
    </div>

</div>