var zigotoo = angular.module('zigotoo', ['xeditable']);

zigotoo.run(function(editableOptions, editableThemes) {
    // surcharge du template par défaut pour y ajour les styles de btn et les titles
    editableThemes['default'].submitTpl = '<button type="submit" class="btn btn-primary" title="Ctrl+Entrer">Valider</button>';
    editableThemes['default'].cancelTpl = '<button type="button btn" class="btn btn-secondary" ng-click="$form.$cancel()" title="Esc">Annuler</button>';
});

zigotoo.controller('PageEleveurCtrl', ['$scope', '$http', function ($scope, $http) {

    $scope.pageEleveur = globPageEleveur;

    $scope.commit = function() {
        $http({
            method: 'POST',
            url: '/commit-page-eleveur',
            data: $scope.pageEleveur
        }).then(function successCallback(response) {
            $scope.pageEleveur = response.data;
        }, function errorCallback(response) {
            console.debug(response);
        });
    };

    $scope.addAnimal = function() {
        $http({
            method: 'POST',
            url: '/add-animal',
            data: $scope.pageEleveur
        }).then(function successCallback(response) {
            $scope.pageEleveur = response.data;
        }, function errorCallback(response) {
            console.debug(response);
        });
    };
}]);