<!-- <div class="col-md-3">
    
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
                
                <?php get_the_content(); ?>

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
 -->

<div class="col-md-6 projects" <?php echo $style; ?> >
    <div class="col-md-12">
        <div class="row card">
            <div class="col-md-8 no-margin">
                <div class="project-video ivory-bg">
                   <?php
            
                    if( has_post_thumbnail( get_the_ID() ) )
                    {
                         the_post_thumbnail( 'full' ); 
                    }
                    else
                    {
                        echo '<img src="http://dummyimage.com/265x220/000/fff"/>';
                    }
                    
                ?>
                </div>
            </div>
            <div class="col-md-4 no-margin">
                <div class="project-description-video steelblue">
                    <p class="project-title-video">
                        <a style="color: #fff" href="<?php the_permalink (); ?>">
                            <?php echo $this->limit_words(get_the_title(),5);?>                                
                        </a>
                    </p>

                    <p class="project-org">
                        <b><i class="fa fa-check-circle"></i>&nbsp;Organization:&nbsp;</b> 
                        <?php echo $this->limit_words($post_meta['ocsdnet_project_title'][0],10);;?>                              </p>
                    <p class="project-countries">
                        <b><i class="fa fa-map-marker"></i>&nbsp;Countries:&nbsp;</b> <?php echo $post_meta['ocsdnet_project_countries'][0];?></p>
                    <p class="read-more">
                        <a href="<?php the_permalink (); ?>" style="color:#fff; font-weight: bold;">
                            &nbsp;&nbsp;<i class="fa fa-angle-double-right"></i> READ MORE
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
