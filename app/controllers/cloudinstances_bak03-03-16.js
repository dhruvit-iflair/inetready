app.controller('CloudCtrl', function($scope, $http, $location, $window , $templateCache) {   

    $scope.showModal = false;
    $scope.buttonClicked = "";
    $scope.toggleModal = function(btnClicked){
        $scope.buttonClicked = btnClicked;
        $scope.showModal = !$scope.showModal;
    };   



    $http.get("php/cloud_instance_list.php")
   .then(function (response) 
    { 

        $scope.cloud_instance_names = response.data.records;
    }
    );

   
   $scope.img_dwnld = function()
   {    
    var instanceid  = $('#instance_hid').val();
    var filepath    = encodeURIComponent($('#instance_img_hid').val());
    var filename    = $('#instance_savdname_hid').val();
    var filetype    = $('#instance_filetype_hid').val();
    
    window.location="php/download.php?instanceid="+instanceid+"&filepath="+filepath+"&filename="+filename+"&filetype="+filetype;
    
    }


    $scope.img_remove = function(cinstence_id, index){

    var instanceid  = $('#instance_hid').val();
    var filepath    = encodeURIComponent($('#instance_img_hid').val());
    var filename    = $('#instance_savdname_hid').val();
    var filetype    = $('#instance_filetype_hid').val();
    var fileid      = $('#fileid_hid').val();        

    var deleteInstance = $window.confirm('Are you absolutely sure you want to delete?');
    if(deleteInstance) 
        {
        $http.get("php/imagedelete.php?instanceid="+instanceid+"&filename="+filename+"&fileid="+fileid)
        .success(function(data){
        $scope.cloud_instance_files.splice(index, 1);
        //$location.path("app/manageusers");
        $scope.errorMsg = "Deleted Successfully";
        
        })
        }
    }
    


   $scope.set_instance_img_hid = function(href_url_path,savd_file_name,file_type,fileid){

      $('#instance_img_hid').val(href_url_path); 
      $('#instance_savdname_hid').val(savd_file_name);    
      $('#instance_filetype_hid').val(file_type);
      $('#fileid_hid').val(fileid);
    } 

   $scope.listall = function(){

    document.getElementById('instencename_hid').style.visibility  = 'visible';
    document.getElementById('instencename_hid').style.position    = '';
    document.getElementById('instencefiles_hid').style.visibility = 'hidden';
    document.getElementById('search_hid_id').style.visibility     = 'hidden';

    $http.get("php/cloud_instance_list.php")
   .then(function (response) 
    {           
        $scope.cloud_instance_names = response.data.records;
    }
    );
   }

   $scope.searchclouddetails = function(){           
        
        var searchcloudinstance = $scope.searchcloudinstance;               
        
        var jsonString  = '{"searchcloudinstance":"'+searchcloudinstance+'"}';
        
        var obj         = JSON.parse(jsonString);

        $http({
            method: 'POST',
            url: 'php/searchcloudinstance.php',
            data: {
                searchcloudinstance:  obj.searchcloudinstance             
            },
            headers: { 'Content-Type': 'application/json'}
        })

        .then(function (response) 
        {           
            $scope.cloud_instance_names = response.data.records;            

        }
        );         

    }

    $scope.cld_inst_files = function(cinstence_id){
        //alert(cinstence_id);
        document.getElementById('instencename_hid').style.visibility = 'hidden';
        document.getElementById('instencename_hid').style.position = 'absolute';
        document.getElementById('instencefiles_hid').style.visibility = 'visible';
        document.getElementById('search_hid_id').style.visibility     = 'visible';
        $('#instance_hid').val(cinstence_id);
        //document.getElementById('instencefiles_hid').style.visibility = 'visible';
        
        var jsonString  = '{"cinstence_id":"'+cinstence_id+'"}';
        
        var obj         = JSON.parse(jsonString);

        $http({
            method: 'POST',
            url: 'php/cloudinstance_files.php',
            data: {
                cinstence_id:  obj.cinstence_id             
            },
            headers: { 'Content-Type': 'application/json'}
        })

        .then(function (response) 
        { 
                     
            $scope.cloud_instance_files = response.data.records;            

        }
        );  
    }   

    $scope.reload_all = function(){
      //alert('enter');
      var cinstence_id = $('#instance_hid').val();
      //alert(cinstence_id);
        //document.getElementById('instencefiles_hid').style.visibility = 'visible';
        
        var jsonString  = '{"cinstence_id":"'+cinstence_id+'"}';
        
        var obj         = JSON.parse(jsonString);

        $http({
            method: 'POST',
            url: 'php/cloudinstance_files.php',
            data: {
                cinstence_id:  obj.cinstence_id             
            },
            headers: { 'Content-Type': 'application/json'}
        })

        .then(function (response) 
        { 
                     
            $scope.cloud_instance_files = response.data.records;            

        }
        ); 
    } 

    
        
    
});

app.directive('modal', function () {
    return {
      template: '<div class="modal fade">' + 
          '<div class="modal-dialog" style="width:70%">' + 
            '<div class="modal-content">' + 
              '<div class="modal-header">' + 
                '<button type="button" class="close" data-dismiss="modal" aria-hidden="true" ng-click="reload_all()">&times;</button>' + 
                '<h4 class="modal-title">Upload Your Files!!</h4>' + 
              '</div>' + 
              '<div class="modal-body" ng-transclude></div>' + 
            '</div>' + 
          '</div>' + 
        '</div>',
      restrict: 'E',
      transclude: true,
      replace:true,
      scope:true,
      link: function postLink(scope, element, attrs) {
          scope.$watch(attrs.visible, function(value){
          if(value == true)
            $(element).modal('show');
          else
            $(element).modal('hide');
        });

        $(element).on('shown.bs.modal', function(){
          scope.$apply(function(){
            scope.$parent[attrs.visible] = true;
          });
        });

        $(element).on('hidden.bs.modal', function(){
          scope.$apply(function(){
            scope.$parent[attrs.visible] = false;
          });
        });
      }
    };
  });


app.directive('ngRightClick', function($parse) {
    return function(scope, element, attrs) {
        var fn = $parse(attrs.ngRightClick);
        element.bind('contextmenu', function(event) {
            scope.$apply(function() {
                event.preventDefault();
                fn(scope, {$event:event});
            });
        });
    };
});