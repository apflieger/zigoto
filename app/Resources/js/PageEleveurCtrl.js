/**
 * Created by arnaudpflieger on 17/12/2015.
 */
var zigoto = angular.module('zigoto', []);

zigoto.controller('PageEleveurCtrl', function ($scope) {
    $scope.pageEleveur = globPageEleveur;
    $scope.commit = function() {
        console.debug($scope.pageEleveur);
    };
});