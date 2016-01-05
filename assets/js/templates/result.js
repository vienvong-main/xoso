'use strict';

xosoApp.controller('resultCtrl', function($scope, $filter) {
    $scope.init = function(){
        // default 7 days
        $scope.total = 7;
        $scope.state = '';
        $scope.date = $filter('date')(new Date(),'dd/MM/yyyy');
    };

    $scope.search = function(){
        var errors = [];
        if($scope.state == null || $scope.state == ''){
            errors.push('Chưa chọn khu vực')
        }
        if($scope.date == null || $scope.date == ''){
            errors.push('Chưa chọn ngày')
        }

        var parts = $scope.date.split("/");
        var isOK = false;
        console.log(parts);
        if (parts.length == 3) {
            if (!$scope.isInt(parts[0]) || !$scope.isInt(parts[1]) || !$scope.isInt(parts[2])) {

            } else {
                var date = new Date();
                date.setHours(0, 0, 0, 0);
                date.setYear(parseInt(parts[2]));
                date.setMonth(parseInt(parts[1] - 1));
                date.setDate(parseInt(parts[0]));
                isOK = true;
            }
        }

        if (!isOK) {
            errors.push('Ngày sai cú pháp');
        }


        if(errors.length > 0){
            alert(errors);
        }else{
            console.log(date);
        }
    };

    $scope.isInt = function(str) {
        return !isNaN(parseInt(str));
    }

});
