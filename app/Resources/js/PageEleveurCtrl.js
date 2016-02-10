/**
 * Created by arnaudpflieger on 17/12/2015.
 */
var zigotoo = angular.module('zigotoo', ['xeditable']);

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