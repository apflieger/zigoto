module.exports = function($scope, $http) {
    // Variable injectée dans la page par le backend
    $scope.pageAnimal = globPageAnimal;

    $scope.commit = function() {
        $http({
            method: 'POST',
            url: '/animal/' + $scope.pageAnimal.id,
            data: $scope.pageAnimal
        }).then(function successCallback(response) {
            $scope.pageAnimal = response.data;
        }, function errorCallback(response) {
            console.debug(response);
        });
    };

    $scope.validateNom = function($nom) {
        if (!$nom)
            return "L'animal doit avoir un nom";
    };
};