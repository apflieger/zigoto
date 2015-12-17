/**
 * Created by arnaudpflieger on 17/12/2015.
 */
var zigoto = angular.module('zigoto', []);

zigoto.controller('PageEleveurCtrl', function ($scope) {
    $scope.pageEleveur = {'description': pageEleveurDescription};
});