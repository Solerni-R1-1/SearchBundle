searchApp.controller('searchFiltersCtrl', ['$scope','$filter', function($scope, $filter) {
        $scope.checkAll = function(list) {
            //console.log(list);
            angular.forEach(list, function(value, key) {
                list[key] = true;
            });
            $scope.search($scope.data.query);
        };
        
        var orderBy = $filter('orderBy');
        $scope.order = function(dic, predicate, reverse) {
            return orderBy(dic, predicate, reverse);
        };
 }]);