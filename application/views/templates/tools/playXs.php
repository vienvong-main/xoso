<div class="container" ng-controller="playXsCtrl" ng-init="init('<?php echo $region;?>')">
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h1 class="panel-title">Quay Thử XS {{short2Long[region]}}</h1>
        </div>

        <div class="panel-body">
            <div>
                <i class='glyphicon glyphicon-alert'></i> <i>Chú ý: Kết quả mang tính tham khảo. Chúc các bạn may mắn!</i>
            </div>
            <br/>
            <div align="center">
                <div ng-click="quay()" class="btn btn-danger"><span class="lead">{{quayText}}</span></div>
            </div>
            <br/>

            <div class="row">
                <div class="col-sm-9 col-xs-12 col-no-padding">
                    <table class="table table-bordered quaythuResult">
                        <tr ng-repeat="prize in prizes track by $index">
                            <td align='center' class="prize-text" width="25%">{{prizeText[$index]}}</td>
                            <td>
                                <table style="width: 100%;" class="subResult">
                                    <tr>
                                        <td colspan="{{ 12 / (prize.length > 3 ? 3 : prize.length) }}"
                                            class='pz'
                                            ng-repeat="p in prize track by $index" ng-show="$index < 3">{{p}}
                                        </td>
                                    </tr>
                                    <tr ng-if="prize.length > 3">
                                        <td colspan="{{ 12 / (prize.length - 3) }}" class='pz'
                                            ng-repeat="p in prize track by $index" ng-show="$index >= 3">{{p}}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-sm-3 col-xs-12">
                    <table class="subLoto table table-bordered">
                        <thead>
                        <th width="30%">Đầu</th>
                        <th>Đuôi</th>
                        </thead>
                        <tbody>
                        <tr ng-repeat="i in [0,1,2,3,4,5,6,7,8,9]">
                            <td class="head">{{i}}</td>
                            <td>{{loto[i].join(' ')}}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div align="center">
                <div ng-click="quay()" class="btn btn-danger">
                    <span class="lead">{{quayText}}</span>
                </div>
            </div>
        </div>

    </div>

</div>