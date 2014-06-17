searchApp.filter('path', function() {
  return function(relativeLink) {
    return Claroline.Home.path + relativeLink.replace(/^\/|\/$/g, '');;
  };
});