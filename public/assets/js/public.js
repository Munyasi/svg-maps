jQuery(document).ready(function () {
    var map;
    var data_countries = {}

    var data_initial_countries = {
        action: "get_initial_countries_front_end"
    };


   /* var colorschemes=['91,7,51, 1','173,209,55, 1','7,33,45, 1','0,129,139, 1','121,170,197, 1','244,32,32, 1','123, 0, 0, 1','23,174,89, 1','161, 105, 105, 1','13, 116, 140, 1','98, 0, 131, 1','158,118,210, 1','255, 0, 255, 1','250,232,130, 1','4, 0, 18, 1','4, 28, 18, 1','4, 255, 255, 1','102, 70, 96, 1','246, 0, 12, 1','255, 129, 91, 1'];*/
    var colorschemes=['4,50,39, 1','9,113,104, 1','255,204,136, 1','250,72,46, 1','244,163,46, 1','165,0,38, 1','217,239,139, 1','26,152,80, 1','142,1,82, 1','241,182,218, 1','122,1,119, 1','191,129,45, 1','239,140,0, 1','77,79,78, 1','95,123,211, 1','4, 28, 18, 1','4, 255, 255, 1','102, 70, 96, 1','246, 0, 12, 1','255, 129, 91, 1'];
    /* var colorschemes=['127,0,0, 1','255,68,68, 1','255,178,178, 1','153,81,0, 1','255,136,0, 1','255,229,100, 1','44,76,0, 1','102,153,0, 1','210,254,76, 1','60,20,81, 1','153,51,204, 1','188,147,209, 1','188,147,209, 1','0,153,204, 1','142,213,240, 1','102,0,51, 1','229,0,114, 1','255,127,191, 1','246, 0, 12, 1','255, 129, 91, 1'];*/

    var allData= new Array();
    //getCategories();
    function getCategories(){

        var data_initial_categories = {
            action: "get_initial_categories"
        }
        jQuery.ajax({
            type:'POST',
            url: MyAjax.ajaxurl,
            data:data_initial_categories,
            success:function(res){
               console.log(res);
            }
        });

    }
    //getCountries();
   

    getInitialData();

    function getInitialData(){
         var data_initial={
            action: "get_initial_data"
        }
       
          jQuery.ajax({
            type:'POST',
            dataType:'JSON',
            url: MyAjax.ajaxurl,
            data:data_initial,
            success:function(res){
               var country_title = new Array();
               res.forEach(function (i){
                    countries = i.countries.split(",");
                    countries.forEach(function (j){
                        country_title.push({"title":(i.title),"country":j.trim(),"link":i.link,"post_id":i.post_id,"categories":i.categories});

                    });
               });

                var countries_list = new Array();
                var markers= new Array();
                var title_list= new Array();
                count=-1;;
                p_color="";
                country_title.forEach(function (i) {
                    code = getCountryCode(i.country);
                    data_countries[code] = 1;
                    param=contains(countries_list,i.country);
                    //console.log(title_list);
                    param2=contains_arry(title_list,i.post_id);

                    if(!param2){
                        count++;
                        p_color=colorschemes[count];
                        title_list.push({"post_id":i.post_id,"title":i.title,"color":p_color});
                    }
                    countries_list.push(i.country);
                    
                    if (!param) {
                       markers.push({"latLng":getlatLng(code),"name":i.title,"post_id":i.post_id,"link":i.link,"categories":i.categories,style:{fill:'rgba('+colorschemes[count]+')'}}); 
                           if(typeof getlatLng(code)==='undefined' ){
                                console.log(code);return;
                            }
                   }else{

                       ll=getlatLng(code);
                       markers.push({"latLng":[(ll[0]*1)-2,(ll[1]*1)-2],"name":i.title,"post_id":i.post_id,"link":i.link,"categories":i.categories,style:{fill:'rgba('+colorschemes[count]+')'}});
                        //console.log(markers);
                        //return;
                   };
                   
                });
               
                getSelectedTitles(markers);

                allData=markers;
                map = new jvm.WorldMap({
                    container: jQuery('#main_project_map'),
                    map: 'world_mill_en',
                    enablePan: false,
                    draggable: false,
                    zoomOnScroll: false,
                    regionsSelectable: false,
                    focusOn: {
                        x: 0.5,
                        y: 0.5,
                        scale: 1
                    },
                    backgroundColor: 'white',
                    regionStyle: {
                        initial: {
                            fill: '#b2b2b2',
                            "fill-opacity": 1,
                            stroke: 'none',
                            "stroke-width": 0,
                            "stroke-opacity": 1
                        },
                        hover: {
                            fill: "#F48020",
                            "fill-opacity": 0.6
                        },
                        selected: {
                            fill: "#333333"
                        },
                        selectedHover: {
                        }
                    },
                    series: {
                        regions: [
                            {
                                scale: {
                                    '1': '#F48020', //Base - Countries with projects
                                    '2': '#333333' //Active - Countries with projects
                                },
                                attribute: 'fill',
                                values: data_countries
                            }
                        ]
                    },
                    markerStyle: {
                      initial: {
                        fill: '#F8E23B',
                        stroke: '#383f47'
                      }
                    },
                    backgroundColor: '#F1F7DC',
                    markers: markers,
                    onMarkerClick: function (event,code){
                        //console.log(markers[code].categories);
                        window.location.assign(markers[code].link);
                    },
                    onMarkerOver: function(event,code){
                        $(this).css('cursor','pointer');
                    },
                    onRegionClick: function (event, code) {
                        //alert('danger');
                        if (data_countries[code] > 0) {

                            if (jQuery('#project_map').hasClass("active")) {
                                jQuery('#project_map').removeClass("active")

                                var current_countries = map.getSelectedRegions()

                                if (current_countries.indexOf(code) > -1) {
                                    current_countries.splice(current_countries.indexOf(code), 1);
                                }
                                else {
                                    current_countries.push(code)

                                }
                                console.log(data_countries[code]);

                                map.clearSelectedRegions()
                                map.setSelectedRegions(current_countries)

                                jQuery("#project_countries_list li").removeClass("active")

                                current_countries.forEach(function (country_code) {

                                    jQuery("#project_countries_list li[filter='" + country_code + "']").addClass("active")

                                });

                                jQuery("#wbb_pagination li").first().addClass("active")

                                if (current_countries.length < 1)
                                    jQuery("#project_countries_list li").first().addClass("active")


                                show_data(map.getSelectedRegions())

                            }

                        }
                    }
                 });


                for (var i = 0; i <= title_list.length; i++) {
                    if (title_list[i]) {
                        jQuery("#project_title_list").append(
                            "<li class='project_title truncate ellipsis' data-filter='"
                            + title_list[i]['post_id'] + "'><i class='fa fa-circle' style='color:rgba("+title_list[i]['color']+")'></i> "+ 
                            title_list[i]['title']
                            + "</li>");

                    }

                }

                scrollOnHover();
                    //Order by a-z
                var mylist = jQuery('#project_title_list');
                var listitems = mylist.children('li').get();

                listitems.sort(function (a, b) {
                    var compA = jQuery(a).text().toUpperCase();
                    var compB = jQuery(b).text().toUpperCase();
                    return (compA < compB) ? -1 : (compA > compB) ? 1 : 0;
                });

                jQuery.each(listitems, function (idx, itm) {
                    mylist.append(itm);
                });

                jQuery('#project_title_list').prepend("<li class='project_title active all' filter='' ><i class='fa fa-circle'></i> Show All Projects </li>")
            },
            beforeSend: function(){
                jQuery('#js-loading-wheel').css('display','block');
             },
            complete: function(){
                jQuery('#js-loading-wheel').css('display','none');
                
            }
        });

    }



    function getSelectedTitles(markers){
        allData=markers;
        //console.log(markers);
        $(document).on('click','.project_title,.project_category',function(){
            map.removeAllMarkers();
            if($(this).hasClass('all')&&$(this).hasClass('project_title')&&!$(this).hasClass('active')){
                map.addMarkers(markers);
                $('.project_title').removeClass('active');
                $(this).addClass('active');
            }

            if(!$(this).hasClass('all')&&$(this).hasClass('project_title')&&!$(this).hasClass('active')){
                $('.project_title').filter('.all').removeClass('active');
                $(this).addClass('active');
            }else if(!$(this).hasClass('all')&&$(this).hasClass('project_title')&&$(this).hasClass('active')){
                    $(this).removeClass('active');
            }
      
            if($(this).hasClass('all')&&$(this).hasClass('project_category')&&!$(this).hasClass('active')){
               // map.addMarkers(markers);
                $('.project_category').removeClass('active');
                $(this).addClass('active');
            }

            if(!$(this).hasClass('all')&&$(this).hasClass('project_category')&&!$(this).hasClass('active')){
                $('.project_category').filter('.all').removeClass('active');
                $(this).addClass('active');
            }else if(!$(this).hasClass('all')&&$(this).hasClass('project_category')&&$(this).hasClass('active')){
                    $(this).removeClass('active');
            }
              
            dataList = $(".project_title.active").map(function() {
                            return $(this).data("filter");
                        }).get();
            //console.log(dataList);
            dataCategorySlug=$(".project_category.active").map(function() {
                            return $(this).data("filter");
                        }).get();

            jQuery.each(allData, function(index, value) {
                jQuery.each(dataCategorySlug, function(index2, value2) {
                    var categories = value.categories.split(',');
                    jQuery.each(categories, function(index3, value3) {
                        if (value3==value2) {
                            dataList.push(value.post_id);
                        };
                    });
                });
            });
            //console.log(dataList);
            newmarkers=new Array();
            jQuery.each(allData, function(index, value) {
                jQuery.each(dataList, function(index2, value2) {

                    if (value.post_id==value2) {
                        newmarkers.push(value);
                    };
                });
            });
                map.addMarkers(newmarkers);
        });
    }

    /*function getFilteredCategories(markers){
        $(document).on('click','.project_category',function(){

        });
    }
*/
    /**/

    function contains(a, obj) {
        for (var i = 0; i < a.length; i++) {
            if (a[i] === obj) {
                return true;
            }
        }
        return false;
    }

    function contains_arry(a,obj){
        for( var i = 0, len = a.length; i < len; i++ ) {
            if( a[i]['post_id'] == obj ) {
               return true;
            }
        }
        return false;
    }

    function getProjectCategories(){
        data_get_categories={action:'getProjectCategories'}
        return jQuery.ajax({type:'POST',dataType:'JSON', url: MyAjax.ajaxurl,data:data_get_categories });
    }


    

//FILTER BY PROJECT CATEGORIES
    /*jQuery(".project_category").click(function () {

        if (jQuery('#project_map').hasClass("active")) {

            jQuery('#project_map').removeClass("active")

            var filter = jQuery(this).attr("filter")

            if (filter !== "") {

                jQuery(".project_category").first().removeClass("active")

                if (!jQuery(this).hasClass("active")) {

                    jQuery(this).addClass("active")

                }
                else {

                    jQuery(this).removeClass("active")

                }

                if (jQuery(".project_category.active").length < 1)
                    jQuery(".project_category").first().addClass("active")

            }
            else {

                jQuery(".project_category").removeClass("active")
                jQuery(".project_category").first().addClass("active")

            }


            jQuery("#category_filter").val("");

            jQuery(".project_category.active").each(function () {

                var current_filter = jQuery("#category_filter").val();

                if (jQuery(this).attr("filter") !== "")
                    jQuery("#category_filter").val(current_filter + "," + jQuery(this).attr("filter"));


            })

            jQuery("#wbb_pagination li").removeClass("active")
            jQuery("#wbb_pagination li").first().addClass("active")

        }
    })*/
// Get the country name for a given code.
    function getCountryCode(strCountryName) {
        // ISO 3166-1 country names and codes from http://opencountrycodes.appspot.com/javascript
        countries = [
            {code: "GB", name: "United Kingdom"},
            {code: "AF", name: "Afghanistan"},
            {code: "AX", name: "Aland Islands"},
            {code: "AL", name: "Albania"},
            {code: "DZ", name: "Algeria"},
            {code: "AS", name: "American Samoa"},
            {code: "AD", name: "Andorra"},
            {code: "AO", name: "Angola"},
            {code: "AI", name: "Anguilla"},
            {code: "AQ", name: "Antarctica"},
            {code: "AG", name: "Antigua and Barbuda"},
            {code: "AR", name: "Argentina"},
            {code: "AM", name: "Armenia"},
            {code: "AW", name: "Aruba"},
            {code: "AU", name: "Australia"},
            {code: "AT", name: "Austria"},
            {code: "AZ", name: "Azerbaijan"},
            {code: "BS", name: "Bahamas"},
            {code: "BH", name: "Bahrain"},
            {code: "BD", name: "Bangladesh"},
            {code: "BB", name: "Barbados"},
            {code: "BY", name: "Belarus"},
            {code: "BE", name: "Belgium"},
            {code: "BZ", name: "Belize"},
            {code: "BJ", name: "Benin"},
            {code: "BM", name: "Bermuda"},
            {code: "BT", name: "Bhutan"},
            {code: "BO", name: "Bolivia"},
            {code: "BA", name: "Bosnia and Herzegovina"},
            {code: "BW", name: "Botswana"},
            {code: "BV", name: "Bouvet Island"},
            {code: "BR", name: "Brazil"},
            {code: "IO", name: "British Indian Ocean Territory"},
            {code: "BN", name: "Brunei Darussalam"},
            {code: "BG", name: "Bulgaria"},
            {code: "BF", name: "Burkina Faso"},
            {code: "BI", name: "Burundi"},
            {code: "KH", name: "Cambodia"},
            {code: "CM", name: "Cameroon"},
            {code: "CA", name: "Canada"},
            {code: "CV", name: "Cape Verde"},
            {code: "KY", name: "Cayman Islands"},
            {code: "CF", name: "Central African Republic"},
            {code: "TD", name: "Chad"},
            {code: "CL", name: "Chile"},
            {code: "CN", name: "China"},
            {code: "CX", name: "Christmas Island"},
            {code: "CC", name: "Cocos (Keeling) Islands"},
            {code: "CO", name: "Colombia"},
            {code: "KM", name: "Comoros"},
            {code: "CG", name: "Congo"},
            {code: "CD", name: "Democratic Republic of the Congo"},
            {code: "CK", name: "Cook Islands"},
            {code: "CR", name: "Costa Rica"},
            {code: "CI", name: "Ivory Coast"},
            {code: "HR", name: "Croatia"},
            {code: "CU", name: "Cuba"},
            {code: "CY", name: "Cyprus"},
            {code: "CZ", name: "Czech Republic"},
            {code: "DK", name: "Denmark"},
            {code: "DJ", name: "Djibouti"},
            {code: "DM", name: "Dominica"},
            {code: "DO", name: "Dominican Republic"},
            {code: "EC", name: "Ecuador"},
            {code: "EG", name: "Egypt"},
            {code: "SV", name: "El Salvador"},
            {code: "GQ", name: "Equatorial Guinea"},
            {code: "ER", name: "Eritrea"},
            {code: "EE", name: "Estonia"},
            {code: "ET", name: "Ethiopia"},
            {code: "FK", name: "Falkland Islands (Malvinas)"},
            {code: "FO", name: "Faroe Islands"},
            {code: "FJ", name: "Fiji"},
            {code: "FI", name: "Finland"},
            {code: "FR", name: "France"},
            {code: "GF", name: "French Guiana"},
            {code: "PF", name: "French Polynesia"},
            {code: "TF", name: "French Southern Territories"},
            {code: "GA", name: "Gabon"},
            {code: "GM", name: "Gambia"},
            {code: "GE", name: "Georgia"},
            {code: "DE", name: "Germany"},
            {code: "GH", name: "Ghana"},
            {code: "GI", name: "Gibraltar"},
            {code: "GR", name: "Greece"},
            {code: "GL", name: "Greenland"},
            {code: "GD", name: "Grenada"},
            {code: "GP", name: "Guadeloupe"},
            {code: "GU", name: "Guam"},
            {code: "GT", name: "Guatemala"},
            {code: "GG", name: "Guernsey"},
            {code: "GN", name: "Guinea"},
            {code: "GW", name: "Guinea-Bissau"},
            {code: "GY", name: "Guyana"},
            {code: "HT", name: "Haiti"},
            {code: "HM", name: "Heard Island and McDonald Islands"},
            {code: "VA", name: "Holy See (Vatican City State)"},
            {code: "HN", name: "Honduras"},
            {code: "HK", name: "Hong Kong"},
            {code: "HU", name: "Hungary"},
            {code: "IS", name: "Iceland"},
            {code: "IN", name: "India"},
            {code: "ID", name: "Indonesia"},
            {code: "IR", name: "Iran, Islamic Republic of"},
            {code: "IQ", name: "Iraq"},
            {code: "IE", name: "Ireland"},
            {code: "IM", name: "Isle of Man"},
            {code: "IL", name: "Israel"},
            {code: "IT", name: "Italy"},
            {code: "JM", name: "Jamaica"},
            {code: "JP", name: "Japan"},
            {code: "JE", name: "Jersey"},
            {code: "JO", name: "Jordan"},
            {code: "KZ", name: "Kazakhstan"},
            {code: "KE", name: "Kenya"},
            {code: "KI", name: "Kiribati"},
            {code: "KP", name: "Korea, Democratic People's Republic of"},
            {code: "KR", name: "Korea, Republic of"},
            {code: "KW", name: "Kuwait"},
            {code: "KG", name: "Kyrgyzstan"},
            {code: "LA", name: "Lao People's Democratic Republic"},
            {code: "LV", name: "Latvia"},
            {code: "LB", name: "Lebanon"},
            {code: "LS", name: "Lesotho"},
            {code: "LR", name: "Liberia"},
            {code: "LY", name: "Libyan Arab Jamahiriya"},
            {code: "LI", name: "Liechtenstein"},
            {code: "LT", name: "Lithuania"},
            {code: "LU", name: "Luxembourg"},
            {code: "MO", name: "Macao"},
            {code: "MK", name: "Macedonia, The Former Yugoslav Republic of"},
            {code: "MG", name: "Madagascar"},
            {code: "MW", name: "Malawi"},
            {code: "MY", name: "Malaysia"},
            {code: "MV", name: "Maldives"},
            {code: "ML", name: "Mali"},
            {code: "MT", name: "Malta"},
            {code: "MH", name: "Marshall Islands"},
            {code: "MQ", name: "Martinique"},
            {code: "MR", name: "Mauritania"},
            {code: "MU", name: "Mauritius"},
            {code: "YT", name: "Mayotte"},
            {code: "MX", name: "Mexico"},
            {code: "FM", name: "Micronesia, Federated States of"},
            {code: "MD", name: "Moldova"},
            {code: "MC", name: "Monaco"},
            {code: "MN", name: "Mongolia"},
            {code: "ME", name: "Montenegro"},
            {code: "MS", name: "Montserrat"},
            {code: "MA", name: "Morocco"},
            {code: "MZ", name: "Mozambique"},
            {code: "MM", name: "Myanmar"},
            {code: "NA", name: "Namibia"},
            {code: "NR", name: "Nauru"},
            {code: "NP", name: "Nepal"},
            {code: "NL", name: "Netherlands"},
            {code: "AN", name: "Netherlands Antilles"},
            {code: "NC", name: "New Caledonia"},
            {code: "NZ", name: "New Zealand"},
            {code: "NI", name: "Nicaragua"},
            {code: "NE", name: "Niger"},
            {code: "NG", name: "Nigeria"},
            {code: "NU", name: "Niue"},
            {code: "NF", name: "Norfolk Island"},
            {code: "MP", name: "Northern Mariana Islands"},
            {code: "NO", name: "Norway"},
            {code: "OM", name: "Oman"},
            {code: "PK", name: "Pakistan"},
            {code: "PW", name: "Palau"},
            {code: "PS", name: "Palestinian Territory, Occupied"},
            {code: "PA", name: "Panama"},
            {code: "PG", name: "Papua New Guinea"},
            {code: "PY", name: "Paraguay"},
            {code: "PE", name: "Peru"},
            {code: "PH", name: "Philippines"},
            {code: "PN", name: "Pitcairn"},
            {code: "PL", name: "Poland"},
            {code: "PT", name: "Portugal"},
            {code: "PR", name: "Puerto Rico"},
            {code: "QA", name: "Qatar"},
            {code: "RE", name: "Réunion"},
            {code: "RO", name: "Romania"},
            {code: "RU", name: "Russian Federation"},
            {code: "RW", name: "Rwanda"},
            {code: "BL", name: "Saint Barthélemy"},
            {code: "SH", name: "Saint Helena"},
            {code: "KN", name: "Saint Kitts and Nevis"},
            {code: "LC", name: "Saint Lucia"},
            {code: "MF", name: "Saint Martin"},
            {code: "PM", name: "Saint Pierre and Miquelon"},
            {code: "VC", name: "Saint Vincent and the Grenadines"},
            {code: "WS", name: "Samoa"},
            {code: "SM", name: "San Marino"},
            {code: "ST", name: "Sao Tome and Principe"},
            {code: "SA", name: "Saudi Arabia"},
            {code: "SN", name: "Senegal"},
            {code: "RS", name: "Serbia"},
            {code: "SC", name: "Seychelles"},
            {code: "SL", name: "Sierra Leone"},
            {code: "SG", name: "Singapore"},
            {code: "SK", name: "Slovakia"},
            {code: "SI", name: "Slovenia"},
            {code: "SB", name: "Solomon Islands"},
            {code: "SO", name: "Somalia"},
            {code: "ZA", name: "South Africa"},
            {code: "GS", name: "South Georgia and the South Sandwich Islands"},
            {code: "ES", name: "Spain"},
            {code: "LK", name: "Sri Lanka"},
            {code: "SD", name: "Sudan"},
            {code: "SR", name: "Suriname"},
            {code: "SJ", name: "Svalbard and Jan Mayen"},
            {code: "SZ", name: "Swaziland"},
            {code: "SE", name: "Sweden"},
            {code: "CH", name: "Switzerland"},
            {code: "SY", name: "Syrian Arab Republic"},
            {code: "TW", name: "Taiwan, Province of China"},
            {code: "TJ", name: "Tajikistan"},
            {code: "TZ", name: "Tanzania"},
            {code: "TH", name: "Thailand"},
            {code: "TL", name: "Timor-Leste"},
            {code: "TG", name: "Togo"},
            {code: "TK", name: "Tokelau"},
            {code: "TO", name: "Tonga"},
            {code: "TT", name: "Trinidad and Tobago"},
            {code: "TN", name: "Tunisia"},
            {code: "TR", name: "Turkey"},
            {code: "TM", name: "Turkmenistan"},
            {code: "TC", name: "Turks and Caicos Islands"},
            {code: "TV", name: "Tuvalu"},
            {code: "UG", name: "Uganda"},
            {code: "UA", name: "Ukraine"},
            {code: "AE", name: "United Arab Emirates"},
            {code: "GB", name: "United Kingdom"},
            {code: "US", name: "United States"},
            {code: "UM", name: "United States Minor Outlying Islands"},
            {code: "UY", name: "Uruguay"},
            {code: "UZ", name: "Uzbekistan"},
            {code: "VU", name: "Vanuatu"},
            {code: "VE", name: "Venezuela"},
            {code: "VN", name: "Viet Nam"},
            {code: "VG", name: "Virgin Islands, British"},
            {code: "VI", name: "Virgin Islands, U.S."},
            {code: "WF", name: "Wallis and Futuna"},
            {code: "EH", name: "Western Sahara"},
            {code: "YE", name: "Yemen"},
            {code: "ZM", name: "Zambia"},
            {code: "ZW", name: "Zimbabwe"}
        ];

        for (var i = 0; i < countries.length; i++) {
            if (strCountryName == countries[i].name) {
                //console.log(countries);
                return countries[i].code;
            }
        }
        return strCountryName;
    }


    function getlatLng(code){
       code=code.toLowerCase();
        latlon = {"ad":["42.5000","1.5000"],"ae":["24.0000","54.0000"],"af":["33.0000","65.0000"],"ag":["17.0500","-61.8000"],"ai":["18.2500","-63.1667"],"al":["41.0000","20.0000"],"am":["40.0000","45.0000"],"an":["12.2500","-68.7500"],"ao":["-12.5000","18.5000"],"ap":["35.0000","105.0000"],"aq":["-90.0000","0.0000"],"ar":["-34.0000","-64.0000"],"as":["-14.3333","-170.0000"],"at":["47.3333","13.3333"],"au":["-27.0000","133.0000"],"aw":["12.5000","-69.9667"],"az":["40.5000","47.5000"],"ba":["44.0000","18.0000"],"bb":["13.1667","-59.5333"],"bd":["24.0000","90.0000"],"be":["50.8333","4.0000"],"bf":["13.0000","-2.0000"],"bg":["43.0000","25.0000"],"bh":["26.0000","50.5500"],"bi":["-3.5000","30.0000"],"bj":["9.5000","2.2500"],"bm":["32.3333","-64.7500"],"bn":["4.5000","114.6667"],"bo":["-17.0000","-65.0000"],"br":["-10.0000","-55.0000"],"bs":["24.2500","-76.0000"],"bt":["27.5000","90.5000"],"bv":["-54.4333","3.4000"],"bw":["-22.0000","24.0000"],"by":["53.0000","28.0000"],"bz":["17.2500","-88.7500"],"ca":["60.0000","-95.0000"],"cc":["-12.5000","96.8333"],"cd":["0.0000","25.0000"],"cf":["7.0000","21.0000"],"cg":["-1.0000","15.0000"],"ch":["47.0000","8.0000"],"ci":["8.0000","-5.0000"],"ck":["-21.2333","-159.7667"],"cl":["-30.0000","-71.0000"],"cm":["6.0000","12.0000"],"cn":["35.0000","105.0000"],"co":["4.0000","-72.0000"],"cr":["10.0000","-84.0000"],"cu":["21.5000","-80.0000"],"cv":["16.0000","-24.0000"],"cx":["-10.5000","105.6667"],"cy":["35.0000","33.0000"],"cz":["49.7500","15.5000"],"de":["51.0000","9.0000"],"dj":["11.5000","43.0000"],"dk":["56.0000","10.0000"],"dm":["15.4167","-61.3333"],"do":["19.0000","-70.6667"],"dz":["28.0000","3.0000"],"ec":["-2.0000","-77.5000"],"ee":["59.0000","26.0000"],"eg":["27.0000","30.0000"],"eh":["24.5000","-13.0000"],"er":["15.0000","39.0000"],"es":["40.0000","-4.0000"],"et":["8.0000","38.0000"],"eu":["47.0000","8.0000"],"fi":["64.0000","26.0000"],"fj":["-18.0000","175.0000"],"fk":["-51.7500","-59.0000"],"fm":["6.9167","158.2500"],"fo":["62.0000","-7.0000"],"fr":["46.0000","2.0000"],"ga":["-1.0000","11.7500"],"gb":["54.0000","-2.0000"],"gd":["12.1167","-61.6667"],"ge":["42.0000","43.5000"],"gf":["4.0000","-53.0000"],"gh":["8.0000","-2.0000"],"gi":["36.1833","-5.3667"],"gl":["72.0000","-40.0000"],"gm":["13.4667","-16.5667"],"gn":["11.0000","-10.0000"],"gp":["16.2500","-61.5833"],"gq":["2.0000","10.0000"],"gr":["39.0000","22.0000"],"gs":["-54.5000","-37.0000"],"gt":["15.5000","-90.2500"],"gu":["13.4667","144.7833"],"gw":["12.0000","-15.0000"],"gy":["5.0000","-59.0000"],"hk":["22.2500","114.1667"],"hm":["-53.1000","72.5167"],"hn":["15.0000","-86.5000"],"hr":["45.1667","15.5000"],"ht":["19.0000","-72.4167"],"hu":["47.0000","20.0000"],"id":["-5.0000","120.0000"],"ie":["53.0000","-8.0000"],"il":["31.5000","34.7500"],"in":["20.0000","77.0000"],"io":["-6.0000","71.5000"],"iq":["33.0000","44.0000"],"ir":["32.0000","53.0000"],"is":["65.0000","-18.0000"],"it":["42.8333","12.8333"],"jm":["18.2500","-77.5000"],"jo":["31.0000","36.0000"],"jp":["36.0000","138.0000"],"ke":["1.0000","38.0000"],"kg":["41.0000","75.0000"],"kh":["13.0000","105.0000"],"ki":["1.4167","173.0000"],"km":["-12.1667","44.2500"],"kn":["17.3333","-62.7500"],"kp":["40.0000","127.0000"],"kr":["37.0000","127.5000"],"kw":["29.3375","47.6581"],"ky":["19.5000","-80.5000"],"kz":["48.0000","68.0000"],"la":["18.0000","105.0000"],"lb":["33.8333","35.8333"],"lc":["13.8833","-61.1333"],"li":["47.1667","9.5333"],"lk":["7.0000","81.0000"],"lr":["6.5000","-9.5000"],"ls":["-29.5000","28.5000"],"lt":["56.0000","24.0000"],"lu":["49.7500","6.1667"],"lv":["57.0000","25.0000"],"ly":["25.0000","17.0000"],"ma":["32.0000","-5.0000"],"mc":["43.7333","7.4000"],"md":["47.0000","29.0000"],"me":["42.0000","19.0000"],"mg":["-20.0000","47.0000"],"mh":["9.0000","168.0000"],"mk":["41.8333","22.0000"],"ml":["17.0000","-4.0000"],"mm":["22.0000","98.0000"],"mn":["46.0000","105.0000"],"mo":["22.1667","113.5500"],"mp":["15.2000","145.7500"],"mq":["14.6667","-61.0000"],"mr":["20.0000","-12.0000"],"ms":["16.7500","-62.2000"],"mt":["35.8333","14.5833"],"mu":["-20.2833","57.5500"],"mv":["3.2500","73.0000"],"mw":["-13.5000","34.0000"],"mx":["23.0000","-102.0000"],"my":["2.5000","112.5000"],"mz":["-18.2500","35.0000"],"na":["-22.0000","17.0000"],"nc":["-21.5000","165.5000"],"ne":["16.0000","8.0000"],"nf":["-29.0333","167.9500"],"ng":["10.0000","8.0000"],"ni":["13.0000","-85.0000"],"nl":["52.5000","5.7500"],"no":["62.0000","10.0000"],"np":["28.0000","84.0000"],"nr":["-0.5333","166.9167"],"nu":["-19.0333","-169.8667"],"nz":["-41.0000","174.0000"],"om":["21.0000","57.0000"],"pa":["9.0000","-80.0000"],"pe":["-10.0000","-76.0000"],"pf":["-15.0000","-140.0000"],"pg":["-6.0000","147.0000"],"ph":["13.0000","122.0000"],"pk":["30.0000","70.0000"],"pl":["52.0000","20.0000"],"pm":["46.8333","-56.3333"],"pr":["18.2500","-66.5000"],"ps":["32.0000","35.2500"],"pt":["39.5000","-8.0000"],"pw":["7.5000","134.5000"],"py":["-23.0000","-58.0000"],"qa":["25.5000","51.2500"],"re":["-21.1000","55.6000"],"ro":["46.0000","25.0000"],"rs":["44.0000","21.0000"],"ru":["60.0000","100.0000"],"rw":["-2.0000","30.0000"],"sa":["25.0000","45.0000"],"sb":["-8.0000","159.0000"],"sc":["-4.5833","55.6667"],"sd":["15.0000","30.0000"],"se":["62.0000","15.0000"],"sg":["1.3667","103.8000"],"sh":["-15.9333","-5.7000"],"si":["46.0000","15.0000"],"sj":["78.0000","20.0000"],"sk":["48.6667","19.5000"],"sl":["8.5000","-11.5000"],"sm":["43.7667","12.4167"],"sn":["14.0000","-14.0000"],"so":["10.0000","49.0000"],"sr":["4.0000","-56.0000"],"st":["1.0000","7.0000"],"sv":["13.8333","-88.9167"],"sy":["35.0000","38.0000"],"sz":["-26.5000","31.5000"],"tc":["21.7500","-71.5833"],"td":["15.0000","19.0000"],"tf":["-43.0000","67.0000"],"tg":["8.0000","1.1667"],"th":["15.0000","100.0000"],"tj":["39.0000","71.0000"],"tk":["-9.0000","-172.0000"],"tm":["40.0000","60.0000"],"tn":["34.0000","9.0000"],"to":["-20.0000","-175.0000"],"tr":["39.0000","35.0000"],"tt":["11.0000","-61.0000"],"tv":["-8.0000","178.0000"],"tw":["23.5000","121.0000"],"tz":["-6.0000","35.0000"],"ua":["49.0000","32.0000"],"ug":["1.0000","32.0000"],"um":["19.2833","166.6000"],"us":["38.0000","-97.0000"],"uy":["-33.0000","-56.0000"],"uz":["41.0000","64.0000"],"va":["41.9000","12.4500"],"vc":["13.2500","-61.2000"],"ve":["8.0000","-66.0000"],"vg":["18.5000","-64.5000"],"vi":["18.3333","-64.8333"],"vn":["16.0000","106.0000"],"vu":["-16.0000","167.0000"],"wf":["-13.3000","-176.2000"],"ws":["-13.5833","-172.3333"],"ye":["15.0000","48.0000"],"yt":["-12.8333","45.1667"],"za":["-29.0000","24.0000"],"zm":["-15.0000","30.0000"],"zw":["-20.0000","30.0000"]}

            return latlon[code];

    }

    


    function show_projects_result() {

        var filter = jQuery("#category_filter").val();
        var country = jQuery("#country_filter").val()

        var page = 1

        if (jQuery("#wbb_pagination li.active").length > 0)
            page = jQuery("#wbb_pagination li.active").attr("page")


        var data_show_project = {
            action: "show_project_query",
            filter: filter,
            page: page,
            country: country
        }

        jQuery("#project_result_list").fadeOut(100, function () {

            jQuery("#project_result_list").html("")

            jQuery.post(MyAjax.ajaxurl, data_show_project, function (res) {


                jQuery("#project_result_list").append(res)

                var project_item = jQuery("#project_result_list .project_item")

                project_item.each(function () {

                    var country_list = jQuery(this).find(".project_country_title").attr("countries");


                    if (country_list !== "" && typeof country_list !== "undefined") {

                        var country_string = ""

                        if (country_list.search(",") > 0) {

                            var country_array = country_list.split(",");
                            var counter = 1;
                            country_array.forEach(function (i) {

                                country_string += map.getRegionName(i)
                                if (counter < country_array.length)
                                    country_string += ", "

                                counter++
                            })

                        }
                        else {

                            if (country_list !== "") {
                                country_string += map.getRegionName(country_list)
                            }

                        }

                        if (country_string.length > 18)
                            country_string = country_string.substr(0, 15) + "..."

                        jQuery(this).find(".project_country_title").removeAttr("countries")
                        jQuery(this).find(".project_country_title").html(country_string)
                    }
                })

                jQuery("#project_result_list").fadeIn(100, function () {

                    jQuery("#project_map").addClass("active")

                })

            })

        })
    }

//FILTER BY COUNTRIES
    jQuery(document).on("click", "#project_countries_list li", function () {

        if (jQuery('#project_map').hasClass("active")) {

            jQuery('#project_map').removeClass("active")
            var code = jQuery(this).attr("filter")

            if (code !== "") {

                jQuery("#project_countries_list li").first().removeClass("active")

                if (jQuery(this).hasClass("active"))
                    jQuery(this).removeClass("active")
                else
                    jQuery(this).addClass("active")


                var current_countries = map.getSelectedRegions()
                console.log(current_countries);
                if (current_countries.indexOf(code) > -1) {

                    current_countries.splice(current_countries.indexOf(code), 1);

                }
                else {

                    current_countries.push(code)

                }


                jQuery("#wbb_pagination li").removeClass("active")
                jQuery("#wbb_pagination li").first().addClass("active")


                map.clearSelectedRegions()
                map.setSelectedRegions(current_countries)

                if (jQuery("#project_countries_list li.active").length < 1)
                    jQuery("#project_countries_list li").first().addClass("active")

                show_data(current_countries)
            }
            else {
                map.clearSelectedRegions()

                jQuery("#project_countries_list li").removeClass("active")
                jQuery(this).addClass("active")

                show_data(code)

            }

        }


    })

//PAGINATION
    jQuery(document).on("click", "#wbb_pagination li", function () {

        if (!jQuery(this).hasClass("active")) {

            jQuery("#wbb_pagination li").removeClass("active")
            jQuery(this).addClass("active")

            show_projects_result()

        }


    })

    function show_data(code) {
//Filter result by this country-code

        jQuery("#country_filter").val(code);
        show_projects_result("")

    }


    jQuery(window).scroll(function () {

        if (jQuery(document).scrollTop() > 800)
            jQuery(".clase").addClass("active")
        else
            jQuery(".clase").removeClass("active")

    })


    jQuery(document).on("click", ".clase.active", function () {

        scrollWinTop()

    })

    function scrollWinTop() {
        jQuery('html,body').animate({
            scrollTop: 0
        }, 400);
    }

    function get_data_for_mouseover(){

        var data = {
            action: "get_filters_amount_mouseover"
        };

        return jQuery.ajax({
            type: "POST",
            url: MyAjax.ajaxurl,
            async: false,
            data: data,
            dataType: "json"

        }).responseText;

    }

    function scrollOnHover(){
        $(".truncate").mouseover(function() {
            $(this).removeClass("ellipsis");
            var maxscroll = $(this).width();
            var speed = maxscroll * 15;
            $(this).animate({
                scrollLeft: maxscroll
            }, speed, "linear");
        });

        $(".truncate").mouseout(function() {
            $(this).stop();
            $(this).addClass("ellipsis");
            $(this).animate({
                scrollLeft: 0
            }, 'slow');
        });
    }

});