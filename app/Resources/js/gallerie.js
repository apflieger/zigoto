var $ = require('jquery');
var PhotoSwipe = require('photoswipe/dist/photoswipe.js');
var PhotoSwipeUI_Default = require('photoswipe/dist/photoswipe-ui-default.js');

module.exports = function(photos, index) {
    var pswpElement = document.querySelectorAll('.pswp')[0];

    var items = [];
    for (var i = 0; i < photos.length; i++) {
        var photo = photos[i];
        items.push({
            src: 'https://s3-eu-west-1.amazonaws.com/zigotoo-runtime/images/full/' + photo.nom,
            w: photo.width,
            h: photo.height
        })
    }

    var options = {
        index: index,
        shareEl: false, // pas besoin du bouton share
        fullscreenEl: false // mois y a de boutons mieux on se porte
    };

    var gallery = new PhotoSwipe(pswpElement, PhotoSwipeUI_Default, items, options);
    gallery.init();
};