'use strict';

angular.module('eveTool')
.controller('corpListController', ['$scope', '$http', function($scope, $http){
    $scope.corps = [];

    $http.get(Routing.generate('api.corps')).then(function(data){
        $scope.corps = data.data;
    });

}]);