searchApp.filter('tranfo', function() {
  return function(uri, filters) {
    return (Claroline.Home.asset + Claroline.Home.path.substring(1) + 'transfo/' + filters + '?img_uri=' + uri);
  };
});