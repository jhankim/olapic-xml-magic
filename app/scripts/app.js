'use strict';

/**
 * @ngdoc overview
 * @name olapicFeedVisualApp
 * @description
 * # olapicFeedVisualApp
 *
 * Main module of the application.
 */
angular
  .module('olapicFeedVisualApp', [
    'ngAnimate',
    'ngResource',
    'ngRoute',
    'ngSanitize',
    'ngTouch',
    'ui.bootstrap',
    'angularFileUpload',
    'angular-loading-bar'
  ])
  .config(function ($routeProvider) {
    $routeProvider
      .when('/', {
        templateUrl: 'views/main.html',
        controller: 'MainCtrl'
      })
      .otherwise({
        redirectTo: '/'
      });
  }).filter('pagination', function(){
    return function(input, start){
      if (!input || !input.length) { console.log('input null');return; }
      start = +start;
      return input.slice(start);
    };
  });