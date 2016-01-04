<div class="container" ng-controller="resultCtrl" ng-init="init()">
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h1 class="panel-title">
                <i class="glyphicon glyphicon-book"></i>
                <?php echo $title_score;?> - LIVESCORE
            </h1>
        </div>

        <div class="panel-body">
            <div style="width:100%;background-color:#333" align="center">
                <a href="<?php echo current_url();;?>">
                    <img src="/assets/img/back.jpg">
                </a>
            </div>
            <div style="height:1000px;overflow:hidden;" id="score-container">
                <div style="visibility:visible;margin-top:-76px;display:block;">
                    <iframe src="<?php echo $link_score;?>" width="100%" height="1000" frameborder="0" marginheight="0" marginwidth="0" scrolling="yes" id="livescoreif"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>