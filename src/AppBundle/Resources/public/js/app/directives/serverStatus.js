'use strict';

angular.module('eveTool')
    .directive('serverStatus', [ '$http', '$interval', function($http, $interval){
        return {
            restrict: 'A',
            link : function(scope, element, attributes) {
                $http.get(Routing.generate('api.server.status')).then(function(data){
                    scope.server_status = data.data;
                });
                $interval(function(){
                    $http.get(Routing.generate('api.server.status')).then(function(data){
                        scope.server_status = data.data;
                    });
                }, 1000*60);
            },
            templateUrl: Routing.generate('template.serverstatus')
        };
    }]);
