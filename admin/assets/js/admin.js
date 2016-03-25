(function ( $ ) {
	"use strict";

        var map     = ""
        
	$(function () {

            if( $('#country_connection_map').length > 0 )
            {
                
                var post_id = $("#country_connection_container").attr("post_id")
            
                var data_initial_countries = {
                    action:     "get_initial_countries",
                    post_id:    post_id
                }

                $.post(ajaxurl, data_initial_countries, function(response) {

                    var res = response.split(",");

                    var data_countries = {}
                    var countries_array = new Array()

                    for (var index = 0; index < res.length; ++index) {

                        //data_countries[ res[index] ] = 1;
                        countries_array.push( res[index] )
                    }



                    // Place your administration-specific JavaScript here
                    map = new jvm.WorldMap({
                        container:  $('#country_connection_map')
                        , map:        'world_mill_en'
                        , enablePan: false
                        , draggable: false
                        , zoomOnScroll: false
                        , regionsSelectable: true
                        , focusOn: {
                          x: 0.64,
                          y: 0.6,
                          scale: 2
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
                                '1': '#b2b2b2', //Base - Countries with projects
                                '2': '#333333', //Active - Countries with projects
                              },
                              attribute: 'fill',
                              values: data_countries
                            }]
                        },
                        /*
                        //onRegionLabelShow: function(e, el, code) {
                        onRegionLabelShow: function(e, label_dom, code) {

                            label_dom.html(label_dom.html()+' (GDP - '+[code]+')');

                        },
                        */
                        onRegionClick: function(event, code){

                            show_data(code)

                        }
                    })
                    
                    map.setSelectedRegions( countries_array )

                    for (var index = 0; index < countries_array.length; ++index) {

                        $("#current_countries_connected ul").append("<li>" + map.getRegionName( countries_array[index] ) + "</li>")

                    }

                    var mylist = $('#current_countries_connected ul');
                    var listitems = mylist.children('li').get();
                    listitems.sort(function(a, b) {
                       var compA = $(a).text().toUpperCase();
                       var compB = $(b).text().toUpperCase();
                       return (compA < compB) ? -1 : (compA > compB) ? 1 : 0;
                    })
                    $.each(listitems, function(idx, itm) { 
                        mylist.append(itm); 
                    });





                });

                
                
            }

            

            
            
            
            function show_data( code ){
                
                $("#current_countries_connected ul").html("")
                
                var current_connected = map.getSelectedRegions()
                
                if( current_connected.indexOf( code ) < 0)
                {
                    
                    current_connected.push(code)
                }
                else
                {
                    
                    current_connected.splice( current_connected.indexOf( code ) , 1)
                    
                }
                    current_connected.sort()
                    
                for (var index = 0; index < current_connected.length; ++index) {
                    
                    $("#current_countries_connected ul").append("<li>" + map.getRegionName( current_connected[index] ) + "</li>")
                    
                }
                
                var mylist = $('#current_countries_connected ul');
                var listitems = mylist.children('li').get();
                listitems.sort(function(a, b) {
                   var compA = $(a).text().toUpperCase();
                   var compB = $(b).text().toUpperCase();
                   return (compA < compB) ? -1 : (compA > compB) ? 1 : 0;
                })
                $.each(listitems, function(idx, itm) { 
                    mylist.append(itm); 
                });
                
                
                
                //Update post_meta
                var data = {
                    action:     "update_wbb_country_connection_post_meta",
                    countries:  current_connected,
                    post_id:    post_id
                }
                
                $.post(ajaxurl, data, function(response) {
                
                    if(response)
                    {
                        
                    }
                    else
                    {
                        alert("error")
                    }
                    
                });
                
            }
            
	});

}(jQuery));