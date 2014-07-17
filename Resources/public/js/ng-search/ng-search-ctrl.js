searchApp.controller('ngSearchCtrl', ['$scope', '$location', 'dataSearchFactory', function($scope, $location, dataSearchFactory) {
        var _namespace = {
            'results': null,
            'query': {
                'page': 1,
                'rpp': 5,
                'q': '',
                'fs': '',
                'ss': '',
                'afs': 'mcat,type',
                'sb': false
            },
            'filters': {},
            'facets': []
        };          

          
        var _indexOfObjByName = function(objects, name) {
            for( var i=0;  i < objects.length; i++) {
                if (objects[i].name === name) return i;
            }
            return -1;
        };
              
        
        var _facetsBuilder = function(query) {
            if (query.se ) {
                var indexOfFacet = _indexOfObjByName($scope.data.facets, query.se);
                if (indexOfFacet > 0 ) {
                    $scope.data.results.facets[indexOfFacet] = $scope.data.facets[indexOfFacet];
                }
            } 
           $scope.data.facets = $scope.data.results.facets;
        };
        
        var _filtersBuilder = function(query, facets) {
            $scope.data.showPublicPrivateDiv = false;
            angular.forEach(facets, function(facet) {
                
                switch (facet.type) {
                    case 'checkbox-all' :
                        $scope.data.filters[facet.name] = (function(facet) {
                            var elmnts = {};
                            elmnts['all'] = true;
                            angular.forEach(facet.value, function(elmnt) {
                                if (query.ss.split(",").indexOf(facet.name + '__' + elmnt.value) < 0) {
                                    elmnts[elmnt.value] = false;
                                } else {
                                    elmnts[elmnt.value] = true;
                                    elmnts['all'] = false;
                                }
                            });
                            return elmnts;
                        })(facet);
                        break;
                    case 'checkbox' : 
                        if (facet.name === 'ispub')
                            $scope.data.showPublicPrivateDiv = true;
                        $scope.data.filters[facet.name] = (function(facet) {
                            var elmnts = {};
                            angular.forEach(facet.value, function(elmnt) {
                                if (query.ss.split(",").indexOf(facet.name + '__' + elmnt.value) < 0) {
                                    elmnts[elmnt.value] = false;
                                } else {
                                    elmnts[elmnt.value] = true;
                                }
                            });
                            return elmnts;
                        })(facet);
                        break;
                }

            });
        };


        var _search = function(query) {
            document.getElementById('slrn-wrapper').style.display = 'block';
            //console.log(query);
            dataSearchFactory.request(query).then(function(data) {
                _namespace.results = data;
                _namespace.query = query;
                $scope.data = _namespace;

            }).then(function() {
                document.getElementById('slrn-wrapper').style.display = 'none';
            }).then(function() {
                $location.search(_namespace.query);
            }).then(function() {
                _facetsBuilder(_namespace.query);
                _filtersBuilder(_namespace.query, $scope.data.results.facets);
                console.log($scope.data.filters);
            });
        };

        var _generateSelectionQuery = function(elmnts, nameFilter) {
            selectionQueries = [];
            if (!elmnts['all']) {
                angular.forEach(elmnts, function(value, name) {
                    if (value) {
                        selectionQueries.push(nameFilter + '__' + name);
                    }
                });
            }
            return selectionQueries.join(',');
        };


        $scope.updateQuery = function(filters, srcEventFacetName) {
            //update query values
            $scope.data.query.ss = (function(filters) {
                var elmnts = [];
                angular.forEach(filters, function(value, key) {
                    elmnts.push(_generateSelectionQuery(value, key));
                });
                return elmnts.join(',');
            })(filters);
            if (srcEventFacetName)
                $scope.data.query.se = srcEventFacetName;
            _search($scope.data.query);
        };

        $scope.search = function(query) {
            _search(query);
        };


        (function() {
            var searchObject = $location.search();
            angular.extend(_namespace.query, searchObject);
            console.log(_namespace.query);
            _search(_namespace.query);
        })();

    }]);