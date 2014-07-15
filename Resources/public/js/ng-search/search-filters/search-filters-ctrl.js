searchApp.controller('searchFiltersCtrl', ['$scope','$filter', function($scope, $filter) {
        
        var _areCheckboxChecked = function (list) {
            var result = true;
            angular.forEach(list, function(value, key) {
                if (key !== 'all') result  = result && value;
            });
            return result;
        };
        
        $scope.onChangeAllCheckbox = function(list, srcFacetName) {
            if (list['all']) {
                angular.forEach(list, function(value, key) {
                    list[key] = false;
                });
            } 
            list['all'] = true;
            $scope.updateQuery($scope.data.filters, srcFacetName);
        };
        
        $scope.onChangeCheckbox = function(list, srcFacetName) {
            if (_areCheckboxChecked(list)) {
                angular.forEach(list, function(value, key) {
                    list[key] = false;
                });
                list['all'] = true;
            } else {
                list['all'] = false;
            }
            $scope.updateQuery($scope.data.filters, srcFacetName);
        };
        
        
        var orderBy = $filter('orderBy');
        $scope.order = function(dic, predicate, reverse) {
            return orderBy(dic, predicate, reverse);
        };
 }]);