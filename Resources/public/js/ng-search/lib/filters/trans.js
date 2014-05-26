searchApp.filter('trans', function() {
  return function(input) {
    return Translator.get('search:' + input);
  };
});