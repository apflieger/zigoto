var moment = require('moment');

module.exports = function($scope, $http, Upload) {
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

    $scope.validateDateNaissance = function($dateNaissance) {
        if (!$dateNaissance)
            return "L'animal doit avoir une date de naissance";
        if (!moment($dateNaissance, 'DD/MM/YYYY').isValid())
            return "La date doit être au format jj/mm/aaaa"
    };

    $scope.dateNaissanceChanged = function() {
        $scope.pageAnimal.date_naissance = moment($scope.dateNaissanceString, 'DD/MM/YYYY');
        $scope.commit();
    };

    $scope.uploadPhotos = function($files, $file, $newFiles, $duplicateFiles, $invalidFiles, $event) {
        if ($newFiles && $newFiles.length) {
            for (var i = 0; i < $newFiles.length; i++) {
                var file = $newFiles[i];

                $scope.pageAnimal.photos = $scope.pageAnimal.photos || [];

                var photo = {
                    file: file,
                    uploaded: false
                };

                $scope.pageAnimal.photos.push(photo);

                (function(photo) {
                    // Il n'y a aucune authentification, le bucket autorise tous les POST.
                    Upload.upload({
                        url: 'https://zigotoo-runtime.s3.amazonaws.com/', //S3 upload url including bucket name
                        method: 'POST',
                        data: {
                            key: 'images/' + file.name,
                            "Content-Type": file.type != '' ? file.type : 'application/octet-stream',
                            file: file
                        }
                    }).then(function(response) {
                        photo.uploaded = true;
                    });
                })(photo);
            }
        }
    };
};