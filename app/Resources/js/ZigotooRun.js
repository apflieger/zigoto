module.exports = function(editableOptions, editableThemes) {
    // surcharge du template par défaut pour y ajour les styles de btn et les titles
    editableThemes['default'].submitTpl = '<button type="submit" class="btn btn-primary" title="Ctrl+Entrer">Valider</button>';
    editableThemes['default'].cancelTpl = '<button type="button" class="btn btn-secondary" ng-click="$form.$cancel()" title="Esc">Annuler</button>';
};