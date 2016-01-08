'use strict';

xosoApp.controller('resultCtrl', function($scope, $filter, $http) {
    $scope.init = function(){
        // default 7 days
        $scope.total = 7;
        $scope.state = '';
        $scope.date = $filter('date')(new Date(),'dd/MM/yyyy');
    };

    $scope.search = function(){
        var errors = [];
        var parts = $scope.date.split("/");
        var isOK = false;

        if($scope.state == null || $scope.state == ''){
            errors.push('Chưa chọn khu vực')
        }
        if($scope.date == null || $scope.date == ''){
            errors.push('Chưa chọn ngày')
        }

        if (parts.length == 3) {
            if (!$scope.isInt(parts[0]) || !$scope.isInt(parts[1]) || !$scope.isInt(parts[2])) {
                isOK = false;
            } else {
                isOK = true;
            }
        }

        if (!isOK) {
            errors.push('Ngày sai cú pháp');
        }

        if(errors.length > 0){
            alert(errors);
        }else{

            $http({
                method: 'POST',
                url: '/result/search',
                data: {
                    total: $scope.total,
                    date: $scope.date,
                    state: $scope.state
                }
            }).then(function successCallback(response) {
                console.log(response);
            }, function errorCallback(response) {
                console.log(response);
            });
        }
    };

    $scope.isInt = function(str) {
        return !isNaN(parseInt(str));
    }

});
