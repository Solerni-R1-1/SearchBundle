searchApp.factory('ngSearchService', ['$http', '$q', function($http, $q) {
        return {
            'post': function(query){
                return $http.post(Claroline.Home.path + 'search/query.json', query);
            }
        };
}]);