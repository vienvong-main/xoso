'use strict';

xosoApp.controller('resultCtrl', function($scope, $filter) {
    $scope.init = function(){
        $scope.region = '';
        $scope.date = $filter('date')(new Date(),'dd/MM/yyyy');
    };

    $scope.search = function(){
        var erros = [];
        if($scope.region == null || $scope.region == ''){
            erros.push('Chưa chọn khu vực')
        }
        if($scope.date == null || $scope.date == ''){
            erros.push('Chưa chọn ngày')
        }

        if(erros.length > 0){
            alert(erros);
        }
    };
});
