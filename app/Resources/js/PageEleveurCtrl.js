var PageEleveurTab = require('./PageEleveurTab.js');

module.exports = function($scope, $http) {
    // Variable injectée dans la page par le backend
    $scope.pageEleveur = globPageEleveur;

    $scope.tab = PageEleveurTab.EN_VENTE;
    $scope.PageEleveurTab = PageEleveurTab;

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
        $scope.tab = PageEleveurTab.EN_VENTE;
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

    $scope.addActualite = function() {
        $scope.pageEleveur.actualites = $scope.pageEleveur.actualites || [];
        $scope.pageEleveur.actualites.push( {
            contenu: '',
            date: new Date()
        });

        $scope.commit();
    };

    $scope.removeActualite = function($actualite) {
        var index = $scope.pageEleveur.actualites.indexOf($actualite);
        if (index > -1) {
            $scope.pageEleveur.actualites.splice(index, 1);
        }

        $scope.commit();
    };
};
