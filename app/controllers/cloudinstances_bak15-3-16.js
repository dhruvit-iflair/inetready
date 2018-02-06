app.controller('CloudCtrl', function($scope, $http, $location, $window , $templateCache) {   

    $scope.showModal = false;
    $scope.buttonClicked = "";
    $scope.toggleModal = function(btnClicked){
        $scope.buttonClicked = btnClicked;
        $scope.showModal = !$scope.showModal;
    };  

    $scope.showModal1 = false;
    $scope.buttonClicked = "";
    $scope.toggleModal1 = function(btnClicked){

        var instanceid  = $('#instance_hid').val();
        var filename    = $('#instance_savdname_hid').val();
        var newURL      = window.location.protocol + "//" + window.location.host + window.location.pathname+ "php/uploads/instances/"+instanceid+"/"+filename;
        $('#url').val(newURL);
        //alert(newURL);
        //$('#modal-title-id').html = 'Copy Link';
        //document.getElementById('modal-title-id').innerHTML = "Copy This Link";
        $scope.buttonClicked = btnClicked;
        $scope.showModal1 = !$scope.showModal1;
    };  

    $scope.toggleModal11 = function(btnClicked,filename){
        //alert(filename);
        //document.getElementById('modal-title-id').innerHTML = "Copy "+filename;
        var instanceid  = $('#instance_hid').val();
        //var filename    = $('#instance_savdname_hid').val();
        var newURL      = window.location.protocol + "//" + window.location.host + window.location.pathname+ "php/uploads/instances/"+instanceid+"/"+filename;
        $('#url').val(newURL);
        //alert(newURL);
        //$('#modal-title-id').html = 'Copy Link';
        //document.getElementById('modal-title-id').innerHTML = "Copy This Link";
        $scope.buttonClicked = btnClicked;
        $scope.showModal1 = !$scope.showModal1;
    }; 

    $scope.showModal2 = false;
    $scope.buttonClicked = "";
    $scope.toggleModal2 = function(btnClicked){
        // initial image index
        $scope._Index = 0;

        var instanceid  = $('#instance_hid').val();
        var filename    = $('#instance_img_hid').val();
        var fileid      = $('#fileid_hid').val(); 
        var orgnl_file_name  = $('#orgnl_file_name_hid').val(); 
        //alert(fileid); 
        //document.getElementById('file_name_span').innerHTML = orgnl_file_name;    
        //document.getElementById('modal-title-id').innerHTML = orgnl_file_name;
        $scope.buttonClicked = btnClicked;
        $scope.showModal2 = !$scope.showModal2;

        // Set of Photos
        var jsonString  = '{"fileid":"'+fileid+'"}';

        var obj         = JSON.parse(jsonString);
        $http({
            method: 'POST',
            url: 'php/cloud_file_list.php',  
            data: {
                        fileid:  obj.fileid                                           
                    },          
            headers: { 'Content-Type': 'application/json'}
        })

        .then(function (response) 
        { 
                     
            $scope.photos = response.data.records;            

        }
        ); 

    }; 

   $scope.showModal3 = false;
    $scope.buttonClicked = "";
    $scope.toggleModal3 = function(btnClicked,fileid){
        // initial image index
        $scope._Index = 0;

        //var instanceid  = $('#instance_hid').val();
        //var filename    = $('#instance_img_hid').val();
        //var fileid      = $('#fileid_hid').val(); 
        //var orgnl_file_name  = $('#orgnl_file_name_hid').val(); 
        //alert(fileid); 
        //document.getElementById('file_name_span').innerHTML = orgnl_file_name;    
        //document.getElementById('modal-title-id').innerHTML = orgnl_file_name;
        $scope.buttonClicked = btnClicked;
        $scope.showModal3 = !$scope.showModal3;

        // Set of Photos
        var jsonString  = '{"fileid":"'+fileid+'"}';

        var obj         = JSON.parse(jsonString);
        $http({
            method: 'POST',
            url: 'php/cloud_file_list.php',  
            data: {
                        fileid:  obj.fileid                                           
                    },          
            headers: { 'Content-Type': 'application/json'}
        })

        .then(function (response) 
        { 
                     
            $scope.photos = response.data.records;            

        }
        ); 

    }; 

   

    // if a current image is the same as requested image
    $scope.isActive = function (index) {
        return $scope._Index === index;
        //$location.path("/app/cloudinstances");
        //var newURL1      = window.location.protocol + "//" + window.location.host + window.location.pathname+ "#/app/cloudinstances";       
        //window.location = newURL1;

    };

    // show prev image
    $scope.showPrev = function () {
        var v = document.getElementById('videoID');
        v.pause();
        var w = document.getElementById('videoID1');
        w.pause();
        document.getElementById('audio_pause_id').pause();
        document.getElementById('audio_pause_id2').pause();
        $scope._Index = ($scope._Index > 0) ? --$scope._Index : $scope.photos.length - 1;
        //alert(file_id);
        //$location.path("/app/cloudinstances");
        //var newURL1      = window.location.protocol + "//" + window.location.host + window.location.pathname+ "#/app/cloudinstances";
        //alert(newURL);
        //window.location = newURL1;
    };

    // show next image
    $scope.showNext = function () {
        var v = document.getElementById('videoID');
        v.pause();
        var w = document.getElementById('videoID1');
        w.pause();
        document.getElementById('audio_pause_id').pause();
        document.getElementById('audio_pause_id2').pause();
        
        $scope._Index = ($scope._Index < $scope.photos.length - 1) ? ++$scope._Index : 0;
        //alert(file_id);
        //$location.path("/app/cloudinstances");
        //var newURL1      = window.location.protocol + "//" + window.location.host + window.location.pathname+ "#/app/cloudinstances";
        //alert(newURL);
        //window.location = newURL1;
    };

    // show a certain image
    $scope.showPhoto = function (index) {
        $scope._Index = index;
        //$location.path("/app/cloudinstances");
       //var newURL1      = window.location.protocol + "//" + window.location.host + window.location.pathname+ "#/app/cloudinstances";
        //alert(newURL);
        //window.location = newURL1;
    };



    $http.get("php/cloud_instance_list.php")
   .then(function (response) 
    { 

        $scope.cloud_instance_names = response.data.records;
    }
    );

   $scope.sel_img_dwnld = function(filename,org_name)
   { 
    //alert(filename)   ;
    var instanceid  = $('#instance_hid').val();
    var filepath    = encodeURIComponent($('#instance_img_hid').val());
    //var filename    = $('#instance_savdname_hid').val();
    var filetype    = $('#instance_filetype_hid').val();
    
    window.location="php/download.php?instanceid="+instanceid+"&filepath="+filepath+"&filename="+filename+"&filetype="+filetype+"&org_name="+org_name;
    
    }

   
   $scope.img_dwnld = function()
   {    
    var instanceid  = $('#instance_hid').val();
    var filepath    = encodeURIComponent($('#instance_img_hid').val());
    var filename    = $('#instance_savdname_hid').val();
    var filetype    = $('#instance_filetype_hid').val();
    var org_name    = $('#orgnl_file_name_hid').val();
    
    window.location="php/download.php?instanceid="+instanceid+"&filepath="+filepath+"&filename="+filename+"&filetype="+filetype+"&org_name="+org_name;
    
    }


    $scope.img_remove = function(cinstence_id, index){

    var instanceid  = $('#instance_hid').val();
    
    var filename    = $('#instance_savdname_hid').val();
    
    var fileid      = $('#fileid_hid').val();   
    //alert(fileid);     

    var deleteInstance = $window.confirm('Are you absolutely sure you want to delete?');
    if(deleteInstance) 
        {
        $http.get("php/imagedelete.php?instanceid="+instanceid+"&filename="+filename+"&fileid="+fileid)
        .success(function(data){
        $scope.cloud_instance_files.splice(index, 1);
        //$location.path("app/manageusers");
        $scope.errorMsg = "Deleted Successfully";
        ////////////////////////list all remaining files
        var jsonString  = '{"cinstence_id":"'+instanceid+'"}';
        
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
        ////////////////////////
        })
        }
    }
    


   $scope.set_instance_img_hid = function(href_url_path,savd_file_name,file_type,fileid,orgnl_file_name){

      $('#instance_img_hid').val(href_url_path); 
      $('#instance_savdname_hid').val(savd_file_name);    
      $('#instance_filetype_hid').val(file_type);
      $('#fileid_hid').val(fileid);
      $('#orgnl_file_name_hid').val(orgnl_file_name);
    } 

   $scope.listall = function(){
    $('#instance_hid').val('');
    $('#sort_hid').val(0);

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
   $scope.sortlistall = function(){
    //$('#instance_hid').val('');
    //var sort_hid = $('#sort_hid').val('');
    var sort_hid = document.getElementById('sort_hid').value;
    //alert(sort_hid);
    if(sort_hid==1)
    {
        document.getElementById('instencename_hid').style.visibility  = 'visible';
        document.getElementById('instencename_hid').style.position    = '';
        document.getElementById('instencefiles_hid').style.visibility = 'hidden';
        document.getElementById('search_hid_id').style.visibility     = 'hidden';

        $http.get("php/cloud_instance_sortlist_asc.php")
       .then(function (response) 
        {           
            $scope.cloud_instance_names = response.data.records;
            $('#sort_hid').val(0);
        }
        ); 
    }
    if(sort_hid==3)
    {
        document.getElementById('instencename_hid').style.visibility  = 'hidden';
        document.getElementById('instencename_hid').style.position    = 'absolute';
        document.getElementById('instencefiles_hid').style.visibility = 'visible';
        document.getElementById('search_hid_id').style.visibility     = 'visible';
        //$('#instance_hid').val(cinstence_id);  
        var cinstence_id        = $('#instance_hid').val();       
        
        var jsonString  = '{"cinstence_id":"'+cinstence_id+'"}';
        
        var obj         = JSON.parse(jsonString);

        $http({
            method: 'POST',
            url: 'php/cloudinstance_files_desc.php',
            data: {
                cinstence_id:  obj.cinstence_id             
            },
            
            headers: { 'Content-Type': 'application/json'}
        })

        .then(function (response) 
        { 
                     
            $scope.cloud_instance_files = response.data.records;  
            $('#sort_hid').val(4);          

        }
        );  
    }
    if(sort_hid==4)
    {
        document.getElementById('instencename_hid').style.visibility  = 'hidden';
        document.getElementById('instencename_hid').style.position    = 'absolute';
        document.getElementById('instencefiles_hid').style.visibility = 'visible';
        document.getElementById('search_hid_id').style.visibility     = 'visible';
        //$('#instance_hid').val(cinstence_id);   
        var cinstence_id        = $('#instance_hid').val();      
        
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
            $('#sort_hid').val(3);
        }
        );  
    }
    if(sort_hid=="" || sort_hid==0)
    {
        document.getElementById('instencename_hid').style.visibility  = 'visible';
        document.getElementById('instencename_hid').style.position    = '';
        document.getElementById('instencefiles_hid').style.visibility = 'hidden';
        document.getElementById('search_hid_id').style.visibility     = 'hidden';

        $http.get("php/cloud_instance_sortlist_desc.php")
       .then(function (response) 
        {           
            $scope.cloud_instance_names = response.data.records;
            $('#sort_hid').val(1);
        }
        ); 
    }
    
   }

   $scope.searchclouddetails = function(){  
        var cinstence_id        = $('#instance_hid').val(); 
        var searchcloudinstance = $scope.searchcloudinstance;  
        if(cinstence_id) 
        {                   
            document.getElementById('instencename_hid').style.visibility    = 'hidden';
            document.getElementById('instencename_hid').style.position      = 'absolute';
            document.getElementById('instencefiles_hid').style.visibility   = 'visible';
            document.getElementById('search_hid_id').style.visibility       = 'visible';
            $('#instance_hid').val(cinstence_id);        
            
             var jsonString  = '{"searchcloudinstance":"'+searchcloudinstance+'","cinstence_id":"'+cinstence_id+'"}';
            
            var obj         = JSON.parse(jsonString);

            $http({
                method: 'POST',
                url: 'php/searchcloudfile.php',
                data: {
                    cinstence_id:  obj.cinstence_id ,
                    searchcloudinstance:  obj.searchcloudinstance             
                },
                
                headers: { 'Content-Type': 'application/json'}
            })

            .then(function (response) 
            { 
                         
                $scope.cloud_instance_files = response.data.records;            

            }
            );  
        }   
        else
        {
            ////////////////////////////////////instance search list                     
            
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
            /////////////////////////////////////
        }     
        
    }

    $scope.cld_inst_files = function(cinstence_id,instence_name){
         $('#sort_hid').val(3); 
        
        document.getElementById('title_head').innerHTML = "<ul id='title_head' class='breadcrumb ng-scope ng-isolate-scope' ncy-breadcrumb='' style='margin: 0 0'><li><i class='fa fa-home'></i><a href='#'>Home</a></li><li class='ng-scope active' ng-switch='$last || !!step.abstract' ng-class='{active: $last}' ng-repeat='step in steps'><span class='ng-binding ng-scope' ng-switch-when='true'>Cloud Instances</span></li><li>"+instence_name+"</li></ul>";
        //var title_head = document.getElementById('title_head').value;
        //alert(title_head);
        document.getElementById('instencename_hid').style.visibility  = 'hidden';
        document.getElementById('instencename_hid').style.position    = 'absolute';
        document.getElementById('instencefiles_hid').style.visibility = 'visible';
        document.getElementById('search_hid_id').style.visibility     = 'visible';
        $('#instance_hid').val(cinstence_id);        
        
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
      var v = document.getElementById('videoID');
      v.pause();
      var w = document.getElementById('videoID1');
      w.pause();
      document.getElementById('audio_pause_id').pause();
      document.getElementById('audio_pause_id2').pause();
      $('#img_doc').remove();
      $('#img_docx').remove();
      $('#img_odt').remove();
      $('#img_mp3').remove();
      $('#img_zip').remove();
      $('#img_rar').remove();
      $('#img_jpg').remove();
      $('#img_pdf').remove();
      $('#img_txt').remove();
      $('#img_doc_label').html('');
      $('#img_docx_label').html('');
      $('#img_odt_label').html('');
      $('#img_pdf_label').html('');
      $('#img_txt_label').html('');
      $('#img_mp3_label').html('');
      $('#img_mp4_label').html('');
      $('#img_zip_label').html('');
      $('#img_rar_label').html('');
      $('#img_jpg_label').html('');

      $('#img_doc1').remove();
      $('#img_docx1').remove();
      $('#img_odt1').remove();
      $('#img_mp31').remove();
      $('#img_zip1').remove();
      $('#img_rar1').remove();
      $('#img_jpg1').remove();
      $('#img_pdf1').remove();
      $('#img_txt1').remove();
      $('#img_doc_label1').html('');
      $('#img_docx_label1').html('');
      $('#img_odt_label1').html('');
      $('#img_pdf_label1').html('');
      $('#img_txt_label1').html('');
      $('#img_mp3_label1').html('');
      $('#img_mp4_label1').html('');
      $('#img_zip_label1').html('');
      $('#img_rar_label1').html('');
      $('#img_jpg_label1').html('');
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
          '<div class="modal-dialog" style="width:100%;height:100vh;vertical-align=middle">' + 
            '<div class="modal-content" style="height:100%;vertical-align=middle">' + 
              '<div class="modal-header">' + 
                '<button type="button" class="close" data-dismiss="modal" aria-hidden="true" ng-click="reload_all()">&times;</button>' + 
                '<h4 class="modal-title" id="modal-title-id">&nbsp;</h4>' + 
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


app.directive('html5FallbackVideo', function () {
    return {
        restrict: 'A', //this means the direct must be declared as an attribute on an element
        replace: false, //don't replace the surrounding element with the template code
        link: function (scope, element, attrs) { // manipulate the DOM in here
            if (!Modernizr.video) { //if html5 video is not supported start flowplayer
                //alert('flash video');
                flowplayer("flash-player", "flowplayer-3.2.16.swf", {
                    clip: {
                        url: scope.mp4Url,
                        autoPlay: false,
                        autoBuffering: true,
                        scaling: "fit"
                    },
                    canvas: {
                        backgroundColor: "#000000",
                        backgroundGradient: "none"
                    }
                });
            }
        },
        scope: {
            webmUrl: '@', //binds property value to the element's attribute
            mp4Url: '@',
            videoWidth: '@',
            videoHeight: '@',
            splashImage: '@'
        },
        templateUrl: 'views/html5-fallback-video.html' //contains the video code
    }
});

app.directive('html5FallbackVideo1', function () {
    return {
        restrict: 'A', //this means the direct must be declared as an attribute on an element
        replace: false, //don't replace the surrounding element with the template code
        link: function (scope, element, attrs) { // manipulate the DOM in here
            if (!Modernizr.video) { //if html5 video is not supported start flowplayer
                //alert('flash video');
                flowplayer("flash-player", "flowplayer-3.2.16.swf", {
                    clip: {
                        url: scope.mp4Url,
                        autoPlay: false,
                        autoBuffering: true,
                        scaling: "fit"
                    },
                    canvas: {
                        backgroundColor: "#000000",
                        backgroundGradient: "none"
                    }
                });
            }
        },
        scope: {
            webmUrl: '@', //binds property value to the element's attribute
            mp4Url: '@',
            videoWidth: '@',
            videoHeight: '@',
            splashImage: '@'
        },
        templateUrl: 'views/html5-fallback-video1.html' //contains the video code
    }
});