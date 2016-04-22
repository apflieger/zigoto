var moment = require('moment');

module.exports = function($scope, $http) {
    // Variable injectée dans la page par le backend
    $scope.pageAnimal = globPageAnimal;
    $scope.dateNaissanceString = moment(globPageAnimal.date_naissance).format('DD/MM/YYYY');

    $scope.commit = function() {
        $http({
            method: 'POST',
            url: '/animal/' + $scope.pageAnimal.id,
            data: $scope.pageAnimal
        }).then(function successCallback(response) {
            $scope.pageAnimal = response.data;
            $scope.dateNaissanceString = moment($scope.pageAnimal.date_naissance).format('DD/MM/YYYY');
        }, function errorCallback(response) {
            console.debug(response);
        });
    };

    $scope.validateNom = function($nom) {
        if (!$nom)
            return "L'animal doit avoir un nom";
    };

    $scope.dateNaissanceChanged = function() {
        $scope.pageAnimal.date_naissance = moment($scope.dateNaissanceString, 'DD-MM-YYYY');
        $scope.commit();
    };
};