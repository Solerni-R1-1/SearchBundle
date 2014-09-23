searchApp.factory('ngSearchService', ['$http', '$q', function($http, $q) {
        return {
            'get': function(query){
               return  $http({
                   method: 'GET', 
                   url: Claroline.Home.path + 'search/query.json',
                   params: query
               });
            }
        };
}]);