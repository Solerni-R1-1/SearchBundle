searchApp.controller('ngSearchCtrl', ['$q', '$scope', '$location', 'dataSearchFactory', function($q, $scope, $location, dataSearchFactory) {
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
            if (query.se) {
                var indexOfFacet = _indexOfObjByName($scope.data.facets, query.se);
                var isAll = true;

                angular.forEach(query.ss.split(','), function(ss) {
                	if (ss.indexOf("status__") == 0) {
                		isAll = false;
                	}
                });
                if (indexOfFacet >= 0 && !isAll) {
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
                                if (query.ss && query.ss.split(",").indexOf(facet.name + '__' + elmnt.value) < 0) {
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
                                if (query.ss && query.ss.split(",").indexOf(facet.name + '__' + elmnt.value) < 0) {
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
            dataSearchFactory.request(query).then(function(data) {
                _namespace.results = data;
                _namespace.query = query;
                $scope.data = _namespace;
            }, function(reason) {
                return $q.reject(reason);
            }).then(function() {
                _facetsBuilder(_namespace.query);
                _filtersBuilder(_namespace.query, $scope.data.results.facets);
                 $location.search(_namespace.query);
            }, function(reason) {
            	$('#modal-solr-error').modal();
            }).then(function() {
                document.getElementById('slrn-wrapper').style.display = 'none';
                window.scrollTo(0, 0);
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
            var slices = (function(filters) {
                var elmnts = [];
                angular.forEach(filters, function(value, key) {
                    var generatedSelectionQuery = _generateSelectionQuery(value, key);
                    if ($.trim(generatedSelectionQuery)) {
                        elmnts.push(generatedSelectionQuery);
                    }
                });
                return elmnts;
            })(filters);

            $scope.data.query.ss = slices.join(',');

            if (srcEventFacetName)
                $scope.data.query.se = srcEventFacetName;
            $location.search($scope.data.query);
        };

        $scope.search = function(query) {
            _search(query);

        };


        (function() {
            var searchObject = $location.search();
            angular.extend(_namespace.query, searchObject);
            $location.search(_namespace.query);

            $scope.$on('$locationChangeSuccess', function(event){
                var searchObject = $location.search();
                _namespace.query = searchObject;
                angular.extend(_namespace.query, searchObject);
                _search(_namespace.query);
            });


        })();

    }]);