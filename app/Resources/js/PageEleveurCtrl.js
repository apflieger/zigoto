/**
 * Created by arnaudpflieger on 17/12/2015.
 */
var zigoto = angular.module('zigoto', ['xeditable']);

zigoto.controller('PageEleveurCtrl', ['$scope', '$http', function ($scope, $http) {
    $scope.pageEleveur = globPageEleveur;
    $scope.commit = function() {
        $http({
            method: 'POST',
            url: '/commit-page-eleveur',
            data: $scope.pageEleveur
        }).then(function successCallback(response) {
            $scope.pageEleveur.commit.id = response.data;
        }, function errorCallback(response) {
            console.debug(response);
        });
    };
}]);