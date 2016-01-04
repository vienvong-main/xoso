<div class="container" ng-controller="resultCtrl" ng-init="init()">
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h1 class="panel-title">
                <i class="glyphicon glyphicon-book"></i>
                Kết quả bóng đá - LIVESCORE
            </h1>
        </div>

        <div class="panel-body">
            <div style="height:1500px;overflow:hidden;" id="score-container">
                <div style="visibility:visible;margin-top:-76px;display:block;">
                    <iframe src="http://android.livescore.com/" width="100%" height="1500" frameborder="0" marginheight="0" marginwidth="0" scrolling="yes" id="livescoreif"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>