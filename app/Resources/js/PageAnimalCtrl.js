var moment = require('moment');
var gallerie = require('./gallerie.js');

module.exports = function($scope, $http, Upload) {
    // Variable injectée dans la page par le backend
    $scope.pageAnimal = globPageAnimal;
    $scope.dateNaissanceString = moment(globPageAnimal.date_naissance).format('DD/MM/YYYY');
    $scope.dirtyPhotos = globPageAnimal.photos || [];
    for (var i = 0; i < $scope.dirtyPhotos.length; i++) {
        $scope.dirtyPhotos[i].uploaded = true;
    }

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

        $scope.pageAnimal.photos = $scope.dirtyPhotos.filter(function(photo){
            return !Upload.isFile(photo) || photo.uploaded;
        });

        $http({
            method: 'POST',
            url: '/animal/' + $scope.pageAnimal.id,
            data: $scope.pageAnimal
        }).then(function successCallback(response) {
            commiting = false;
            if (pendingChanges) {
                $scope.pageAnimal.head = response.data.head;
            } else {
                $scope.pageAnimal = response.data;
            }
            $scope.dateNaissanceString = moment($scope.pageAnimal.date_naissance).format('DD/MM/YYYY');

            if (pendingChanges) {
                pendingChanges = false;
                $scope.commit();
            }

        }, function errorCallback(response) {
            commiting = false;
            pendingChanges = true;
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

                file.nom = Math.random().toString(16).slice(2) + file.type.replace('image/', '.');
                file.uploaded = false;

                var contentType = file.type != '' ? file.type : 'application/octet-stream';

                (function(file, contentType) {
                    var fileUploaded = 0;

                    // Upload de la version full de l'image
                    uploadResizedFile(file, 'images/full/' + file.nom);

                    // Upload de la version thumbnail de l'image
                    Upload.resize(file, 300, 300, 0.8, null, null, true).then(function(resizedFile){
                        uploadResizedFile(resizedFile, 'images/thumbnail/' + file.nom);
                    });

                    function uploadResizedFile(resizedFile, key) {
                        // pour écrire la policy voir http://docs.aws.amazon.com/fr_fr/AmazonS3/latest/API/sigv4-post-example.html
                        // pour générer la signature et la policy en base64 : https://angular-file-upload.appspot.com/

                        /*
                        la policy :

                         { "expiration": "2050-12-30T12:00:00.000Z",
                             "conditions": [
                                 {"bucket": "zigotoo-runtime"},
                                 ["starts-with", "$key", "images/"],
                                 ["starts-with", "$Content-Type", "image/"],
                                 {"acl": "public-read"},
                                 {"x-amz-credential": "AKIAIOSFODNN7EXAMPLE/20151229/us-east-1/s3/aws4_request"}
                             ]
                         }
                         */
                        Upload.upload({
                            url: 'https://zigotoo-runtime.s3.amazonaws.com/',
                            method: 'POST',
                            data: {
                                "X-amz-credential": "AKIAIOSFODNN7EXAMPLE/20151229/us-east-1/s3/aws4_request",
                                key: key,
                                acl: "public-read",
                                AWSAccessKeyId: 'AKIAJHA63IJWUWG5UB6Q', // Secret Access Key: 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 (oui la clé privé est envoyée coté client par ce commentaire)
                                policy: "eyAiZXhwaXJhdGlvbiI6ICIyMDUwLTEyLTMwVDEyOjAwOjAwLjAwMFoiLAogICJjb25kaXRpb25zIjogWwogICAgeyJidWNrZXQiOiAiemlnb3Rvby1ydW50aW1lIn0sCiAgICBbInN0YXJ0cy13aXRoIiwgIiRrZXkiLCAiaW1hZ2VzLyJdLAogICAgWyJzdGFydHMtd2l0aCIsICIkQ29udGVudC1UeXBlIiwgImltYWdlLyJdLAogICAgeyJhY2wiOiAicHVibGljLXJlYWQifSwKICAgIHsieC1hbXotY3JlZGVudGlhbCI6ICJBS0lBSU9TRk9ETk43RVhBTVBMRS8yMDE1MTIyOS91cy1lYXN0LTEvczMvYXdzNF9yZXF1ZXN0In0KICBdCn0=",
                                Signature: "JPw3t/rbQN/2P86xUpmN66ea9Gc=",
                                "Content-Type": contentType,
                                file: resizedFile
                            }
                        }).then(function() {
                            fileUploaded++;
                            if (fileUploaded == 2) {
                                file.uploaded = true;
                                Upload.imageDimensions(file).then(function(dimensions){
                                    /* On doit enregistrer les dimensions car
                                     photoswipe en a besoin à l'affichage */
                                    file.width = dimensions.width;
                                    file.height = dimensions.height;
                                    $scope.commit();
                                });
                            }
                        });
                    }
                })(file, contentType);
            }
        }
    };

    $scope.deletePhoto = function(photo) {
        var i = $scope.dirtyPhotos.indexOf(photo);
        if(i != -1) {
            $scope.dirtyPhotos.splice(i, 1);
        }
        $scope.commit();
    };

    $scope.gallerie = function(index) {
        gallerie($scope.pageAnimal.photos, index);
    };

    $scope.draggable = function(index) {
        return index <= $scope.dirtyPhotos.length;
    };
};