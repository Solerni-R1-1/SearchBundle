searchApp.controller('searchFiltersCtrl', ['$scope', function($scope) {
        $scope.checkAll = function(list) {
            //console.log(list);
            angular.forEach(list, function(value, key) {
                list[key] = true;
            });
            $scope.search($scope.data.query);
        };
 }]);