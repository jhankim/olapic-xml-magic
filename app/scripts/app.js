'use strict';

/**
 * @ngdoc overview
 * @name feedvalidApp
 * @description
 * # feedvalidApp
 *
 * Main module of the application.
 */
angular
  .module('feedvalidApp', [
    'ngAnimate',
    'ngRoute'
  ])
  .config(function ($routeProvider) {
    $routeProvider
      .when('/', {
        templateUrl: 'views/main.html',
        controller: 'MainCtrl'
      })
      .when('/about', {
        templateUrl: 'views/about.html',
        controller: 'AboutCtrl'
      })
      .otherwise({
        redirectTo: '/'
      });
  });
