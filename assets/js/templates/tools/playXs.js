'use strict';

xosoApp.controller('playXsCtrl', function($scope, $interval) {
    $scope.short2Long = {'mb': 'Miền Bắc', 'mn': 'Miền Nam', 'mt': 'Miền Trung'};
    $scope.prizeText = ['Đặc Biệt', 'Giải Nhất', 'Giải Nhì', 'Giải Ba', 'Giải Tư', 'Giải Năm', 'Giải Sáu', 'Giải Bảy', 'Giải Tám'];
    $scope.quayText = 'Quay!';
    $scope.loto = [];
    $scope.num = [];
    $scope.dig = [];
    $scope.exp = [10, 100, 1000, 10000, 100000, 1000000];

    $scope.init = function(zone){
        $scope.region = zone;
        switch ($scope.region){
            case 'mb':
                $scope.num = [1,1,2,6,4,6,3,4];
                $scope.dig = [5,5,5,5,4,4,3,2];
                break;
            case 'mn':
            case 'mt':
                $scope.num = [1,1,1,2,7,1,3,1,1];
                $scope.dig = [6,5,5,5,5,4,4,3,2];
                break;
        }
        $scope.prizes = $scope.generate($scope.num, $scope.dig, '*');
    }

    var running;

    $scope.quay = function(){
        if (angular.isDefined(running)) {
            $interval.cancel(running);
            $scope.quayText = 'Quay!';
            running = undefined;
        } else {
            running = $interval(function () {
                $scope.prizes = $scope.generate($scope.num, $scope.dig);
                $scope.loto = Array(10);
                for (var i = 0; i <= 9; ++i) {
                    $scope.loto[i] = [];
                }

                for (var i = 0; i < $scope.prizes.length; ++i) {
                    for (var j = 0; j < $scope.prizes[i].length; ++j) {
                        var curPrize = $scope.prizes[i][j];
                        if (curPrize && curPrize.length >= 2) {
                            var head = parseInt(curPrize[curPrize.length - 2]);
                            var tail = parseInt(curPrize[curPrize.length - 1]);
                            if (head != null && tail != null) {
                                $scope.loto[head].push(tail);
                            }
                        }
                    }
                }
                //console.log($scope.loto);

                $scope.quayText = 'Dừng!';
            }, 50);
        }
    }

    $scope.generate = function(num, dig, mask){
        var result = [];
        var exp = $scope.exp;

        for (var i = 0; i < num.length; ++i) {
            var prize = [];
            for (var j = 0; j < num[i]; ++j) {
                var p = '';
                if (mask) {
                    for (var t = 0; t < dig[i]; ++t)
                        p = p.concat(mask);
                } else {
                    p = Math.floor(Math.random() * exp[dig[i] - 1]) + '';
                    while (p.length < dig[i]) p = '0'.concat(p);
                }
                prize.push(p);
            }
            result.push(prize);
        }
        return result;
    }
});
