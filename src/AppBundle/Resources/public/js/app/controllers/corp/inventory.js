'use strict';

angular.module('eveTool')
    .controller('inventoryController', ['$scope', 'corporationDataManager', 'selectedCorpManager', function($scope, corporationDataManager, selectedCorpManager){
        $scope.loading = true;
        $scope.predicate = 'total_price';
        $scope.reverse = true;
        $scope.image_width = 32;
        $scope.max_size = 15;
        $scope.per_page = 5;
        $scope.page = 1;
        $scope.filters = {
            market_group: null,
            list: []
        };
        $scope.per_page_selection = [
            { label: '5', value: 5},
            { label: '10', value: 10},
            { label: '15', value: 15},
            { label: '25', value: 25},
            { label: '50', value: 50},
            { label: '100', value: 100}
        ];

        $scope.addFilter = function(filter){
            $scope.filters.list.push({
                name: filter.name,
                type: 1,
                value: filter.value
            });
        };

        $scope.getActiveFilters = function(){
            var activeFilters = [];
            for(var i = 0 ; i <= $scope.filters.list.length - 1; i++){
                activeFilters.push($scope.filters.list[i]);
            }
            return activeFilters;
        };

        $scope.$watch(function(){ return selectedCorpManager.get(); }, function(val){
            if (typeof val.id === 'undefined'){
                return;
            }

            $scope.selected_corp = val;
            $scope.assets = [];
            $scope.loading = true;

            corporationDataManager.getMarketGroups().then(function(val){
                $scope.market_groups = val;
            });

            corporationDataManager.getCorpInventory(val, $scope.page, $scope.per_page).then(function(data){
                var items = data.items;
                $scope.assets = items.items;
                $scope.total_price = items.total_price;
                $scope.total_items = data.total_count;
                $scope.per_page = data.num_items_per_page;
                $scope.page = data.current_page_number;
                $scope.loading = false;
            }).then(function(){
                function updateInventory(){
                    $scope.assets = [];
                    $scope.loading = true;
                    corporationDataManager.getCorpInventory($scope.selected_corp, $scope.page, $scope.per_page).then(function(data){
                        var items = data.items;
                        $scope.assets = items.items;
                        $scope.per_page = data.num_items_per_page;
                        $scope.page = data.current_page_number;
                        $scope.loading = false;
                    });
                }

                $scope.pageChanged = function(){
                    updateInventory();
                };

                $scope.$watch('per_page', function(){
                    updateInventory();
                });
            });

            corporationDataManager.getLastUpdate(val, 2).then(function(data){
                $scope.updated_at = moment(data.created_at).format('x');
                $scope.update_succeeded = data.succeeded;
                $scope.next_update = moment(data.created_at).add(10, 'hours').format('x');
            });
        });


        $scope.totalM3 = function(){
            var total = 0;
            angular.forEach($scope.assets, function(item){
                total += $scope.getM3(item);
            });

            return total;
        };


        $scope.getM3 = function(item){
            if (item && typeof item.descriptors != 'undefined' && typeof item.descriptors.volume !== 'undefined')
                return parseFloat(item.descriptors.volume) * item.quantity;
        };

        $scope.sumItems = function(){
            if (!$scope.price_reference.length){
                return 0;
            }
            var total = 0;
            angular.forEach($scope.assets, function(item){
                var price = $scope.getPrice(item);

                if (typeof price != 'undefined'){
                    total += price.average_price * item.quantity;
                }
            });
            return total;
        };


        $scope.getPrice = function(type){
            if (typeof type === 'undefined'){
                return;
            }
            return type.descriptors.total_price;
        };

        $scope.order = function(predicate){
            $scope.reverse = ($scope.predicate === predicate) ? !$scope.reverse : false;
            $scope.predicate = predicate;
        };
    }]);
