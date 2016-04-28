require('angular');
require('angular-xeditable-npm');
require('ng-file-upload');
require('angular-drag-and-drop-lists');

var zigotoo = angular.module('zigotoo', ['xeditable', 'ngFileUpload', 'dndLists']);

zigotoo.run(['editableOptions', 'editableThemes', require('./ZigotooRun.js')]);

zigotoo.filter('pageAnimalChipStatut', require('./PageAnimalStatutChipFilter.js'));
zigotoo.filter('pageAnimalTranslateStatut', require('./PageAnimalStatutTranslateFilter.js'));

zigotoo.controller('PageEleveurCtrl', ['$scope', '$http', require('./PageEleveurCtrl.js')]);
zigotoo.controller('PageAnimalCtrl', ['$scope', '$http', 'Upload', require('./PageAnimalCtrl.js')]);

// Directive qui sert à typer le ngModel en integer
// Voir https://docs.angularjs.org/api/ng/directive/select
zigotoo.directive('zIntegerModel', require('./ZIntegerModelDirective.js'));