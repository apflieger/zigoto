var PageAnimalStatut = require('./PageAnimalStatut.js');

module.exports = function() {
    return function(statut) {
        switch (statut) {
            case PageAnimalStatut.DISPONIBLE:
                return "Disponible";
            case PageAnimalStatut.OPTION:
                return "Option";
            case PageAnimalStatut.RESERVE:
                return "Réservé";
            case PageAnimalStatut.ADOPTE:
                return "Adopté";
        }
    };
};