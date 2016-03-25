jQuery(function($){
    
    function get_time_and_show_it(){
        
        $.ajax({
              type: "POST"
            , dataType: "json"
            , url: MyAjax.ajaxurl
            , data: {
                action: "get_end_of_time"
            },
            success: function(time){
        
                if( time.now >= time.end )
                {
                    //The countdown is finished
                    stop_countdown()
                }    
                else
                {
                     $('#flipcountdownbox1').flipcountdown({	
                            size:'md',
                            tick:function(){
                                    var nol = function(h){
                                            return h>9?h:'0'+h;
                                    }
                                    var	range  	= 	time.end-Math.round((new Date()).getTime()/1000),
                                            secday	= 	86400, sechour = 3600,
                                            days	= 	parseInt(range/secday),
                                            hours	= 	parseInt((range%secday)/sechour),
                                            min	= 	parseInt(((range%secday)%sechour)/60),
                                            sec	= 	((range%secday)%sechour)%60;
                                    return nol(days)+' '+nol(hours)+' '+nol(min)+' '+nol(sec);
                            }
                     });
                    
                    
                }
                
                
            }
        })
        
    }
    
    function stop_countdown(){
        
    }
    
    
    $(document).ready(function(){
        
        get_time_and_show_it()
        
    })
})

