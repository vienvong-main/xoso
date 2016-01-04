'use strict';

xosoApp.controller('resultCtrl', function($scope, $filter) {
    $scope.init = function(){
        $scope.region = "";
        $scope.date = $filter('date')(new Date(),'dd/MM/yyyy');
    };

    $scope.search = function(){
        console.log($scope.region);
        console.log($scope.date);
    };
});
