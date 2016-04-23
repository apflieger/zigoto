module.exports = function() {
    return function(statut) {
        switch (statut) {
            case 1:
                return "chip-valid";
            case 2:
                return "chip-warn";
            case 3:
                return "chip-error";
        }
    };
};