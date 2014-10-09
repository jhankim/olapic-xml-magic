'use strict';

/**
 * @ngdoc function
 * @name olapicFeedVisualApp.controller:MainCtrl
 * @description
 * # MainCtrl
 * Controller of the olapicFeedVisualApp
 */
angular.module('olapicFeedVisualApp')
  .controller('MainCtrl', ['$scope', '$http' ,'$upload', function($scope, $http, $upload){

    $scope.oneAtATime = true;

    $scope.status = {
      isFirstOpen: true,
      isFirstDisabled: false
    };

    $scope.formData = {};

    $scope.curPage = 0;
    $scope.pageSize = 10;

    $scope.numberOfPages = function() {
      return Math.ceil($scope.products.length / $scope.pageSize);
    };

    function onSuccess(data){
      $scope.metadata = data.metadata;
      if ( data.metadata.code > 1 ) {
        $scope.errors = data.data;
      } else {
        // console.log(JSON.parse(data.data));
        var o = data.data
        $scope.products = Object.keys(o).map(function(k) { return o[k] });
      }
    }

    $scope.triggerUpload=function()
    {
     var fileuploader = angular.element('#input-feed-file');
        fileuploader.on('click',function(){
            console.log('File upload triggered programatically');
        });
        fileuploader.trigger('click');
    };

    $scope.urlSubmit = function() {

      $scope.errors = $scope.products = '';

      var requestUrl;

      if (!this.feedUrl) {
        return false;
      } else {
        requestUrl = 'http://localhost:5000/validator/validate.php?url=' + encodeURIComponent(this.feedUrl);
      }

      if ( this.auth ) {
        console.log('auth checked');
        requestUrl = 'http://localhost:5000/validator/validate.php?url=' + encodeURIComponent(this.feedUrl) + '&username=' + this.username + '&password=' + this.password;
      }

      $http.get(requestUrl).
      success(function(data) {
        onSuccess(data);
      }).
      error(function(data) {
        console.log(data);
      });
    };

    $scope.onFileSelect = function($files) {

      $scope.errors = '';

      for (var i = 0; i < $files.length; i++) {
        var file = $files[i];
        $scope.upload = $upload.upload({
          url: 'http://localhost:5000/validator/validate.php',
          method: 'POST',
          file: file
        }).progress(function(evt) {
          console.log('percent: ' + parseInt(100.0 * evt.loaded / evt.total));
        }).success(function(data) {
          onSuccess(data);
        });
        //.error(...)
        //.then(success, error, progress); 
        // access or attach event listeners to the underlying XMLHttpRequest.
        //.xhr(function(xhr){xhr.upload.addEventListener(...)})
      }
    };

  }]);