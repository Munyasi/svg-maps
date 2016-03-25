<div class="col-md-4">
    <div class="categories_and_countries_filters">
    <h3>COUNTRIES ///</h3>
    <ul id='project_countries_list'>
    </ul>

    <h3>CATEGORIES ///</h3>
    <ul id='project_categories_list'>
        <?php 

        $taxonomy = 'categories';
        $args = array(
                      'orderby' => 'name',
                      'show_count' => 0,
                      'pad_counts' => 0,
                      'hierarchical' => 1,
                      'taxonomy' => $taxonomy,
                      'title_li' => ''
                    );
        $p_categories=get_categories( $args );

        $count = count($p_categories);
        if ( $count > 0 ){
            echo "<li class='project_category active' filter='' >Show All categories <i class='icon-ok'></i></li>";

            foreach ( $p_categories as $term ) {

                echo "<li class='project_category' filter='".$term->slug."' >" . $term->name . "<i class='icon-ok'></i></li>";

            }
        }
        ?>
    </ul>
    
    </div>
    
</div>