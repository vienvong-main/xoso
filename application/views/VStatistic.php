

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
                    <div class="row">
                        <form method="POST">
							<select name='city'>
								<!-- <option value="00">Truyền thống</option> -->
								<?php 
								if(isset($cities)) {
									foreach ($cities as $value) {
										echo '<option value="'. $value['_id'] .'" '. (($value['_id'] == '00')?'selected':null) .'>'. $value['city'] .'</option>';
									}
								} 
								?>
							</select>
							<input type="text" name="listnumber">
							<input type="date" name="startdate" value="<?php echo $startdate?>">
							<input type="date" name='enddate' value="<?php echo $enddate?>">
							<button type="submit">Thong Ke</button>
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