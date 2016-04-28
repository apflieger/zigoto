module.exports = function() {
    return function(statut) {
        switch (statut) {
            case 1:
                return "Disponible";
            case 2:
                return "Option";
            case 3:
                return "Réservé";
            case 4:
                return "Adopté";
        }
    };
};