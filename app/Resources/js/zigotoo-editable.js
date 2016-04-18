require('angular');
require('angular-xeditable-npm');

var zigotoo = angular.module('zigotoo', ['xeditable']);

zigotoo.run(['editableOptions', 'editableThemes', require('./ZigotooRun.js')]);

zigotoo.controller('PageEleveurCtrl', ['$scope', '$http', require('./PageEleveurCtrl.js')]);
zigotoo.controller('PageAnimalCtrl', ['$scope', '$http', require('./PageAnimalCtrl.js')]);
