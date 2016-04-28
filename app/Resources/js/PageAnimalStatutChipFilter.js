var PageAnimalStatut = require('./PageAnimalStatut.js');

module.exports = function() {
    return function(statut) {
        switch (statut) {
            case PageAnimalStatut.DISPONIBLE:
                return "chip-valid";
            case PageAnimalStatut.OPTION:
                return "chip-warn";
            case PageAnimalStatut.RESERVE:
                return "chip-error";
            case PageAnimalStatut.ADOPTE:
                return "";
        }
    };
};