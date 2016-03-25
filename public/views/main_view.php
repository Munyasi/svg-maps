<div id="wbb_projects_main_container">
    
    <div id="project_map" class="active">
        <div class="row">
            <div class="col-md-8">
                <div id="main_project_map"></div>
                <input type="hidden" id="category_filter" value="" />
                <input type="hidden" id="country_filter" value="" />
            </div>
            <div class="col-md-4">
                <div class="categories_and_countries_filters">
                <h3>COUNTRIES ///</h3>
                <ul id='project_countries_list'>
                </ul>
<!-- 
                <h3>CATEGORIES ///</h3>
                <ul id='project_categories_list'>
                    <?php 

                    $taxonomy = 'project_category';
                    $terms = get_terms($taxonomy);

                    $count = count($terms);
                    if ( $count > 0 ){
                        echo "<li class='project_category active' filter='' >Show All categories <i class='icon-ok'></i></li>";

                        foreach ( $terms as $term ) {

                            echo "<li class='project_category' filter='".$term->slug."' >" . $term->name . "<i class='icon-ok'></i></li>";

                        }
                    }



                    ?>
                </ul> -->
                
                </div>
                
            </div>
        </div>

    </div>


    <div id="project_results_container">

        <div class="row" id="project_result_list">

        </div>
        <ul class="wbb_country_pagination">
        </ul>

    </div>


</div>