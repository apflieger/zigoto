var $ = require('jquery');
var gallerie = require('./gallerie.js');

$('#gallerie').on('click', 'img', function(event) {
    gallerie(globPageAnimal.photos);
});