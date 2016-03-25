<style type="text/css">
    .loading-wheel{
        position: absolute;
        left: 0px;
        width: 100%;
        height: 100%;
        background-color: white;
        opacity: 0.8;
        text-align: center;
    }
</style>
<div class="js-loading-wheel loading-wheel" id="js-loading-wheel" style="display: none;">
    <?php $loader_url= plugins_url ( 'wbb-projects-master/public/assets/images/loading-wheel.gif' );?>
    <img src="<?php echo $loader_url;?>">
</div>
<div id="project_map" class="active">
</div>
<div id="wbb_projects_main_container" >
    
    <!-- Main div -->
    <div class="row">
        <!-- Content div -->
        <div class="col-md-8">
            <!-- map/Projects div -->
            <div class="row">
                <p class="projects-guide">Welcome to our current projects listing. Use the side bar on the right to filter project displays. Click the markers on the map to navigate to a specific project.</p>
            </div>
            <div class="row">
                <!-- Map -->
                <div id="main_project_map" class="col-md-12">
                    <input type="hidden" id="category_filter" value="" />
                    <input type="hidden" id="country_filter" value="" />
                </div><!-- End Map div -->
                <!-- Project listing -->
                <div id="project_results_container" class="col-md-12">

                    <div class="row" id="project_result_list">

                    </div>
                    <ul class="wbb_country_pagination">
                    </ul>
                </div><!-- End project listing div -->
            </div>
        </div><!-- End map/Project div -->
        <!-- Country sidebar div -->
        <div class="col-md-4 categories_and_project_filters">
            <h3>CURRENT PROJECTS</h3>
                <ul id='project_title_list'>
                </ul>
            <h3>THEMATIC AREAS</h3>
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
                    echo "<li class='project_category active all' filter='' >Show All Thematic Areas <i class='icon-ok'></i></li>";

                    foreach ( $p_categories as $term ) {

                        echo "<li class='project_category truncate ellipsis' data-filter='".$term->slug."' >" . $term->name . "<i class='icon-ok'></i></li>";

                    }
                }
                ?>
            </ul>
        </div><!-- end conuntry div -->
    </div><!-- End main div -->