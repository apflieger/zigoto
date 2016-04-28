var PageAnimalStatut = require('./PageAnimalStatut.js');
var PageEleveurTab = require('./PageEleveurTab.js');

module.exports = function() {
    return function(animaux, tab) {
        return animaux.filter(function(pageAnimal){
            if (tab === PageEleveurTab.EN_VENTE)
                return pageAnimal.statut !== PageAnimalStatut.ADOPTE ? pageAnimal : null;
            else
                return pageAnimal.statut === PageAnimalStatut.ADOPTE ? pageAnimal : null;
        });
    };
};