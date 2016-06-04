var PageAnimalSexe = require('./PageAnimalSexe.js');

module.exports = function() {
    return function(statut) {
        switch (statut) {
            case PageAnimalSexe.MALE:
                return "Mâle";
            case PageAnimalSexe.FEMELLE:
                return "Femelle";
        }
    };
};