jQuery(function($){
   
    
    $(document).ready(function(){
    
        data_countries = {
            
                "GH" : "1", //Ghana
                "ID" : "1", //Indonesia
                "KE" : "1", //Kenya
                "NE" : "1", //Nigeria
                "ZA" : "1", //South Africa
                "UG" : "1",  //Uganda
                "TZ" : "1",  //Tanzania
                "LR" : "1",  //Liberia
                
        }
        
        map = new jvm.WorldMap({
                    container:  $('#world-map')
                    , map:        'world_mill_en'
                    , enablePan: false
                    , draggable: false
                    , zoomOnScroll: false
                    , regionsSelectable: false
                    , focusOn: {
                      x: 0.64,
                      y: 1,
                      scale: 2.2
                    }
                    , backgroundColor: 'white'
                    , regionStyle: {
                          initial: {
                            fill: '#b2b2b2',
                            "fill-opacity": 1,
                            stroke: 'none',
                            "stroke-width": 0,
                            "stroke-opacity": 1,
                          },
                          hover: {
                            fill: "#CD1719",
                            "fill-opacity": 0.6
                          },
                          selected: {
                              fill: "#cd1719"
                          },
                          selectedHover: {
                          }
                        },
                    series: {
                        regions: [{
                          scale: {
                            '1': '#333333', //Base - Countries with projects
                            '2': '#333333', //Active - Countries with projects
                          },
                          attribute: 'fill',
                          values: data_countries
                        }]
                    },
                    onRegionClick: function(event, code){
                        
                        if( !$("#where_main_container").hasClass("animation_running") )
                        {
                            
                            if( data_countries[code] > 0 )
                            {
                                map.clearSelectedRegions()
                                map.setSelectedRegions(code)
                                show_data(code)

                            }
                            
                        }
                        
                    }
                })
        $('.jvectormap-zoomin').remove()
        $('.jvectormap-zoomout').remove()
               
        $(".data_panel li").hover(
                function(){
                      $(this).find(".dial").trigger(
                            'configure',
                            {
                                "fgColor":"#CD1719",
                                "inputColor": "#CD1719"
                            }
                        );
                },
                function(){
                      $(this).find(".dial").trigger(
                            'configure',
                            {
                                "fgColor":"#333333",
                                "inputColor": "#333333"
                            }
                        );
                }
        )
        
        show_data( "nocode" )
        map.setSelectedRegions(data_countries)
        
        $(".nocode").click(function(){
            
            show_data( "nocode" )
            map.setSelectedRegions(data_countries)
        })
        
        
        
    })
  
    
$(document).on("click", "#multi_country", function(){

    show_data("MULTI")

})
    
function show_data(code){

    $("#where_main_container").addClass("animation_running")
            //alert(code)
    
    $.ajax({
        type: "POST"
        , dataType: "json"
        , url: MyAjax.ajaxurl
        , data: {
            action: "get_projects_data"
          , code: code
        },
        success: function(chartdata){
            
            
            circle_chart( parseInt(chartdata.total) )
            bar_chart( parseInt(chartdata.peoples), parseInt(chartdata.amount), parseInt(chartdata.partner) )
            //pie_chart( parseInt(chartdata.peoples), parseInt(chartdata.amount), parseInt(chartdata.partner) )

            $(".data_panel").find("h2").html( chartdata.title )
            //$(".data_panel .country_description").html( chartdata.description )
            

        }
    })

}
    
    
/**
 * Get data for CIRCLE chart and create CIRCLE element
 * 
 * @param {type} code
 * @returns {undefined}
 */    
function circle_chart( cir_total ){
    
    
    //Revome the current knob. 
    $(".dial_container .dial, .dial_container canvas").remove()
    
    //Append the input
    $(".dial_container").append("<input class='dial linear' type='text' value='0' />")
    
    //Create and initialize knob chart
    $(".dial").knob({
        
            width:      130
          , height:     130
          , max:        544
          , min:        0
          , fgColor:    "#333333"
          , inputColor: "#333333"
          , readOnly:   true
          
    });
    
    var counter_animation = 0 
    
    animation_charts = setInterval(function(){

        var value       = $(".dial.linear").val()

        if( parseInt(value) < parseInt(cir_total) )
        {
            $(".dial.linear").val( counter_animation ).trigger('change');
        }
        else
        {
            clearInterval(animation_charts)
            $("#where_main_container").removeClass("animation_running")
        }

        counter_animation++

    }, 1);
    
}

/**
 * Get data for BAR chart and create BAR element
 * 
 * @param {type} code
 * @returns {undefined}
 */
function bar_chart( bar_people, bar_amount, bar_partners ){
    
    var bar_chart = new Highcharts.Chart({
        chart: {
              renderTo:   "bar_chart"   //#bar_chart div
            , type:       "bar"
            , marginLeft: 50
            , marginTop: 30
        }
        , tooltip: {
            enabled: false
        }
        , lineColor: "#321321"
        , title: {
              text: ' TYPE OF GRANT ///'
            , align: "left"
            , style: {
                      fontSize: 16
                    , color: "#333"
                    , fontWeight: "bold"
                    , fontFamily: "Helvetica Neue,Helvetica,Arial,sans-serif"
                }
            }
        , credits: {
            position: {
                align: 'left',
                verticalAlign: 'bottom',
                x: -10010,
                y: -10
            }
        }
        , xAxis: {
              title: {
                     text:  ""
              }
            , labels: {
                  rotation: -45
                , x: -5
            } 
            , categories: ["Research", "Innovation", "Scaling"]
            
        }
        , yAxis: {
              title: {
                  "text": ""
              }
            , type: 'linear'
        }
        , legend: false
        , series: [
            {
              name: "Title of data"
            , data: [
                {
                    y: bar_people
                    , color: '#CD1719'
                },
                {
                    y: bar_amount
                    , color: '#333333'
                },
                {
                    y: bar_partners
                    , color: '#9D9D9C'
                }
            ]
            }
        ]

   })
    
}
});
