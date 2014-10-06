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

    $scope.triggerUpload=function()
    {
     var fileuploader = angular.element("#input-feed-file");
        fileuploader.on('click',function(){
            console.log("File upload triggered programatically");
        })
        fileuploader.trigger('click')
    }

    $scope.urlSubmit = function() {

      $scope.errors, $scope.products = '';

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
      success(function(data, status, headers, config) {
        $scope.metadata = data.metadata;
        if ( data.metadata.code > 1 ) {
          $scope.errors = data.data;
        } else {
          $scope.products = data.data;
        }
        
      }).
      error(function(data, status, headers, config) {
        // log error
      });
    }

    $scope.onFileSelect = function($files) {

      $scope.errors = '';

      //$files: an array of files selected, each file has name, size, and type.
      for (var i = 0; i < $files.length; i++) {
        var file = $files[i];
        $scope.upload = $upload.upload({
          url: 'http://localhost:5000/validator/validate.php', //upload.php script, node.js route, or servlet url
          method: 'POST',
          //headers: {'header-key': 'header-value'},
          //withCredentials: true,
          // data: {myObj: $scope.myModelObj},
          file: file, // or list of files ($files) for html5 only
          //fileName: 'doc.jpg' or ['1.jpg', '2.jpg', ...] // to modify the name of the file(s)
          // customize file formData name ('Content-Disposition'), server side file variable name. 
          //fileFormDataName: myFile, //or a list of names for multiple files (html5). Default is 'file' 
          // customize how data is added to formData. See #40#issuecomment-28612000 for sample code
          //formDataAppender: function(formData, key, val){}
        }).progress(function(evt) {
          console.log('percent: ' + parseInt(100.0 * evt.loaded / evt.total));
        }).success(function(data, status, headers, config) {
          // file is uploaded successfully
          $scope.metadata = data.metadata;
          $scope.products = data.data;
          console.log($scope.products);
        });
        //.error(...)
        //.then(success, error, progress); 
        // access or attach event listeners to the underlying XMLHttpRequest.
        //.xhr(function(xhr){xhr.upload.addEventListener(...)})
      }
      /* alternative way of uploading, send the file binary with the file's content-type.
         Could be used to upload files to CouchDB, imgur, etc... html5 FileReader is needed. 
         It could also be used to monitor the progress of a normal http post/put request with large data*/
      // $scope.upload = $upload.http({...})  see 88#issuecomment-31366487 for sample code.
    };

  }]);