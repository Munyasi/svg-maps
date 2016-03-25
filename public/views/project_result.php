<div class="col-md-3">
    
    <div class="media-item-container">
    <div class="media-item-project project_item">
        <a href="<?php the_permalink (); ?>">

            <?php
            
            if( has_post_thumbnail( get_the_ID() ) )
            {
                the_post_thumbnail( array( 265, 150 ) , true);
            }
            else
            {
                echo '<img src="http://dummyimage.com/265x220/000/fff"/>';
            }
            
            ?>
                

        </a>             

        <div class="media-body">

            <h2 class="media-heading"> 

                <a href="<?php the_permalink (); ?>"> <?php the_title()?></a>

            </h2>

            <div class="media-content">
                
                <?php THEME_FUNCTIONS_limit_content_words ( get_the_content(), 140 ); ?>

            </div>

            <div class="media-meta">
                <div class="project_meta project_country_title" countries="<?php echo get_post_meta(get_the_ID(), "country_connected", true);?>">
                </div>
                <div class="project_meta"><?php echo $terms_name;?></div>

            </div>


        </div>       
    </div>
    </div>
        
    <div class="arrow_down_black"></div>
    
</div>