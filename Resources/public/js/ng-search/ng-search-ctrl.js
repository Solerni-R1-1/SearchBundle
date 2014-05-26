searchApp.controller('ngSearchCtrl', ['$scope', '$location', 'dataSearchFactory', function($scope, $location, dataSearchFactory) {
        var _namespace = {
            'results': null,
            'query': {
                'page':1,
                'items_per_page':5,
                'keywords':'',
                'filters':{}
            }
        };
        
        var _updateQuery = function(facets) {
                //change filters state
                angular.forEach(facets, function(facet) {
                    $scope.data.query.filters[facet.name] = (function(facet) {
                            var elmnts = {};
                            angular.forEach(facet.value, function(elmnt) {
                                    if (!(facet.name in $scope.data.query.filters && 
                                        elmnt.value in $scope.data.query.filters[facet.name])) {
                                        elmnts[elmnt.value] = true;
                                    } else {
                                        elmnts[elmnt.value] = $scope.data.query.filters[facet.name][elmnt.value];
                                    }
                            });
                            return elmnts;
                        })(facet);
                });
        };
        
        
        var _search = function(query) {
                document.getElementById('slrn-wrapper').style.display = 'block';
                console.log(query);
                dataSearchFactory.request(query).then(function(data) {
                _namespace.results = data;
                _namespace.query = query;
                $scope.data = _namespace;
                
            }).then(function() {
                _updateQuery($scope.data.results.facets);
                document.getElementById('slrn-wrapper').style.display = 'none';
            });
        };

        $scope.search = function(query) {
            _search(query);
        };
        
        (function(){
            _search(_namespace.query);
        })();
        
    }]);