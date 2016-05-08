var PageEleveurTab = require('./PageEleveurTab.js');

module.exports = function($scope, $http) {
    // Variable injectée dans la page par le backend
    $scope.pageEleveur = globPageEleveur;

    $scope.tab = PageEleveurTab.EN_VENTE;
    $scope.PageEleveurTab = PageEleveurTab;

    // si une requete http de commit est en cours
    var commiting = false;

    /*
     si commit() a été appelé alors qu'une requete de commit est deja en cours,
     on ne peut pas en lancer une 2eme en parallèle car le 2eme commit
     ne serait pas fastforward du 1er. On bloque alors le 2eme commit, en attendant
     que le 1er se finisse.
     */
    var pendingChanges = false;

    $scope.commit = function() {

        if (commiting) {
            pendingChanges = true;
            return;
        }
        commiting = true;

        $http({
            method: 'POST',
            url: '/commit-page-eleveur',
            data: $scope.pageEleveur
        }).then(function successCallback(response) {
            commiting = false;
            if (pendingChanges) {
                $scope.pageEleveur.head = response.data.head;
            } else {
                $scope.pageEleveur = response.data;
            }

            if (pendingChanges) {
                pendingChanges = false;
                $scope.commit();
            }

        }, function errorCallback(response) {
            commiting = false;
            pendingChanges = true;
        });
    };

    $scope.addAnimal = function() {
        $scope.tab = PageEleveurTab.EN_VENTE;
        $http({
            method: 'POST',
            url: '/animal'
        }).then(function successCallback(response) {
            var pageAnimal = response.data;

            $scope.pageEleveur.animaux = $scope.pageEleveur.animaux || [];
            $scope.pageEleveur.animaux.unshift(pageAnimal);

            $scope.commit();
        }, function errorCallback(response) {
            console.debug(response);
        });
    };

    $scope.supprimerAnimal = function(pageAnimal, $event) {
        // le bouton est dans le lien vers la PA, on ne veut pas que le lien soit cliqué
        $event.stopPropagation();
        $event.preventDefault();
        
        var index = $scope.pageEleveur.animaux.indexOf(pageAnimal);
        if (index > -1) {
            $scope.pageEleveur.animaux.splice(index, 1);
        }

        $scope.commit();
    };

    $scope.addActualite = function() {
        $scope.pageEleveur.actualites = $scope.pageEleveur.actualites || [];
        $scope.pageEleveur.actualites.unshift( {
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
