searchApp.controller('searchFiltersCtrl', ['$scope','$filter', function($scope, $filter) {
        //namespaces
        $scope.functions = {
            filters : {
                checkboxAll: {},
                ispublic :{}
            }
        };

        // ispublic
        $scope.functions.filters.ispublic.set = function (status) {
            $scope.data.filters['ispub']['true'] = status;
            $scope.data.filters['ispub']['false'] = ! status;
            $scope.updateQuery($scope.data.filters, 'ispub');
        };


        // checkbox all
        var _areCheckboxChecked = function (list) {
            var result = true;
            angular.forEach(list, function(value, key) {
                if (key !== 'all') {
                    result  = ( result && value );
                }
            });
            return result;
        };

        $scope.functions.filters.checkboxAll.onChangeAllCheckbox = function(list, srcFacetName) {
            if (list['all']) {
                angular.forEach(list, function(value, key) {
                    list[key] = false;
                });
            }
            list['all'] = true;
            $scope.updateQuery($scope.data.filters, srcFacetName);
        };

        $scope.functions.filters.checkboxAll.onChangeCheckbox = function(list, srcFacetName) {
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

 }]);