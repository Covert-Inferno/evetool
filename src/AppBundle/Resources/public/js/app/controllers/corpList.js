'use strict';

angular.module('eveTool')
.controller('corpListController', ['$scope', '$http', function($scope, $http){
    $scope.corps = [];
    $scope.nav_open = false;
    $scope.submitLoading = false;

    $scope.newCorp = {};

    $http.get(Routing.generate('api.corps')).then(function(data){
            $scope.corps = data.data;
    });


    $scope.submit = function(){
        $scope.submitLoading = true;
        $http.post(Routing.generate('api.corp_create'), $scope.newUser).then(function(data){
            $scope.corps.push(data.data);
            $scope.submitLoading = false;

            $scope.newUser = {};
            if ($scope.nav_open){
                toggleNav();
            }
        }).catch(function(data){
            $scope.errors = data.data;
            $scope.submitLoading = false;
        });
    };

    $scope.openNew = function(){
        toggleNav();
    };

    function toggleNav() {
        if (!$scope.nav_open){
            $('.push-menu').animate({
                right: "0px"
            }, 300);

            $('body').animate({
                left: "-350px"
            }, 300);
            $scope.nav_open = true;

        } else {
            $('.push-menu').animate({
                right: "-350px"
            }, 300);

            $('body').animate({
                left: "0px"
            }, 300);

            $scope.nav_open = false;
        }

        $scope.newCorp = {};
    }
}]);