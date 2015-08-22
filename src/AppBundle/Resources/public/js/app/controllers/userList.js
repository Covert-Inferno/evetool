'use strict';

angular.module('eveTool')
.controller('userListController', ['$scope', '$http','$document', function($scope, $http, $document){
    $scope.users = [];
    $scope.nav_open = false;
    $scope.submitLoading = false;

    $scope.newUser = {};

    $http.get(Routing.generate('api.users')).then(function(data){
            $scope.users = data.data;
    });


    $scope.submit = function(){
        $scope.submitLoading = true;
        $http.post(Routing.generate('api.user_create'), $scope.newUser).then(function(data){

            $scope.submitLoading = false;
        });
    };

    $scope.openNew = function(){
        if (!$scope.nav_open){
            $('.push-menu').animate({
                right: "0px"
            }, 400);

            $('body').animate({
                left: "-350px"
            }, 400);
            $scope.nav_open = true;

        } else {
            $('.push-menu').animate({
                right: "-350px"
            }, 400);

            $('body').animate({
                left: "0px"
            }, 400);

            $scope.nav_open = false;
        }
    };
}]);